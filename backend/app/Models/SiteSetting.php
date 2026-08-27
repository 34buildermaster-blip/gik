<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['group', 'key', 'value', 'type'])]
class SiteSetting extends Model
{
    public const DEFINITIONS = [
        'general' => [
            'company_name_th' => ['type' => 'text', 'default' => '34 บิลด์ มาสเตอร์ คอนสตรัคชั่น'],
            'company_name_en' => ['type' => 'text', 'default' => '34 Build Master Construction'],
            'tagline' => ['type' => 'text', 'default' => 'รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจร'],
            'phone_display' => ['type' => 'text', 'default' => '081-9512-297'],
            'phone_href' => ['type' => 'text', 'default' => 'tel:+66819512297'],
            'email' => ['type' => 'email', 'default' => '34buildmaster@gmail.com'],
            'address' => ['type' => 'textarea', 'default' => '161/26 หมู่ 4 ตำบลหนองป่าครั่ง อำเภอเมืองเชียงใหม่ จังหวัดเชียงใหม่ 50000'],
            'service_area' => ['type' => 'text', 'default' => 'เชียงใหม่ และพื้นที่ใกล้เคียง'],
            'business_hours' => ['type' => 'text', 'default' => 'จันทร์–เสาร์ 08:30–17:30 น.'],
            'copyright' => ['type' => 'text', 'default' => '© 2026 34 Build Master Construction.'],
        ],
        'branding' => [
            'logo_path' => ['type' => 'image', 'default' => null],
            'footer_logo_path' => ['type' => 'image', 'default' => null],
            'favicon_path' => ['type' => 'image', 'default' => null],
        ],
        'social' => [
            'facebook_url' => ['type' => 'url', 'default' => 'https://www.facebook.com/34BuildMasterConstruction'],
            'instagram_url' => ['type' => 'url', 'default' => 'https://www.instagram.com/34buildmaster'],
            'line_url' => ['type' => 'url', 'default' => 'https://line.me/R/ti/p/@34buildmaster'],
            'tiktok_url' => ['type' => 'url', 'default' => 'https://www.tiktok.com/@34buildmaster'],
        ],
        'cta' => [
            'consultation_label' => ['type' => 'text', 'default' => 'ปรึกษาโครงการ'],
            'tracking_label' => ['type' => 'text', 'default' => 'ติดตามความคืบหน้า'],
            'contact_heading' => ['type' => 'text', 'default' => 'เริ่มต้นบ้านที่ใช่ ด้วยการวางแผนที่ชัดเจน'],
            'contact_description' => ['type' => 'textarea', 'default' => 'เล่าไอเดีย พื้นที่ และงบประมาณเบื้องต้นให้เรา ทีมงานจะติดต่อกลับเพื่อช่วยประเมินแนวทางที่เหมาะกับโครงการ'],
        ],
        'navigation' => [
            'show_house_designs' => ['type' => 'boolean', 'default' => true],
            'show_updates' => ['type' => 'boolean', 'default' => true],
            'show_blog' => ['type' => 'boolean', 'default' => true],
            'show_faq' => ['type' => 'boolean', 'default' => true],
        ],
        'display' => [
            'show_home_services' => ['type' => 'boolean', 'default' => true],
            'show_home_projects' => ['type' => 'boolean', 'default' => true],
            'show_home_process' => ['type' => 'boolean', 'default' => true],
            'show_home_partners' => ['type' => 'boolean', 'default' => true],
            'show_home_reviews' => ['type' => 'boolean', 'default' => true],
            'show_home_contact' => ['type' => 'boolean', 'default' => true],
        ],
        'seo' => [
            'default_title' => ['type' => 'text', 'default' => '34 Build Master | รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อิน'],
            'default_description' => ['type' => 'textarea', 'default' => 'รับออกแบบ รีโนเวท สร้างบ้าน และบิวท์อินครบวงจรในเชียงใหม่ วางแผนงานชัดเจน ดูแลคุณภาพ และสื่อสารกับเจ้าของบ้านทุกขั้นตอน'],
            'og_image_path' => ['type' => 'image', 'default' => null],
        ],
    ];

    public static function groupedValues(): array
    {
        $saved = self::query()->pluck('value', 'key');
        $groups = [];

        foreach (self::DEFINITIONS as $group => $definitions) {
            foreach ($definitions as $key => $definition) {
                $rawValue = $saved->has($key) ? $saved->get($key) : $definition['default'];
                $groups[$group][$key] = self::castValue($rawValue, $definition['type']);
            }
        }

        foreach (['logo_path', 'footer_logo_path', 'favicon_path', 'og_image_path'] as $imageKey) {
            $group = $imageKey === 'og_image_path' ? 'seo' : 'branding';
            $path = $groups[$group][$imageKey] ?? null;
            $groups[$group][str_replace('_path', '_url', $imageKey)] = self::imageUrl($path);
        }

        return $groups;
    }

    public static function definitionFor(string $key): ?array
    {
        foreach (self::DEFINITIONS as $group => $definitions) {
            if (array_key_exists($key, $definitions)) {
                return ['group' => $group, ...$definitions[$key]];
            }
        }

        return null;
    }

    private static function castValue(mixed $value, string $type): mixed
    {
        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    private static function imageUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'media:')) {
            $file = StoredFile::query()->where('uuid', substr($value, 6))->first();

            return $file?->publicUrl();
        }

        return url(Storage::disk('public')->url($value));
    }
}
