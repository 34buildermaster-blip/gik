<?php

namespace BuildMaster\Scripts;

use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    $lineApiStatus = Http::withToken((string) config('project_notifications.line_channel_access_token'))
        ->timeout(10)
        ->get('https://api.line.me/v2/bot/info')
        ->status();
} catch (Throwable) {
    $lineApiStatus = 'unreachable';
}

$result = [
    'checked_at' => now()->toIso8601String(),
    'line_enabled' => (bool) config('project_notifications.line'),
    'line_configured' => filled(config('project_notifications.line_channel_access_token'))
        && filled(config('project_notifications.line_channel_secret')),
    'line_api_status' => $lineApiStatus,
    'email_enabled' => (bool) config('project_notifications.email'),
    'mail_driver' => config('mail.default'),
    'mail_host_configured' => filled(config('mail.mailers.smtp.host'))
        && filled(config('mail.mailers.smtp.username')),
    'queue_driver' => config('queue.default'),
    'connected_line_users' => User::query()->whereNotNull('line_recipient_id')->count(),
    'connected_line_customers' => User::query()
        ->where('role', 'user')
        ->whereNotNull('line_recipient_id')
        ->count(),
    'pending_review_updates' => ProjectUpdate::query()->where('status', 'pending_review')->count(),
    'unread_notifications' => DB::table('notifications')->whereNull('read_at')->count(),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
