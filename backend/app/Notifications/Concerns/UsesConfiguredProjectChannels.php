<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\LineNotificationChannel;
use App\Notifications\Channels\SafeMailChannel;

trait UsesConfiguredProjectChannels
{
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (config('project_notifications.email') && filled($notifiable->routeNotificationFor('mail'))) {
            $channels[] = SafeMailChannel::class;
        }

        if (
            config('project_notifications.line')
            && filled(config('project_notifications.line_channel_access_token'))
            && filled($notifiable->routeNotificationForLine())
        ) {
            $channels[] = LineNotificationChannel::class;
        }

        return $channels;
    }
}
