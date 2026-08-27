<?php

namespace Tests\Feature;

use App\Models\HouseDesign;
use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HouseDesignManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_lists_published_designs_and_returns_detail_gallery(): void
    {
        HouseDesign::create($this->designData([
            'slug' => 'private-draft',
            'name' => 'Private Draft',
            'title' => 'แบบบ้านฉบับร่าง',
            'status' => 'draft',
            'published_at' => null,
        ]));

        $this->getJson('/api/house-designs')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'bm-courtyard'])
            ->assertJsonMissing(['slug' => 'private-draft']);

        $this->getJson('/api/house-designs/bm-courtyard')
            ->assertOk()
            ->assertJsonPath('data.title', 'บ้านโมเดิร์นคอร์ทยาร์ด')
            ->assertJsonPath('data.area', 285)
            ->assertJsonCount(3, 'data.gallery');
    }

    public function test_only_admin_can_manage_house_designs(): void
    {
        $customer = User::factory()->create();
        $inspector = User::factory()->inspector()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($customer)->get(route('admin.house-designs.index'))->assertForbidden();
        $this->actingAs($inspector)->get(route('admin.house-designs.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.house-designs.index'))->assertOk();
    }

    public function test_admin_can_create_design_with_cover_and_gallery_then_delete_files(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.house-designs.store'), [
                ...$this->formData(),
                'cover' => UploadedFile::fake()->create('cover.webp', 150, 'image/webp'),
                'gallery' => [
                    UploadedFile::fake()->create('living-room.webp', 120, 'image/webp'),
                    UploadedFile::fake()->create('garden.webp', 120, 'image/webp'),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.house-designs.index'));

        $design = HouseDesign::query()->where('slug', 'test-residence')->firstOrFail();
        $this->assertSame(['รับแสงธรรมชาติ', 'ปรับผังได้'], $design->features);
        $this->assertSame(2, $design->images()->count());

        $files = StoredFile::query()
            ->whereIn('category', ['house-designs-cover', 'house-designs-gallery'])
            ->get();
        $this->assertCount(3, $files);
        foreach ($files as $file) {
            Storage::disk('local')->assertExists($file->path);
        }

        $galleryImage = $design->images()->with('storedFile')->firstOrFail();
        $galleryPath = $galleryImage->storedFile->path;

        $this->actingAs($admin)
            ->delete(route('admin.house-designs.gallery.destroy', [$design, $galleryImage]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('house_design_images', ['id' => $galleryImage->id]);
        Storage::disk('local')->assertMissing($galleryPath);

        $remainingFiles = StoredFile::query()
            ->whereIn('category', ['house-designs-cover', 'house-designs-gallery'])
            ->get()
            ->map(fn (StoredFile $file): string => $file->path)
            ->all();

        $this->actingAs($admin)
            ->delete(route('admin.house-designs.destroy', $design))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('house_designs', ['id' => $design->id]);
        foreach ($remainingFiles as $path) {
            Storage::disk('local')->assertMissing($path);
        }
    }

    public function test_admin_can_update_design_and_gallery_metadata(): void
    {
        $admin = User::factory()->admin()->create();
        $design = HouseDesign::query()->where('slug', 'bm-courtyard')->firstOrFail();
        $image = $design->images()->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.house-designs.update', $design), [
                ...$this->formData([
                    'name' => $design->name,
                    'title' => 'บ้านคอร์ทยาร์ดฉบับปรับปรุง',
                    'slug' => $design->slug,
                    'status' => 'published',
                ]),
                'gallery_existing' => [
                    $image->id => [
                        'alt_text' => 'ภาพด้านหน้าหลังปรับปรุง',
                        'caption' => 'มุมมองใหม่',
                        'sort_order' => 5,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('house_designs', [
            'id' => $design->id,
            'title' => 'บ้านคอร์ทยาร์ดฉบับปรับปรุง',
        ]);
        $this->assertDatabaseHas('house_design_images', [
            'id' => $image->id,
            'alt_text' => 'ภาพด้านหน้าหลังปรับปรุง',
            'caption' => 'มุมมองใหม่',
            'sort_order' => 5,
        ]);
    }

    private function designData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Residence',
            'title' => 'บ้านทดสอบ',
            'slug' => 'test-residence',
            'style' => 'modern',
            'budget_category' => '5-10',
            'budget_label' => '5 - 7 ล้านบาท',
            'area' => 250,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'floors' => 2,
            'parking_spaces' => 2,
            'description' => 'รายละเอียดแบบบ้านสำหรับการทดสอบ',
            'concept' => 'แนวคิดการออกแบบสำหรับการทดสอบ',
            'features' => ['รับแสงธรรมชาติ'],
            'cover_image' => '/approach-homes/modern.jpg',
            'cover_alt' => 'แบบบ้านทดสอบ',
            'status' => 'published',
            'sort_order' => 500,
            'published_at' => now(),
        ], $overrides);
    }

    private function formData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Residence',
            'title' => 'บ้านทดสอบ',
            'slug' => 'test-residence',
            'style' => 'modern',
            'budget_category' => '5-10',
            'budget_label' => '5 - 7 ล้านบาท',
            'area' => 250,
            'bedrooms' => 4,
            'bathrooms' => 3,
            'floors' => 2,
            'parking_spaces' => 2,
            'description' => 'รายละเอียดแบบบ้านสำหรับการทดสอบ',
            'concept' => 'แนวคิดการออกแบบสำหรับการทดสอบ',
            'features_text' => "รับแสงธรรมชาติ\nปรับผังได้",
            'cover_alt' => 'แบบบ้านทดสอบ',
            'status' => 'draft',
            'sort_order' => 500,
            'seo_title' => 'แบบบ้านทดสอบ',
            'seo_description' => 'คำอธิบาย SEO แบบบ้านทดสอบ',
        ], $overrides);
    }
}
