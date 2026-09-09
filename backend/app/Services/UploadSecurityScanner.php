<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class UploadSecurityScanner
{
    /** @return array{sha256: string, scan_status: string, scanned_at: \DateTimeInterface|null} */
    public function inspect(UploadedFile $file): array
    {
        return $this->inspectPath($file->getRealPath(), $file->getClientOriginalName());
    }

    /** @return array{sha256: string, scan_status: string, scanned_at: \DateTimeInterface|null} */
    public function inspectPath(string $sourcePath, ?string $originalName = null): array
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

            $driver = (string) config('security.upload_scan.driver', 'clamav');
            if ($driver === 'builtin') {
                return $this->inspectWithBuiltinRules($path, $sha256, $originalName);
            }

            if ($driver !== 'clamav') {
                return $this->scannerFailure(
                    new RuntimeException("Unsupported upload scanner driver [{$driver}]."),
                    $sha256,
                );
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

    /** @return array{sha256: string, scan_status: string, scanned_at: \DateTimeInterface} */
    private function inspectWithBuiltinRules(string $path, string $sha256, ?string $originalName): array
    {
        $head = file_get_contents($path, false, null, 0, 8192);
        if ($head === false) {
            return $this->scannerFailure(new RuntimeException('Unable to inspect the uploaded file.'), $sha256);
        }

        $blockedMagic = [
            'MZ',
            "\x7FELF",
            "\xCF\xFA\xED\xFE",
            "\xCE\xFA\xED\xFE",
            "\xFE\xED\xFA\xCF",
            "\xFE\xED\xFA\xCE",
        ];
        foreach ($blockedMagic as $magic) {
            if (str_starts_with($head, $magic)) {
                $this->rejectUnsafeFile('ตรวจพบไฟล์โปรแกรมที่ไม่อนุญาต');
            }
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';
        if (in_array($mimeType, [
            'application/x-dosexec',
            'application/x-executable',
            'application/x-httpd-php',
            'application/x-sharedlib',
            'application/x-shellscript',
            'text/html',
            'text/javascript',
            'text/x-php',
        ], true)) {
            $this->rejectUnsafeFile('ชนิดไฟล์นี้ไม่ได้รับอนุญาตเพื่อความปลอดภัย');
        }

        $extension = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));

        if ($this->fileContainsAny($path, [
            'X5O!P%@AP[4\\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*',
            '<?php',
        ], true)) {
            $this->rejectUnsafeFile('ตรวจพบเนื้อหาที่อาจเป็นอันตราย');
        }

        if ($this->isTextLike($mimeType, $extension)
            && $this->fileContainsAny($path, ['<?='], true)) {
            $this->rejectUnsafeFile('ตรวจพบเนื้อหาที่อาจเป็นอันตราย');
        }

        if ($extension === 'pdf' || $mimeType === 'application/pdf') {
            if (! str_starts_with(ltrim($head), '%PDF-')) {
                $this->rejectUnsafeFile('โครงสร้างไฟล์ PDF ไม่ถูกต้อง');
            }

            if ($this->fileContainsAny($path, [
                '/javascript',
                '/js ',
                '/js<',
                '/js(',
                '/js[',
                '/launch',
                '/openaction',
                '/embeddedfile',
                '/richmedia',
            ], true)) {
                $this->rejectUnsafeFile('ไฟล์ PDF มีคำสั่งหรือไฟล์ฝังที่ไม่อนุญาต');
            }
        }

        if (in_array($extension, ['docx', 'xlsx'], true)) {
            $this->inspectOfficeArchive($path);
        }

        return ['sha256' => $sha256, 'scan_status' => 'validated', 'scanned_at' => now()];
    }

    private function isTextLike(string $mimeType, string $extension): bool
    {
        return str_starts_with($mimeType, 'text/')
            || in_array($mimeType, ['application/json', 'application/xml', 'image/svg+xml'], true)
            || in_array($extension, ['csv', 'htm', 'html', 'json', 'svg', 'txt', 'xml'], true);
    }

    private function inspectOfficeArchive(string $path): void
    {
        $archive = new ZipArchive;
        if ($archive->open($path) !== true) {
            $this->rejectUnsafeFile('โครงสร้างไฟล์ Office ไม่ถูกต้อง');
        }

        try {
            if ($archive->locateName('[Content_Types].xml') === false) {
                $this->rejectUnsafeFile('โครงสร้างไฟล์ Office ไม่สมบูรณ์');
            }

            for ($index = 0; $index < $archive->numFiles; $index++) {
                $entry = strtolower((string) $archive->getNameIndex($index));
                if (str_contains($entry, 'vbaproject.bin')
                    || str_contains($entry, '/embeddings/')
                    || preg_match('/\.(?:exe|dll|js|vbs|ps1|bat|cmd|scr|com|jar)$/', $entry) === 1) {
                    $this->rejectUnsafeFile('ไฟล์ Office มี Macro หรือไฟล์แนบที่ไม่อนุญาต');
                }
            }
        } finally {
            $archive->close();
        }
    }

    private function fileContainsAny(string $path, array $needles, bool $caseInsensitive = false): bool
    {
        $stream = fopen($path, 'rb');
        if ($stream === false) {
            throw new RuntimeException('Unable to open the quarantined file for inspection.');
        }

        $overlap = '';
        $maxNeedleLength = max(array_map('strlen', $needles));

        try {
            while (! feof($stream)) {
                $chunk = fread($stream, 1024 * 1024);
                if ($chunk === false) {
                    throw new RuntimeException('Unable to read the quarantined file.');
                }

                $haystack = $overlap.$chunk;
                foreach ($needles as $needle) {
                    $found = $caseInsensitive
                        ? stripos($haystack, $needle) !== false
                        : strpos($haystack, $needle) !== false;
                    if ($found) {
                        return true;
                    }
                }

                $overlap = substr($haystack, -max(0, $maxNeedleLength - 1));
            }
        } finally {
            fclose($stream);
        }

        return false;
    }

    private function rejectUnsafeFile(string $message): never
    {
        throw ValidationException::withMessages([
            'file' => $message.' กรุณาตรวจสอบไฟล์แล้วอัปโหลดใหม่',
        ]);
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
