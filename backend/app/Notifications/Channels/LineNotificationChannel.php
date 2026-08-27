<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LineNotificationChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $recipient = $notifiable->routeNotificationForLine();
        $token = (string) config('project_notifications.line_channel_access_token');

        if (! $recipient || $token === '' || ! method_exists($notification, 'toLine')) {
            return;
        }

        try {
            Http::withToken($token)
                ->acceptJson()
                ->timeout(10)
                ->post((string) config('project_notifications.line_push_url'), [
                    'to' => $recipient,
                    'messages' => [[
                        'type' => 'text',
                        'text' => $notification->toLine($notifiable),
                    ]],
                ])
                ->throw();
        } catch (Throwable $exception) {
            Log::warning('Project LINE notification could not be delivered.', [
                'notification' => $notification::class,
                'notifiable_id' => $notifiable->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
