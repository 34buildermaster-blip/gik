<?php

namespace App\Notifications;

use App\Models\ProjectUpdate;
use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectUpdateChangesRequested extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public ProjectUpdate $projectUpdate)
    {
        $this->projectUpdate->loadMissing('project:id,code,name');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('อัปเดตหน้างานถูกส่งกลับให้แก้ไข: '.$this->projectUpdate->project->name)
            ->greeting('สวัสดี '.$notifiable->name)
            ->line('Admin ส่งอัปเดตกลับเพื่อแก้ไขข้อมูล')
            ->line($this->projectUpdate->review_note ?: 'กรุณาตรวจสอบรายละเอียดในระบบ')
            ->action('เปิดรายการเพื่อแก้ไข', route('admin.projects.show', $this->projectUpdate->project_id));
    }

    public function toLine(object $notifiable): string
    {
        $note = $this->projectUpdate->review_note ?: 'กรุณาตรวจสอบรายละเอียดในระบบ';

        return "34 Build Master\nอัปเดตถูกส่งกลับให้แก้ไข: {$this->projectUpdate->project->name}\n{$note}\nเปิดรายการ: ".route('admin.projects.show', $this->projectUpdate->project_id);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_update_changes_requested',
            'project_id' => $this->projectUpdate->project_id,
            'project_update_id' => $this->projectUpdate->id,
            'project_code' => $this->projectUpdate->project->code,
            'project_name' => $this->projectUpdate->project->name,
            'title' => $this->projectUpdate->title,
            'progress_percent' => $this->projectUpdate->progress_percent,
            'message' => 'Admin ส่งอัปเดตกลับให้แก้ไข: '.$this->projectUpdate->review_note,
        ];
    }
}
