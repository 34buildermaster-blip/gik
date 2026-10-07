<?php

namespace App\Console\Commands;

use App\Models\ProjectEvent;
use App\Notifications\ProjectEventNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SendProjectEventReminders extends Command
{
    protected $signature = 'project-events:send-reminders';

    protected $description = 'Send reminders for project events starting within 24 hours';

    public function handle(): int
    {
        ProjectEvent::query()
            ->where('status', 'scheduled')
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [now(), now()->addDay()])
            ->with(['project.customers', 'project.manager', 'project.reviewer', 'assignee'])
            ->orderBy('id')
            ->chunkById(50, function ($events): void {
                foreach ($events as $event) {
                    DB::transaction(function () use ($event): void {
                        $lockedEvent = ProjectEvent::query()->lockForUpdate()->find($event->id);
                        if (! $lockedEvent || $lockedEvent->reminder_sent_at || $lockedEvent->status !== 'scheduled') {
                            return;
                        }

                        $recipients = collect([
                            $event->project->manager,
                            $event->project->reviewer,
                            $event->assignee,
                            ...($event->customer_visible ? $event->project->customers->all() : []),
                        ])->filter()->unique('id')->values();

                        Notification::send($recipients, new ProjectEventNotification($event, 'reminder'));
                        $lockedEvent->update(['reminder_sent_at' => now()]);
                    });
                }
            });

        return self::SUCCESS;
    }
}
