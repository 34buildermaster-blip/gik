<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\StoredFile;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function __construct(private readonly MediaStorage $mediaStorage) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::groupedValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name_th' => ['required', 'string', 'max:160'],
            'company_name_en' => ['required', 'string', 'max:160'],
            'tagline' => ['required', 'string', 'max:255'],
            'phone_display' => ['required', 'string', 'max:40'],
            'phone_href' => ['required', 'string', 'max:60', 'starts_with:tel:'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'service_area' => ['required', 'string', 'max:255'],
            'business_hours' => ['nullable', 'string', 'max:255'],
            'copyright' => ['required', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'line_url' => ['nullable', 'url', 'max:500'],
            'tiktok_url' => ['nullable', 'url', 'max:500'],
            'consultation_label' => ['required', 'string', 'max:80'],
            'tracking_label' => ['required', 'string', 'max:80'],
            'contact_heading' => ['required', 'string', 'max:180'],
            'contact_description' => ['required', 'string', 'max:1000'],
            'default_title' => ['required', 'string', 'max:70'],
            'default_description' => ['required', 'string', 'max:180'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico', 'max:2048'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ]);

        $booleanKeys = [
            'show_house_designs', 'show_updates', 'show_blog', 'show_faq',
            'show_home_services', 'show_home_projects', 'show_home_process',
            'show_home_partners', 'show_home_reviews', 'show_home_contact',
        ];

        foreach (SiteSetting::DEFINITIONS as $definitions) {
            foreach ($definitions as $key => $definition) {
                if ($definition['type'] === 'image') {
                    continue;
                }

                $value = in_array($key, $booleanKeys, true)
                    ? ($request->boolean($key) ? '1' : '0')
                    : ($validated[$key] ?? '');

                $meta = SiteSetting::definitionFor($key);
                SiteSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['group' => $meta['group'], 'type' => $meta['type'], 'value' => $value],
                );
            }
        }

        foreach (['logo' => 'logo_path', 'footer_logo' => 'footer_logo_path', 'favicon' => 'favicon_path', 'og_image' => 'og_image_path'] as $input => $key) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $existing = SiteSetting::query()->where('key', $key)->value('value');
            $file = $this->mediaStorage->store(
                $request->file($input),
                'site-settings',
                'public',
                $request->user(),
            );
            $meta = SiteSetting::definitionFor($key);

            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => $meta['group'], 'type' => 'image', 'value' => 'media:'.$file->uuid],
            );

            if ($existing && str_starts_with($existing, 'media:')) {
                $oldFile = StoredFile::query()->where('uuid', substr($existing, 6))->first();
                $this->mediaStorage->delete($oldFile);
            } elseif ($existing) {
                Storage::disk('public')->delete($existing);
            }
        }

        return back()->with('success', 'บันทึกการตั้งค่าเว็บไซต์เรียบร้อยแล้ว');
    }
}
