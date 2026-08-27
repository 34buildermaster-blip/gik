<?php

namespace App\Notifications;

use App\Models\ProjectUpdate;
use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectUpdateSubmitted extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public ProjectUpdate $projectUpdate)
    {
        $this->projectUpdate->loadMissing('project:id,code,name', 'creator:id,name');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('มีอัปเดตหน้างานรอตรวจสอบ: '.$this->projectUpdate->project->name)
            ->greeting('สวัสดี '.$notifiable->name)
            ->line(($this->projectUpdate->creator?->name ?? 'ทีมตรวจหน้างาน').' ส่งอัปเดตใหม่ให้ตรวจสอบ')
            ->line($this->projectUpdate->title)
            ->action('ตรวจสอบอัปเดต', route('admin.projects.show', $this->projectUpdate->project_id));
    }

    public function toLine(object $notifiable): string
    {
        return "34 Build Master\nมีอัปเดตหน้างานรอตรวจสอบ: {$this->projectUpdate->project->name}\n{$this->projectUpdate->title}\nตรวจสอบ: ".route('admin.projects.show', $this->projectUpdate->project_id);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_update_submitted',
            'project_id' => $this->projectUpdate->project_id,
            'project_update_id' => $this->projectUpdate->id,
            'project_code' => $this->projectUpdate->project->code,
            'project_name' => $this->projectUpdate->project->name,
            'title' => $this->projectUpdate->title,
            'progress_percent' => $this->projectUpdate->progress_percent,
            'message' => ($this->projectUpdate->creator?->name ?? 'ทีมตรวจหน้างาน').' ส่งอัปเดตให้ตรวจสอบ',
        ];
    }
}
