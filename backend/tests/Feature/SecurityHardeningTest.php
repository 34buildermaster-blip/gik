<?php

namespace Tests\Feature;

use App\Models\StoredFile;
use App\Models\User;
use App\Services\MediaStorage;
use App\Services\TwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_temporarily_throttled_after_repeated_failures(): void
    {
        $user = User::factory()->admin()->create([
            'username' => 'secure-admin',
            'password' => 'CorrectPassword1',
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), [
                'login' => $user->username,
                'password' => 'wrong-password',
                'portal' => 'admin',
            ])->assertSessionHasErrors('login');
        }

        $this->post(route('login.store'), [
            'login' => $user->username,
            'password' => 'CorrectPassword1',
            'portal' => 'admin',
        ])->assertSessionHasErrors('login');

        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login.throttled']);
        $this->assertGuest();
    }

    public function test_staff_with_two_factor_enabled_must_complete_the_challenge(): void
    {
        $secret = app(TwoFactorAuthentication::class)->generateSecret();
        $user = User::factory()->admin()->create([
            'username' => 'two-factor-admin',
            'password' => 'CorrectPassword1',
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'login' => $user->username,
            'password' => 'CorrectPassword1',
            'portal' => 'admin',
        ])->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $code = app(Google2FA::class)->getCurrentOtp($secret);
        $this->post(route('two-factor.challenge.store'), ['code' => $code])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login.succeeded', 'user_id' => $user->id]);
    }

    public function test_staff_can_enable_two_factor_authentication_from_profile(): void
    {
        $user = User::factory()->admin()->create(['password' => 'CorrectPassword1']);

        $this->actingAs($user)
            ->post(route('admin.profile.two-factor.start'), ['current_password' => 'CorrectPassword1'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('two_factor_setup_secret');

        $secret = session('two_factor_setup_secret');
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->actingAs($user)
            ->post(route('admin.profile.two-factor.confirm'), ['code' => $code])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('two_factor_recovery_codes');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorAuthenticationEnabled());
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    public function test_staff_without_two_factor_is_redirected_to_security_setup(): void
    {
        config(['security.staff_2fa_required' => true]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('warning');

        $this->actingAs($admin)
            ->get(route('admin.profile.edit'))
            ->assertOk();
    }

    public function test_staff_cannot_disable_two_factor_when_it_is_required(): void
    {
        config(['security.staff_2fa_required' => true]);
        $secret = app(TwoFactorAuthentication::class)->generateSecret();
        $admin = User::factory()->admin()->create([
            'password' => 'CorrectPassword1',
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.profile.two-factor.destroy'), [
                'current_password' => 'CorrectPassword1',
                'code' => app(Google2FA::class)->getCurrentOtp($secret),
            ])
            ->assertSessionHasErrors('code', null, 'twoFactor');

        $this->assertTrue($admin->refresh()->hasTwoFactorAuthenticationEnabled());
    }

    public function test_customer_can_enable_two_factor_authentication_and_complete_login_challenge(): void
    {
        $customer = User::factory()->create([
            'username' => 'two-factor-customer',
            'password' => 'CorrectPassword1',
        ]);

        $this->actingAs($customer)
            ->post(route('admin.profile.two-factor.start'), ['current_password' => 'CorrectPassword1'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('two_factor_setup_secret');

        $secret = session('two_factor_setup_secret');
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->actingAs($customer)
            ->post(route('admin.profile.two-factor.confirm'), ['code' => $code])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('two_factor_recovery_codes');

        $this->post(route('logout'));
        $this->post(route('login.store'), [
            'login' => $customer->username,
            'password' => 'CorrectPassword1',
            'portal' => 'customer',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->post(route('two-factor.challenge.store'), [
            'code' => app(Google2FA::class)->getCurrentOtp($secret),
        ])->assertRedirect(route('client.projects.index'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_upload_pipeline_fingerprints_files_and_removes_quarantine_copy(): void
    {
        Storage::fake('local');
        config([
            'media.driver' => 'local',
            'media.images.optimize' => false,
            'security.upload_scan.enabled' => false,
        ]);

        $file = app(MediaStorage::class)->store(
            UploadedFile::fake()->create('contract.pdf', 20, 'application/pdf'),
            'project-documents/1',
            'private',
        );

        $this->assertNotNull($file->sha256);
        $this->assertSame(64, strlen($file->sha256));
        $this->assertSame('not_scanned', $file->scan_status);
        Storage::disk('local')->assertDirectoryEmpty('quarantine');
    }

    public function test_builtin_upload_inspection_validates_a_safe_pdf(): void
    {
        Storage::fake('local');
        config([
            'media.driver' => 'local',
            'media.images.optimize' => false,
            'security.upload_scan.enabled' => true,
            'security.upload_scan.driver' => 'builtin',
            'security.upload_scan.required' => true,
            'security.upload_scan.fail_closed' => true,
        ]);

        $file = app(MediaStorage::class)->store(
            UploadedFile::fake()->createWithContent(
                'contract.pdf',
                "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF",
            ),
            'project-documents/1',
            'private',
        );

        $this->assertSame('validated', $file->scan_status);
        $this->assertTrue($file->passedSecurityInspection());
        $this->assertNotNull($file->scanned_at);
        Storage::disk('local')->assertDirectoryEmpty('quarantine');
    }

    public function test_builtin_upload_inspection_blocks_php_payload(): void
    {
        Storage::fake('local');
        config([
            'security.upload_scan.enabled' => true,
            'security.upload_scan.driver' => 'builtin',
        ]);

        $this->expectException(ValidationException::class);

        app(MediaStorage::class)->store(
            UploadedFile::fake()->createWithContent(
                'unsafe.csv',
                '<?php echo "unsafe"; ?>',
            ),
            'project-documents/1',
            'private',
        );
    }

    public function test_builtin_upload_inspection_does_not_treat_short_echo_bytes_in_an_image_as_php(): void
    {
        Storage::fake('local');
        config([
            'media.driver' => 'local',
            'media.images.optimize' => false,
            'security.upload_scan.enabled' => true,
            'security.upload_scan.driver' => 'builtin',
        ]);

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );
        $this->assertNotFalse($png);

        $file = app(MediaStorage::class)->store(
            UploadedFile::fake()->createWithContent('safe.png', $png.'<?=binary-noise'),
            'project-images/1',
            'private',
        );

        $this->assertSame('validated', $file->scan_status);
        $this->assertTrue($file->passedSecurityInspection());
    }

    public function test_builtin_upload_inspection_blocks_active_pdf_content(): void
    {
        Storage::fake('local');
        config([
            'security.upload_scan.enabled' => true,
            'security.upload_scan.driver' => 'builtin',
        ]);

        $this->expectException(ValidationException::class);

        app(MediaStorage::class)->store(
            UploadedFile::fake()->createWithContent(
                'unsafe.pdf',
                "%PDF-1.4\n1 0 obj<</JavaScript 2 0 R>>endobj\n%%EOF",
            ),
            'project-documents/1',
            'private',
        );
    }

    public function test_builtin_upload_inspection_blocks_office_macros(): void
    {
        Storage::fake('local');
        config([
            'security.upload_scan.enabled' => true,
            'security.upload_scan.driver' => 'builtin',
        ]);

        $path = tempnam(sys_get_temp_dir(), 'unsafe-office-');
        $archive = new \ZipArchive;
        $archive->open($path, \ZipArchive::OVERWRITE);
        $archive->addFromString('[Content_Types].xml', '<Types></Types>');
        $archive->addFromString('word/vbaProject.bin', 'macro');
        $archive->close();

        try {
            app(MediaStorage::class)->store(
                new UploadedFile($path, 'unsafe.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                'project-documents/1',
                'private',
            );
            $this->fail('A macro-enabled Office archive should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        } finally {
            @unlink($path);
        }
    }

    public function test_required_upload_scanning_fails_closed_when_scanner_is_disabled(): void
    {
        Storage::fake('local');
        config([
            'media.driver' => 'local',
            'media.images.optimize' => false,
            'security.upload_scan.enabled' => false,
            'security.upload_scan.required' => true,
            'security.upload_scan.fail_closed' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(MediaStorage::class)->store(
            UploadedFile::fake()->create('contract.pdf', 20, 'application/pdf'),
            'project-documents/1',
            'private',
        );
    }

    public function test_non_clean_managed_files_are_blocked_when_clean_serving_is_required(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/unscanned.jpg', 'unscanned');
        config(['security.upload_scan.require_clean_for_serving' => true]);
        $file = StoredFile::create([
            'uuid' => fake()->uuid(),
            'disk' => 'local',
            'path' => 'public/unscanned.jpg',
            'original_name' => 'unscanned.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 9,
            'scan_status' => 'not_scanned',
            'visibility' => 'public',
            'category' => 'test',
        ]);

        $this->get($file->publicUrl())->assertNotFound();
    }

    public function test_security_headers_are_added_to_web_responses(): void
    {
        $this->get(route('login.admin'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_trusted_https_proxy_enables_hsts(): void
    {
        config(['security.headers.hsts' => true]);
        TrustProxies::at(['127.0.0.1']);

        try {
            $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->withHeader('X-Forwarded-Proto', 'https')
                ->get(route('login.admin'))
                ->assertOk()
                ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        } finally {
            TrustProxies::at([]);
        }
    }
}
