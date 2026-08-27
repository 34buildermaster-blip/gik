<?php

namespace Tests\Feature;

use App\Models\HomeSlide;
use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeSlideManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_returns_active_slides_in_display_order(): void
    {
        HomeSlide::query()->where('section', HomeSlide::SECTION_HERO)->update(['is_active' => false]);

        HomeSlide::create($this->slideData([
            'title' => 'Second',
            'sort_order' => 20,
        ]));
        HomeSlide::create($this->slideData([
            'title' => 'First',
            'sort_order' => 10,
        ]));
        HomeSlide::create($this->slideData([
            'title' => 'Hidden',
            'sort_order' => 1,
            'is_active' => false,
        ]));

        $this->getJson('/api/home-slides')
            ->assertOk()
            ->assertJsonPath('data.hero.0.title', 'First')
            ->assertJsonPath('data.hero.1.title', 'Second')
            ->assertJsonMissing(['title' => 'Hidden']);
    }

    public function test_only_admin_can_manage_home_slides(): void
    {
        $member = User::factory()->create();
        $inspector = User::factory()->inspector()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($member)->get(route('admin.home-slides.index'))->assertForbidden();
        $this->actingAs($inspector)->get(route('admin.home-slides.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.home-slides.index'))->assertOk();
    }

    public function test_admin_can_create_slide_with_managed_public_image(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.home-slides.store'), [
                ...$this->slideData(['section' => HomeSlide::SECTION_APPROACH]),
                'image' => UploadedFile::fake()->create('new-home.webp', 120, 'image/webp'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.home-slides.index'));

        $slide = HomeSlide::query()->where('title', 'Test slide')->firstOrFail();
        $file = StoredFile::query()->findOrFail($slide->stored_file_id);

        $this->assertSame('public', $file->visibility);
        $this->assertSame('home-slides-approach', $file->category);
        Storage::disk('local')->assertExists($file->path);
    }

    public function test_admin_can_replace_slide_image_and_old_file_is_removed(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        $oldFile = StoredFile::create([
            'uuid' => fake()->uuid(),
            'disk' => 'local',
            'path' => 'home-slides-hero/old.webp',
            'original_name' => 'old.webp',
            'mime_type' => 'image/webp',
            'size' => 10,
            'visibility' => 'public',
            'category' => 'home-slides-hero',
            'uploaded_by' => $admin->id,
        ]);
        Storage::disk('local')->put($oldFile->path, 'old');

        $slide = HomeSlide::create($this->slideData(['stored_file_id' => $oldFile->id]));

        $this->actingAs($admin)
            ->put(route('admin.home-slides.update', $slide), [
                ...$this->slideData(['title' => 'Updated slide']),
                'image' => UploadedFile::fake()->create('replacement.webp', 120, 'image/webp'),
            ])
            ->assertSessionHasNoErrors();

        $slide->refresh();
        $this->assertNotSame($oldFile->id, $slide->stored_file_id);
        $this->assertDatabaseMissing('stored_files', ['id' => $oldFile->id]);
        Storage::disk('local')->assertMissing($oldFile->path);
    }

    public function test_last_active_slide_in_a_section_cannot_be_disabled_or_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        HomeSlide::query()
            ->where('section', HomeSlide::SECTION_HERO)
            ->where('id', '!=', HomeSlide::query()->where('section', HomeSlide::SECTION_HERO)->value('id'))
            ->update(['is_active' => false]);
        $slide = HomeSlide::query()
            ->where('section', HomeSlide::SECTION_HERO)
            ->where('is_active', true)
            ->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.home-slides.update', $slide), [
                ...$this->slideData([
                    'title' => $slide->title,
                    'section' => $slide->section,
                    'is_active' => null,
                ]),
            ])
            ->assertSessionHasErrors('is_active');

        $this->actingAs($admin)
            ->delete(route('admin.home-slides.destroy', $slide))
            ->assertSessionHasErrors('slide');

        $this->assertDatabaseHas('home_slides', ['id' => $slide->id]);
    }

    private function slideData(array $overrides = []): array
    {
        return array_merge([
            'section' => HomeSlide::SECTION_HERO,
            'image_path' => '/hero-construction.png',
            'eyebrow' => 'DESIGN',
            'title' => 'Test slide',
            'title_line_2' => 'Second line',
            'description' => 'Slide description',
            'label' => 'LABEL',
            'alt_text' => 'Accessible image description',
            'sort_order' => 10,
            'is_active' => true,
        ], $overrides);
    }
}
