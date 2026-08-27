<?php

namespace Tests\Feature;

use App\Services\MediaStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_image_is_resized_and_stored_as_webp(): void
    {
        Storage::fake('local');
        config()->set('media.driver', 'local');
        config()->set('media.images.optimize', true);
        config()->set('media.images.quality', 80);
        config()->set('media.images.max_width', 800);
        config()->set('media.images.max_height', 800);

        $storedFile = app(MediaStorage::class)->store(
            UploadedFile::fake()->image('large-house.jpg', 2400, 1600),
            'test-images',
            'public',
        );

        Storage::disk('local')->assertExists($storedFile->path);
        $imageInfo = getimagesize(Storage::disk('local')->path($storedFile->path));

        $this->assertSame('image/webp', $storedFile->mime_type);
        $this->assertSame('large-house.webp', $storedFile->original_name);
        $this->assertStringEndsWith('.webp', $storedFile->path);
        $this->assertSame('image/webp', $imageInfo['mime']);
        $this->assertLessThanOrEqual(800, $imageInfo[0]);
        $this->assertLessThanOrEqual(800, $imageInfo[1]);
    }

    public function test_non_image_upload_keeps_its_original_format(): void
    {
        Storage::fake('local');
        config()->set('media.driver', 'local');
        config()->set('media.images.optimize', true);

        $storedFile = app(MediaStorage::class)->store(
            UploadedFile::fake()->create('drawing.pdf', 100, 'application/pdf'),
            'test-documents',
        );

        Storage::disk('local')->assertExists($storedFile->path);
        $this->assertSame('application/pdf', $storedFile->mime_type);
        $this->assertSame('drawing.pdf', $storedFile->original_name);
        $this->assertStringEndsWith('.pdf', $storedFile->path);
    }
}
