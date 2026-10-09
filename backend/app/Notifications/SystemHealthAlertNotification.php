<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemHealthAlertNotification extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public string $headline, public array $problems) {}

    public function notificationEvent(): string
    {
        return 'system_health_alert';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->headline)->line('ระบบตรวจพบรายการที่ต้องดำเนินการ:');
        foreach ($this->problems as $problem) {
            $mail->line('• '.$problem);
        }

        return $mail->action('เปิด Dashboard', route('admin.dashboard'));
    }

    public function toLine(object $notifiable): string
    {
        return "34 Build Master\n{$this->headline}\n- ".implode("\n- ", $this->problems)."\nเปิด Dashboard: ".route('admin.dashboard');
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'system_health_alert', 'title' => $this->headline, 'message' => implode(' · ', $this->problems)];
    }
}
