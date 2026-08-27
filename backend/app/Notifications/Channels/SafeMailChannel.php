<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class SafeMailChannel
{
    public function __construct(private readonly MailChannel $mailChannel) {}

    public function send(object $notifiable, Notification $notification): mixed
    {
        try {
            return $this->mailChannel->send($notifiable, $notification);
        } catch (Throwable $exception) {
            Log::warning('Project email notification could not be delivered.', [
                'notification' => $notification::class,
                'notifiable_id' => $notifiable->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
