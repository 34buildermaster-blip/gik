<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\LineNotificationChannel;
use App\Notifications\Channels\SafeMailChannel;

trait UsesConfiguredProjectChannels
{
    public function via(object $notifiable): array
    {
        $event = method_exists($this, 'notificationEvent')
            ? $this->notificationEvent()
            : null;

        if ($event && method_exists($notifiable, 'wantsNotificationEvent')
            && ! $notifiable->wantsNotificationEvent($event)) {
            return [];
        }

        $channels = [];

        if (! method_exists($notifiable, 'wantsNotificationChannel')
            || $notifiable->wantsNotificationChannel('database')) {
            $channels[] = 'database';
        }

        if (
            (! method_exists($notifiable, 'wantsNotificationChannel') || $notifiable->wantsNotificationChannel('email'))
            && config('project_notifications.email')
            && filled($notifiable->routeNotificationFor('mail'))
        ) {
            $channels[] = SafeMailChannel::class;
        }

        if (
            (! method_exists($notifiable, 'wantsNotificationChannel') || $notifiable->wantsNotificationChannel('line'))
            &&
            config('project_notifications.line')
            && filled(config('project_notifications.line_channel_access_token'))
            && filled($notifiable->routeNotificationForLine())
        ) {
            $channels[] = LineNotificationChannel::class;
        }

        return $channels;
    }
}
