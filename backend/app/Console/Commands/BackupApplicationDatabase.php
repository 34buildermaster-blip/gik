<?php

namespace App\Console\Commands;

use App\Models\StoredFile;
use App\Models\User;
use App\Notifications\SystemHealthAlertNotification;
use App\Services\MediaStorage;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Throwable;

class BackupApplicationDatabase extends Command
{
    protected $signature = 'system:backup-database';

    protected $description = 'Create a compressed database snapshot in the configured private media storage';

    public function handle(MediaStorage $storage): int
    {
        $path = tempnam(sys_get_temp_dir(), 'bm-backup-');
        if ($path === false) {
            return $this->failBackup('ไม่สามารถสร้างไฟล์ชั่วคราวสำหรับสำรองข้อมูลได้');
        }
        $gzipPath = $path.'.jsonl.gz';
        $encryptedPath = $gzipPath.'.enc';

        try {
            $handle = gzopen($gzipPath, 'wb9');
            if ($handle === false) {
                return $this->failBackup('ไม่สามารถเปิดไฟล์สำรองข้อมูลได้');
            }
            gzwrite($handle, json_encode(['format' => 'buildmaster-jsonl-v1', 'created_at' => now()->toIso8601String()])."\n");
            foreach ($this->tables() as $table) {
                foreach (DB::table($table)->cursor() as $row) {
                    gzwrite($handle, json_encode(['table' => $table, 'row' => (array) $row], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)."\n");
                }
            }
            gzclose($handle);

            $this->encryptBackup($gzipPath, $encryptedPath);
            $name = 'buildmaster-database-'.now()->format('Ymd-His').'.jsonl.gz.enc';
            $upload = new UploadedFile($encryptedPath, $name, 'application/octet-stream', null, true);
            $file = $storage->store($upload, 'system-backups', 'private');
            StoredFile::query()
                ->where('category', 'system-backups')
                ->whereKeyNot($file->id)
                ->where('created_at', '<', now()->subDays((int) config('operations.backup_retention_days', 14)))
                ->each(fn (StoredFile $old) => $storage->delete($old));
            $this->info("Backup created: {$file->uuid}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            return $this->failBackup('สำรองฐานข้อมูลไม่สำเร็จ: '.$exception->getMessage());
        } finally {
            @unlink($path);
            @unlink($gzipPath);
            @unlink($encryptedPath);
        }
    }

    private function tables(): array
    {
        $driver = DB::getDriverName();
        $tables = match ($driver) {
            'mysql', 'mariadb' => collect(DB::select('SHOW TABLES'))->map(fn ($row) => array_values((array) $row)[0]),
            'sqlite' => collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))->pluck('name'),
            'pgsql' => collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))->pluck('tablename'),
            default => collect(),
        };

        return $tables->reject(fn ($table) => in_array($table, ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'], true))->values()->all();
    }

    private function failBackup(string $message): int
    {
        $this->error($message);
        $admins = User::where('role', 'admin')->whereNull('disabled_at')->get();
        Notification::send($admins, new SystemHealthAlertNotification('การสำรองข้อมูลมีปัญหา', [$message]));

        return self::FAILURE;
    }

    private function encryptBackup(string $source, string $target): void
    {
        if (! function_exists('sodium_crypto_secretstream_xchacha20poly1305_init_push')) {
            throw new \RuntimeException('เซิร์ฟเวอร์ไม่ได้เปิด PHP Sodium จึงไม่สามารถเข้ารหัสไฟล์สำรองได้');
        }

        $configuredKey = (string) config('app.key');
        $rawKey = str_starts_with($configuredKey, 'base64:')
            ? base64_decode(substr($configuredKey, 7), true)
            : $configuredKey;
        if (! is_string($rawKey) || $rawKey === '') {
            throw new \RuntimeException('APP_KEY ไม่พร้อมสำหรับเข้ารหัสไฟล์สำรอง');
        }
        $key = hash('sha256', $rawKey, true);
        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);
        $input = fopen($source, 'rb');
        $output = fopen($target, 'wb');
        if ($input === false || $output === false) {
            throw new \RuntimeException('ไม่สามารถเปิดไฟล์เพื่อเข้ารหัสข้อมูลสำรองได้');
        }

        try {
            fwrite($output, "BMBAK1\n".$header);
            $chunk = fread($input, 1024 * 1024);
            while (is_string($chunk) && $chunk !== '') {
                $next = fread($input, 1024 * 1024);
                $tag = $next === ''
                    ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL
                    : SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE;
                fwrite($output, sodium_crypto_secretstream_xchacha20poly1305_push($state, $chunk, '', $tag));
                $chunk = $next;
            }
        } finally {
            fclose($input);
            fclose($output);
            sodium_memzero($key);
        }
    }
}
