<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyOperationsSummaryNotification extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public array $summary) {}

    public function notificationEvent(): string
    {
        return 'daily_operations_summary';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('สรุปงานประจำวันที่ต้องติดตาม')
            ->greeting('สวัสดี '.$notifiable->name)
            ->line($this->message())
            ->action('เปิด Dashboard', route('admin.dashboard'));
    }

    public function toLine(object $notifiable): string
    {
        return "34 Build Master\nสรุปงานประจำวัน\n".$this->message()."\nเปิด Dashboard: ".route('admin.dashboard');
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'daily_operations_summary', 'title' => 'สรุปงานประจำวัน', 'message' => $this->message()];
    }

    private function message(): string
    {
        return "นัดวันนี้ {$this->summary['today_events']} · อัปเดตรอตรวจ {$this->summary['pending_reviews']} · ขั้นตอนเกินกำหนด {$this->summary['overdue_steps']} · ปัญหาเร่งด่วน {$this->summary['urgent_issues']}";
    }
}
