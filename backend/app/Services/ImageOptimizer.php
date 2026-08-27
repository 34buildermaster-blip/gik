<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class ImageOptimizer
{
    /**
     * Convert supported uploads to a reasonably sized WebP image.
     *
     * The returned file is temporary and must be deleted by the caller.
     */
    public function optimize(UploadedFile $file): ?UploadedFile
    {
        if (! config('media.images.optimize', true)) {
            return null;
        }

        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        if (! function_exists('imagewebp')) {
            throw new RuntimeException('PHP GD with WebP support is required for image uploads.');
        }

        $sourcePath = $file->getRealPath();
        $source = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => false,
        };

        if ($source === false) {
            return null;
        }

        try {
            if ($mimeType === 'image/jpeg') {
                $source = $this->orientJpeg($source, $sourcePath);
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $maxWidth = max(1, (int) config('media.images.max_width', 2560));
            $maxHeight = max(1, (int) config('media.images.max_height', 2560));
            $scale = min(1, $maxWidth / $width, $maxHeight / $height);
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));

            $output = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($output === false) {
                throw new RuntimeException('Unable to prepare the optimized image.');
            }

            try {
                imagealphablending($output, false);
                imagesavealpha($output, true);
                $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
                imagefilledrectangle($output, 0, 0, $targetWidth, $targetHeight, $transparent);

                if (! imagecopyresampled(
                    $output,
                    $source,
                    0,
                    0,
                    0,
                    0,
                    $targetWidth,
                    $targetHeight,
                    $width,
                    $height,
                )) {
                    throw new RuntimeException('Unable to resize the uploaded image.');
                }

                $temporaryPath = tempnam(sys_get_temp_dir(), 'bm-webp-');
                if ($temporaryPath === false) {
                    throw new RuntimeException('Unable to create a temporary image file.');
                }

                $quality = min(100, max(1, (int) config('media.images.quality', 82)));
                if (! imagewebp($output, $temporaryPath, $quality)) {
                    @unlink($temporaryPath);
                    throw new RuntimeException('Unable to convert the uploaded image to WebP.');
                }
            } finally {
                imagedestroy($output);
            }
        } finally {
            imagedestroy($source);
        }

        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $baseName = $baseName !== '' ? $baseName : 'image';

        return new UploadedFile(
            $temporaryPath,
            $baseName.'.webp',
            'image/webp',
            UPLOAD_ERR_OK,
            true,
        );
    }

    private function orientJpeg(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($path)['Orientation'] ?? 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
