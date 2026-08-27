<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_returns_default_site_settings(): void
    {
        $this->getJson('/api/site-settings')
            ->assertOk()
            ->assertJsonPath('data.general.company_name_en', '34 Build Master Construction')
            ->assertJsonPath('data.display.show_home_reviews', true);
    }

    public function test_only_admin_can_open_site_settings(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($member)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();
    }

    public function test_admin_can_update_site_settings_and_public_api_reflects_changes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->validPayload([
                'phone_display' => '099-999-9999',
                'show_home_reviews' => null,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', [
            'key' => 'phone_display',
            'value' => '099-999-9999',
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'show_home_reviews',
            'value' => '0',
        ]);

        $this->getJson('/api/site-settings')
            ->assertOk()
            ->assertJsonPath('data.general.phone_display', '099-999-9999')
            ->assertJsonPath('data.display.show_home_reviews', false);
    }

    public function test_admin_uploaded_brand_image_uses_managed_public_storage(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->validPayload([
                'logo' => UploadedFile::fake()->create('logo.png', 80, 'image/png'),
            ]))
            ->assertSessionHasNoErrors();

        $value = SiteSetting::query()->where('key', 'logo_path')->value('value');
        $this->assertStringStartsWith('media:', $value);

        $url = $this->getJson('/api/site-settings')
            ->assertOk()
            ->json('data.branding.logo_url');

        $this->get($url)
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    private function validPayload(array $overrides = []): array
    {
        $settings = SiteSetting::groupedValues();

        return array_merge([
            ...$settings['general'],
            ...$settings['social'],
            ...$settings['cta'],
            ...$settings['navigation'],
            ...$settings['display'],
            'default_title' => $settings['seo']['default_title'],
            'default_description' => $settings['seo']['default_description'],
        ], $overrides);
    }
}
