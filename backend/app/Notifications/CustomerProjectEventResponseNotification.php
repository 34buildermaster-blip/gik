<?php

namespace App\Notifications;

use App\Models\ProjectEvent;
use App\Models\User;
use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerProjectEventResponseNotification extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public ProjectEvent $event, public User $customer)
    {
        $this->event->loadMissing('project:id,code,name');
    }

    public function notificationEvent(): string
    {
        return 'project_event_response';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ลูกค้าตอบรับนัดหมาย: '.$this->event->project->name)
            ->line($this->message())
            ->action('เปิดปฏิทินโครงการ', route('admin.calendar.index', ['event' => $this->event->id]));
    }

    public function toLine(object $notifiable): string
    {
        return "34 Build Master\nลูกค้าตอบรับนัดหมาย\n".$this->message()."\nดูรายละเอียด: ".route('admin.calendar.index', ['event' => $this->event->id]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_event_response',
            'project_id' => $this->event->project_id,
            'project_event_id' => $this->event->id,
            'title' => 'ลูกค้าตอบรับนัดหมาย',
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        $response = ProjectEvent::CUSTOMER_RESPONSE_LABELS[$this->event->customer_response] ?? 'ตอบกลับนัดหมาย';
        $proposed = $this->event->proposed_starts_at
            ? ' วันที่เสนอ '.$this->event->proposed_starts_at->format('d/m/Y H:i')
            : '';

        return "{$this->customer->name} {$response}: {$this->event->title}{$proposed}";
    }
}
