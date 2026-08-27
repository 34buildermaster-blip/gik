<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\Process\Process;

class UploadSecurityScanner
{
    /** @return array{sha256: string, scan_status: string, scanned_at: \DateTimeInterface|null} */
    public function inspect(UploadedFile $file): array
    {
        return $this->inspectPath($file->getRealPath());
    }

    /** @return array{sha256: string, scan_status: string, scanned_at: \DateTimeInterface|null} */
    public function inspectPath(string $sourcePath): array
    {
        $disk = (string) config('security.upload_scan.quarantine_disk', 'local');
        $directory = trim((string) config('security.upload_scan.quarantine_path', 'quarantine'), '/');
        $name = Str::uuid().'.'.(pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'bin');
        $relativePath = $directory.'/'.$name;
        $stream = fopen($sourcePath, 'rb');

        if ($stream === false || ! Storage::disk($disk)->put($relativePath, $stream)) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw new RuntimeException('Unable to quarantine the uploaded file.');
        }
        fclose($stream);

        try {
            $path = Storage::disk($disk)->path($relativePath);
            $sha256 = hash_file('sha256', $path);
            if ($sha256 === false) {
                throw new RuntimeException('Unable to fingerprint the uploaded file.');
            }

            if (! config('security.upload_scan.enabled')) {
                if (config('security.upload_scan.required')) {
                    return $this->scannerFailure(
                        new RuntimeException('Upload scanning is required but disabled.'),
                        $sha256,
                    );
                }

                return ['sha256' => $sha256, 'scan_status' => 'not_scanned', 'scanned_at' => null];
            }

            $process = new Process([
                (string) config('security.upload_scan.binary', 'clamscan'),
                '--no-summary',
                '--stdout',
                $path,
            ]);
            $process->setTimeout(max(10, (int) config('security.upload_scan.timeout', 120)));

            try {
                $process->run();
            } catch (\Throwable $exception) {
                return $this->scannerFailure($exception, $sha256);
            }

            if ($process->getExitCode() === 1) {
                Log::warning('Malware upload blocked', ['sha256' => $sha256]);
                throw ValidationException::withMessages([
                    'file' => 'ไฟล์ไม่ผ่านการตรวจสอบความปลอดภัย กรุณาตรวจสอบไฟล์แล้วอัปโหลดใหม่',
                ]);
            }

            if (! $process->isSuccessful()) {
                return $this->scannerFailure(
                    new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput())),
                    $sha256,
                );
            }

            return ['sha256' => $sha256, 'scan_status' => 'clean', 'scanned_at' => now()];
        } finally {
            Storage::disk($disk)->delete($relativePath);
        }
    }

    /** @return array{sha256: string, scan_status: string, scanned_at: null} */
    private function scannerFailure(\Throwable $exception, string $sha256): array
    {
        Log::error('Upload scanner unavailable', [
            'sha256' => $sha256,
            'message' => $exception->getMessage(),
        ]);

        if (config('security.upload_scan.fail_closed', true)) {
            throw ValidationException::withMessages([
                'file' => 'ระบบตรวจสอบไฟล์ไม่พร้อมใช้งาน จึงระงับการอัปโหลดไว้ชั่วคราว',
            ]);
        }

        return ['sha256' => $sha256, 'scan_status' => 'scanner_unavailable', 'scanned_at' => null];
    }
}
