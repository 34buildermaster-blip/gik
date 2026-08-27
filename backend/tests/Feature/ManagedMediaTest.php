<?php

namespace Tests\Feature;

use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagedMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_media_is_public_through_an_application_url(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.articles.media'), [
            'media' => UploadedFile::fake()->create('room.webp', 100, 'image/webp'),
        ])->assertOk();

        $file = StoredFile::query()->sole();
        $this->assertSame('public', $file->visibility);
        $this->assertSame('article-media', $file->category);
        $this->assertSame($file->publicUrl(), $response->json('url'));
        Storage::disk('local')->assertExists($file->path);

        $this->get($file->publicUrl())
            ->assertOk()
            ->assertHeader('content-type', 'image/webp');
    }

    public function test_private_managed_file_is_not_exposed_by_the_public_route(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('private/file.jpg', 'private');

        $file = StoredFile::create([
            'uuid' => fake()->uuid(),
            'disk' => 'local',
            'path' => 'private/file.jpg',
            'original_name' => 'file.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 7,
            'visibility' => 'private',
            'category' => 'project-updates-1',
        ]);

        $this->get($file->publicUrl())->assertNotFound();
    }

    public function test_public_media_supports_byte_range_requests_for_video_playback(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('article-media/demo.mp4', '0123456789');

        $file = StoredFile::create([
            'uuid' => fake()->uuid(),
            'disk' => 'local',
            'path' => 'article-media/demo.mp4',
            'original_name' => 'demo.mp4',
            'mime_type' => 'video/mp4',
            'size' => 10,
            'visibility' => 'public',
            'category' => 'article-media',
        ]);

        $response = $this->withHeader('Range', 'bytes=2-5')
            ->get($file->publicUrl())
            ->assertStatus(206)
            ->assertHeader('content-range', 'bytes 2-5/10')
            ->assertHeader('content-length', '4');

        $this->assertSame('2345', $response->streamedContent());
    }
}
