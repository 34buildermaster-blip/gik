<?php

namespace Tests\Feature;

use App\Models\StoredFile;
use App\Models\User;
use App\Models\WelcomePopup;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WelcomePopupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_returns_only_the_current_highest_priority_popup(): void
    {
        $first = $this->popup(['name' => 'Current first', 'sort_order' => 10]);
        $this->popup(['name' => 'Current second', 'sort_order' => 20]);
        $this->popup(['name' => 'Future', 'sort_order' => 1, 'starts_at' => now()->addDay()]);
        $this->popup(['name' => 'Expired', 'sort_order' => 2, 'ends_at' => now()->subDay()]);
        $this->popup(['name' => 'Disabled', 'sort_order' => 3, 'is_active' => false]);

        $this->getJson('/api/welcome-popup')
            ->assertOk()
            ->assertJsonPath('data.id', $first->id)
            ->assertJsonPath('data.alt', 'Promotion image')
            ->assertJsonPath('data.linkUrl', '/contact');
    }

    public function test_only_admin_can_manage_welcome_popups(): void
    {
        $member = User::factory()->create();
        $inspector = User::factory()->inspector()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($member)->get(route('admin.welcome-popups.index'))->assertForbidden();
        $this->actingAs($inspector)->get(route('admin.welcome-popups.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.welcome-popups.index'))->assertOk();
    }

    public function test_admin_can_create_popup_with_desktop_and_mobile_images(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.welcome-popups.store'), [
                ...$this->formData(),
                'desktop_image' => UploadedFile::fake()->create('desktop.webp', 120, 'image/webp'),
                'mobile_image' => UploadedFile::fake()->create('mobile.webp', 80, 'image/webp'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.welcome-popups.index'));

        $popup = WelcomePopup::query()->sole();
        $files = StoredFile::query()->whereIn('id', [$popup->desktop_stored_file_id, $popup->mobile_stored_file_id])->get();

        $this->assertCount(2, $files);
        $this->assertEqualsCanonicalizing(['welcome-popup-desktop', 'welcome-popup-mobile'], $files->pluck('category')->all());
        $files->each(fn (StoredFile $file) => Storage::disk('local')->assertExists($file->path));
    }

    public function test_admin_schedule_uses_bangkok_time_while_storage_and_api_use_utc(): void
    {
        Storage::fake('local');
        CarbonImmutable::setTestNow('2026-08-05 04:30:00 UTC');
        $admin = User::factory()->admin()->create();

        try {
            $this->actingAs($admin)
                ->post(route('admin.welcome-popups.store'), [
                    ...$this->formData([
                        'starts_at' => '2026-08-05T11:00',
                        'ends_at' => '2026-08-05T12:00',
                    ]),
                    'desktop_image' => UploadedFile::fake()->create('desktop.webp', 120, 'image/webp'),
                ])
                ->assertSessionHasNoErrors();

            $popup = WelcomePopup::query()->sole();
            $this->assertSame('2026-08-05 04:00:00', $popup->starts_at->format('Y-m-d H:i:s'));
            $this->assertSame('2026-08-05 05:00:00', $popup->ends_at->format('Y-m-d H:i:s'));

            $this->getJson('/api/welcome-popup')
                ->assertOk()
                ->assertJsonPath('data.id', $popup->id);

            $this->get(route('admin.welcome-popups.edit', $popup))
                ->assertOk()
                ->assertSee('value="2026-08-05T11:00"', false)
                ->assertSee('value="2026-08-05T12:00"', false);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_replacing_and_deleting_popup_cleans_up_managed_images(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $popup = $this->popupWithStoredImages($admin);
        $oldDesktop = $popup->desktopImage;
        $oldMobile = $popup->mobileImage;

        $this->actingAs($admin)
            ->put(route('admin.welcome-popups.update', $popup), [
                ...$this->formData(['name' => 'Updated promotion']),
                'desktop_image' => UploadedFile::fake()->create('new-desktop.webp', 140, 'image/webp'),
                'remove_mobile_image' => '1',
            ])
            ->assertSessionHasNoErrors();

        $popup->refresh();
        $this->assertNotSame($oldDesktop->id, $popup->desktop_stored_file_id);
        $this->assertNull($popup->mobile_stored_file_id);
        $this->assertDatabaseMissing('stored_files', ['id' => $oldDesktop->id]);
        $this->assertDatabaseMissing('stored_files', ['id' => $oldMobile->id]);

        $newDesktop = $popup->desktopImage;
        $this->actingAs($admin)
            ->delete(route('admin.welcome-popups.destroy', $popup))
            ->assertRedirect();

        $this->assertDatabaseMissing('welcome_popups', ['id' => $popup->id]);
        $this->assertDatabaseMissing('stored_files', ['id' => $newDesktop->id]);
        Storage::disk('local')->assertMissing($newDesktop->path);
    }

    private function popup(array $overrides = []): WelcomePopup
    {
        $file = StoredFile::create([
            'uuid' => fake()->uuid(),
            'disk' => 'local',
            'path' => 'welcome-popup-desktop/'.fake()->uuid().'.webp',
            'original_name' => 'promotion.webp',
            'mime_type' => 'image/webp',
            'size' => 10,
            'visibility' => 'public',
            'category' => 'welcome-popup-desktop',
        ]);

        return WelcomePopup::create(array_merge([
            'desktop_stored_file_id' => $file->id,
            'name' => 'Promotion',
            'alt_text' => 'Promotion image',
            'link_url' => '/contact',
            'sort_order' => 10,
            'is_active' => true,
        ], $overrides));
    }

    private function popupWithStoredImages(User $admin): WelcomePopup
    {
        $desktop = $this->storedImage('welcome-popup-desktop/old.webp', 'welcome-popup-desktop', $admin);
        $mobile = $this->storedImage('welcome-popup-mobile/old.webp', 'welcome-popup-mobile', $admin);

        return WelcomePopup::create([
            ...$this->formData(),
            'desktop_stored_file_id' => $desktop->id,
            'mobile_stored_file_id' => $mobile->id,
        ]);
    }

    private function storedImage(string $path, string $category, User $admin): StoredFile
    {
        Storage::disk('local')->put($path, 'image');

        return StoredFile::create([
            'uuid' => fake()->uuid(),
            'disk' => 'local',
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => 'image/webp',
            'size' => 10,
            'visibility' => 'public',
            'category' => $category,
            'uploaded_by' => $admin->id,
        ]);
    }

    private function formData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'August promotion',
            'alt_text' => 'Promotion image',
            'link_url' => '/contact',
            'starts_at' => null,
            'ends_at' => null,
            'sort_order' => 10,
            'is_active' => true,
        ], $overrides);
    }
}
