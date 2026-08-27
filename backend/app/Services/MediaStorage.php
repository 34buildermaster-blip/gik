<?php

namespace App\Services;

use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStorage
{
    public function __construct(
        private readonly GoogleDriveStorage $googleDrive,
        private readonly ImageOptimizer $imageOptimizer,
        private readonly UploadSecurityScanner $securityScanner,
    ) {}

    public function store(
        UploadedFile $file,
        string $category,
        string $visibility = 'private',
        ?User $uploader = null,
    ): StoredFile {
        $security = $this->securityScanner->inspect($file);
        $optimizedFile = $this->imageOptimizer->optimize($file);
        $storedUpload = $optimizedFile ?? $file;

        try {
            $driver = (string) config('media.driver', 'local');
            $mimeType = $storedUpload->getMimeType() ?: 'application/octet-stream';
            $size = $storedUpload->getSize() ?: 0;

            if ($driver === 'google') {
                $path = $this->googleDrive->upload(
                    $storedUpload->getRealPath(),
                    $storedUpload->getClientOriginalName(),
                    $mimeType,
                    $category,
                );
            } elseif ($driver === 'local') {
                $disk = (string) config('media.local.disk', 'local');
                $extension = $storedUpload->guessExtension() ?: $storedUpload->getClientOriginalExtension() ?: 'bin';
                $path = $storedUpload->storeAs($category, Str::uuid().'.'.$extension, $disk);
                if (! $path) {
                    throw new RuntimeException('Unable to store the uploaded file.');
                }
            } else {
                throw new RuntimeException("Unsupported media storage driver [{$driver}].");
            }

            try {
                return StoredFile::create([
                    'uuid' => (string) Str::uuid(),
                    'disk' => $driver,
                    'path' => $path,
                    'original_name' => $storedUpload->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size' => $size,
                    'sha256' => $security['sha256'],
                    'scan_status' => $security['scan_status'],
                    'scanned_at' => $security['scanned_at'],
                    'visibility' => $visibility,
                    'category' => $category,
                    'uploaded_by' => $uploader?->id,
                ]);
            } catch (\Throwable $exception) {
                $this->deleteObject($driver, $path);
                throw $exception;
            }
        } finally {
            if ($optimizedFile && is_file($optimizedFile->getRealPath())) {
                @unlink($optimizedFile->getRealPath());
            }
        }
    }

    public function response(
        StoredFile $file,
        string $cacheControl = 'private, max-age=3600',
        string $disposition = 'inline',
    ): StreamedResponse {
        if (config('security.upload_scan.require_clean_for_serving')) {
            abort_unless($file->scan_status === 'clean', 404);
        }

        if ($file->disk !== 'google') {
            abort_unless($this->exists($file), 404);
        }

        $range = request()->header('Range');
        $headers = [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => $disposition."; filename*=UTF-8''".rawurlencode($file->original_name),
            'Cache-Control' => $cacheControl,
            'X-Content-Type-Options' => 'nosniff',
            'Accept-Ranges' => 'bytes',
        ];

        if ($file->disk === 'google') {
            return $this->googleResponse($file, $range, $headers);
        }

        return $this->localResponse($file, $range, $headers);
    }

    private function localResponse(StoredFile $file, ?string $range, array $headers): StreamedResponse
    {
        $size = $file->size > 0
            ? $file->size
            : Storage::disk((string) config('media.local.disk', 'local'))->size($file->path);
        [$start, $end, $status] = $this->parseRange($range, $size);
        $length = max(0, $end - $start + 1);

        $headers['Content-Length'] = (string) $length;
        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        return response()->stream(function () use ($file, $start, $length): void {
            $stream = Storage::disk((string) config('media.local.disk', 'local'))->readStream($file->path);

            if ($stream === false) {
                return;
            }

            try {
                if ($start > 0) {
                    fseek($stream, $start);
                }

                $remaining = $length;
                while ($remaining > 0 && ! feof($stream)) {
                    $chunk = fread($stream, min(1024 * 1024, $remaining));
                    if ($chunk === false || $chunk === '') {
                        break;
                    }
                    echo $chunk;
                    $remaining -= strlen($chunk);
                }
            } finally {
                fclose($stream);
            }
        }, $status, $headers);
    }

    private function googleResponse(StoredFile $file, ?string $range, array $headers): StreamedResponse
    {
        $remote = $this->googleDrive->download($file->path, $range);
        abort_if($remote->getStatusCode() === 404, 404);
        abort_unless(in_array($remote->getStatusCode(), [200, 206], true), 502);

        foreach (['Content-Length', 'Content-Range', 'Accept-Ranges'] as $header) {
            if ($remote->hasHeader($header)) {
                $headers[$header] = $remote->getHeaderLine($header);
            }
        }

        return response()->stream(function () use ($remote): void {
            $body = $remote->getBody();
            while (! $body->eof()) {
                echo $body->read(1024 * 1024);
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                flush();
            }
        }, $remote->getStatusCode(), $headers);
    }

    private function parseRange(?string $range, int $size): array
    {
        if ($size <= 0) {
            return [0, -1, 200];
        }

        if (! $range || preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $matches) !== 1) {
            return [0, max(0, $size - 1), 200];
        }

        if ($matches[1] === '' && $matches[2] !== '') {
            $suffixLength = min((int) $matches[2], $size);

            return [max(0, $size - $suffixLength), max(0, $size - 1), 206];
        }

        $start = (int) $matches[1];
        $end = $matches[2] === '' ? $size - 1 : min((int) $matches[2], $size - 1);
        abort_if($start < 0 || $start >= $size || $end < $start, 416);

        return [$start, $end, 206];
    }

    public function exists(StoredFile $file): bool
    {
        if ($file->disk === 'google') {
            return $this->googleDrive->exists($file->path);
        }

        return Storage::disk((string) config('media.local.disk', 'local'))->exists($file->path);
    }

    public function delete(?StoredFile $file): void
    {
        if (! $file) {
            return;
        }

        $this->deleteObject($file->disk, $file->path);
        $file->delete();
    }

    public function rescan(StoredFile $file): StoredFile
    {
        $temporaryPath = null;
        $path = $file->disk === 'google'
            ? $temporaryPath = $this->downloadToTemporaryFile($file)
            : Storage::disk((string) config('media.local.disk', 'local'))->path($file->path);

        try {
            $security = $this->securityScanner->inspectPath($path);
            $file->forceFill($security)->save();

            return $file->refresh();
        } finally {
            if ($temporaryPath && is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    private function downloadToTemporaryFile(StoredFile $file): string
    {
        $response = $this->googleDrive->download($file->path);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("Unable to download stored file [{$file->uuid}] for scanning.");
        }

        $path = tempnam(sys_get_temp_dir(), 'bm-scan-');
        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary scan file.');
        }

        $target = fopen($path, 'wb');
        if ($target === false) {
            @unlink($path);
            throw new RuntimeException('Unable to open a temporary scan file.');
        }

        try {
            $body = $response->getBody();
            while (! $body->eof()) {
                $chunk = $body->read(1024 * 1024);
                if ($chunk !== '' && fwrite($target, $chunk) === false) {
                    throw new RuntimeException('Unable to write a temporary scan file.');
                }
            }
        } catch (\Throwable $exception) {
            fclose($target);
            @unlink($path);
            throw $exception;
        }

        fclose($target);

        return $path;
    }

    private function deleteObject(string $driver, string $path): void
    {
        if ($driver === 'google') {
            $this->googleDrive->delete($path);

            return;
        }

        Storage::disk((string) config('media.local.disk', 'local'))->delete($path);
    }
}
