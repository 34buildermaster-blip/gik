<?php

namespace App\Notifications;

use App\Models\ProjectInquiry;
use App\Models\ProjectInquiryMessage;
use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ProjectInquiryMessageCreated extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public ProjectInquiry $inquiry, public ProjectInquiryMessage $message)
    {
        $this->inquiry->loadMissing('project:id,code,name', 'customer:id,name');
        $this->message->loadMissing('sender:id,name,role');
    }

    public function notificationEvent(): string
    {
        return $this->message->sender->isStaff() ? 'staff_message_received' : 'customer_message_received';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ข้อความใหม่: '.$this->inquiry->project->name)
            ->greeting('สวัสดี '.$notifiable->name)
            ->line($this->message->sender->name.' ส่งข้อความในหัวข้อ “'.$this->inquiry->subject.'”')
            ->line(Str::limit($this->message->body, 180))
            ->action('เปิดดูและตอบกลับ', $this->urlFor($notifiable));
    }

    public function toLine(object $notifiable): string
    {
        return "34 Build Master\nข้อความใหม่: {$this->inquiry->project->name}\n{$this->inquiry->subject}\nจาก {$this->message->sender->name}\nเปิดดู: ".$this->urlFor($notifiable);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->notificationEvent(),
            'project_id' => $this->inquiry->project_id,
            'project_code' => $this->inquiry->project->code,
            'project_name' => $this->inquiry->project->name,
            'inquiry_id' => $this->inquiry->id,
            'title' => $this->inquiry->subject,
            'message' => $this->message->sender->name.': '.Str::limit($this->message->body, 120),
        ];
    }

    private function urlFor(object $notifiable): string
    {
        $route = method_exists($notifiable, 'isStaff') && $notifiable->isStaff()
            ? 'admin.projects.show'
            : 'client.projects.show';

        return route($route, $this->inquiry->project_id).'#project-inquiries';
    }
}
