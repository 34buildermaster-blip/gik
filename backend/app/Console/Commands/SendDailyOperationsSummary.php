<?php

namespace App\Console\Commands;

use App\Models\ProjectEvent;
use App\Models\ProjectIssue;
use App\Models\ProjectStep;
use App\Models\ProjectUpdate;
use App\Models\User;
use App\Notifications\DailyOperationsSummaryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendDailyOperationsSummary extends Command
{
    protected $signature = 'operations:send-daily-summary';

    protected $description = 'Send the daily project operations summary to active administrators';

    public function handle(): int
    {
        $summary = [
            'today_events' => ProjectEvent::where('status', 'scheduled')->whereDate('starts_at', today())->count(),
            'pending_reviews' => ProjectUpdate::where('status', 'pending_review')->count(),
            'overdue_steps' => ProjectStep::where('progress_percent', '<', 100)->whereDate('planned_end_date', '<', today())->count(),
            'urgent_issues' => ProjectIssue::where('status', '<>', 'resolved')->where('priority', 'urgent')->count(),
        ];
        $admins = User::where('role', 'admin')->whereNull('disabled_at')->get();
        Notification::send($admins, new DailyOperationsSummaryNotification($summary));
        $this->info('Daily operations summary sent to '.$admins->count().' administrator(s).');

        return self::SUCCESS;
    }
}
