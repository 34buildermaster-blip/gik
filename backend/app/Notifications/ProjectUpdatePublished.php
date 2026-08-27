<?php

namespace App\Notifications;

use App\Models\ProjectUpdate;
use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectUpdatePublished extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public ProjectUpdate $projectUpdate)
    {
        $this->projectUpdate->loadMissing('project:id,code,name,progress_percent');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('อัปเดตหน้างานใหม่: '.$this->projectUpdate->project->name)
            ->greeting('สวัสดี '.$notifiable->name)
            ->line('ทีมงานได้เผยแพร่อัปเดตหน้างานใหม่ที่ผ่านการตรวจสอบแล้ว')
            ->line($this->projectUpdate->title)
            ->action('ดูความคืบหน้าโครงการ', route('client.projects.show', $this->projectUpdate->project_id))
            ->line('ขอบคุณที่ไว้วางใจ 34 Build Master Construction');
    }

    public function toLine(object $notifiable): string
    {
        return "34 Build Master\nมีอัปเดตหน้างานใหม่: {$this->projectUpdate->project->name}\n{$this->projectUpdate->title}\nดูรายละเอียด: ".route('client.projects.show', $this->projectUpdate->project_id);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_update_approved',
            'project_id' => $this->projectUpdate->project_id,
            'project_update_id' => $this->projectUpdate->id,
            'project_code' => $this->projectUpdate->project->code,
            'project_name' => $this->projectUpdate->project->name,
            'title' => $this->projectUpdate->title,
            'progress_percent' => $this->projectUpdate->project->progress_percent,
            'message' => 'มีอัปเดตหน้างานใหม่ที่ผ่านการตรวจสอบแล้ว',
        ];
    }
}
