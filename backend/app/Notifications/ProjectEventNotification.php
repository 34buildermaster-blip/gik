<?php

namespace App\Notifications;

use App\Models\ProjectEvent;
use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectEventNotification extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public ProjectEvent $event, public string $mode = 'scheduled')
    {
        $this->event->loadMissing('project:id,code,name');
    }

    public function notificationEvent(): string
    {
        return 'project_event_reminder';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->headline().': '.$this->event->project->name)
            ->greeting('สวัสดี '.$notifiable->name)
            ->line($this->event->title)
            ->line('วันที่ '.$this->event->starts_at->locale('th')->translatedFormat('d M Y เวลา H:i น.'))
            ->when(filled($this->event->location), fn (MailMessage $mail) => $mail->line('สถานที่ '.$this->event->location))
            ->action('เปิดดูปฏิทินโครงการ', $this->urlFor($notifiable));
    }

    public function toLine(object $notifiable): string
    {
        $location = filled($this->event->location) ? "\nสถานที่ {$this->event->location}" : '';

        return "34 Build Master\n{$this->headline()}: {$this->event->project->name}\n{$this->event->title}\n"
            .$this->event->starts_at->locale('th')->translatedFormat('d M Y เวลา H:i น.')
            .$location."\nดูรายละเอียด: ".$this->urlFor($notifiable);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_event_reminder',
            'project_id' => $this->event->project_id,
            'project_event_id' => $this->event->id,
            'project_code' => $this->event->project->code,
            'project_name' => $this->event->project->name,
            'title' => $this->event->title,
            'message' => $this->headline().' · '.$this->event->starts_at->format('d/m/Y H:i'),
        ];
    }

    private function headline(): string
    {
        if ($this->event->status === 'cancelled') {
            return 'ยกเลิกนัดหมายในโครงการ';
        }

        return match ($this->mode) {
            'updated' => 'กำหนดการมีการเปลี่ยนแปลง',
            'reminder' => 'แจ้งเตือนนัดหมายภายใน 24 ชั่วโมง',
            default => 'มีนัดหมายใหม่ในโครงการ',
        };
    }

    private function urlFor(object $notifiable): string
    {
        if (method_exists($notifiable, 'isStaff') && $notifiable->isStaff()) {
            return route('admin.calendar.index', ['event' => $this->event->id]);
        }

        return route('client.projects.show', $this->event->project_id).'#project-calendar';
    }
}
