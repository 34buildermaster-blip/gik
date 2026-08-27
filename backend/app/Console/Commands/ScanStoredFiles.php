<?php

namespace App\Console\Commands;

use App\Models\StoredFile;
use App\Services\MediaStorage;
use Illuminate\Console\Command;

class ScanStoredFiles extends Command
{
    protected $signature = 'security:scan-stored-files {--all : Scan clean files again}';

    protected $description = 'Scan existing managed files with the configured malware scanner';

    public function handle(MediaStorage $storage): int
    {
        if (! config('security.upload_scan.enabled')) {
            $this->error('SECURITY_UPLOAD_SCAN_ENABLED must be true before existing files can be scanned.');

            return self::FAILURE;
        }

        $query = StoredFile::query()->orderBy('id');
        if (! $this->option('all')) {
            $query->where('scan_status', '!=', 'clean');
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No files require scanning.');

            return self::SUCCESS;
        }

        $failed = 0;
        $bar = $this->output->createProgressBar($total);
        $query->chunkById(50, function ($files) use ($storage, &$failed, $bar): void {
            foreach ($files as $file) {
                try {
                    if (! $storage->exists($file)) {
                        $file->forceFill(['scan_status' => 'missing', 'scanned_at' => null])->save();
                        $failed++;
                    } else {
                        $storage->rescan($file);
                    }
                } catch (\Throwable $exception) {
                    report($exception);
                    $file->forceFill(['scan_status' => 'scan_failed', 'scanned_at' => null])->save();
                    $failed++;
                }

                $bar->advance();
            }
        });
        $bar->finish();
        $this->newLine(2);

        if ($failed > 0) {
            $this->error("{$failed} file(s) were not cleared. They remain blocked when clean-file serving is required.");

            return self::FAILURE;
        }

        $this->info("{$total} file(s) passed malware scanning.");

        return self::SUCCESS;
    }
}
