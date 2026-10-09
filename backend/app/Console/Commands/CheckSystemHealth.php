<?php

namespace App\Console\Commands;

use App\Models\StoredFile;
use App\Models\User;
use App\Notifications\SystemHealthAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CheckSystemHealth extends Command
{
    protected $signature = 'system:health-check {--notify : Notify administrators when a check fails}';

    protected $description = 'Check database, writable storage, and recent backup health';

    public function handle(): int
    {
        $problems = [];
        try {
            DB::select('SELECT 1');
        } catch (Throwable $exception) {
            $problems[] = 'ฐานข้อมูลไม่ตอบสนอง';
            report($exception);
        }

        try {
            $disk = Storage::disk((string) config('media.local.disk', 'local'));
            $probe = 'health/probe-'.now()->format('YmdHis').'.txt';
            if (! $disk->put($probe, 'ok') || ! $disk->exists($probe)) {
                $problems[] = 'พื้นที่จัดเก็บภายในเขียนหรืออ่านไม่ได้';
            }
            $disk->delete($probe);
        } catch (Throwable $exception) {
            $problems[] = 'พื้นที่จัดเก็บภายในไม่พร้อมใช้งาน';
            report($exception);
        }

        $lastBackup = StoredFile::where('category', 'system-backups')->latest()->first();
        if (! $lastBackup || $lastBackup->created_at->lt(now()->subHours((int) config('operations.backup_max_age_hours', 36)))) {
            $problems[] = 'ไม่พบข้อมูลสำรองที่สำเร็จภายใน 36 ชั่วโมง';
        }

        if ($problems === []) {
            Cache::put('system-health:last-ok-at', now()->toIso8601String(), now()->addDays(7));
            Cache::forget('system-health:last-alert-signature');
            $this->info('All system health checks passed.');

            return self::SUCCESS;
        }

        $signature = sha1(implode('|', $problems));
        if ($this->option('notify') && Cache::get('system-health:last-alert-signature') !== $signature) {
            $admins = User::where('role', 'admin')->whereNull('disabled_at')->get();
            Notification::send($admins, new SystemHealthAlertNotification('ระบบต้องการการตรวจสอบ', $problems));
            Cache::put('system-health:last-alert-signature', $signature, now()->addHours(12));
        }
        foreach ($problems as $problem) {
            $this->error($problem);
        }

        return self::FAILURE;
    }
}
