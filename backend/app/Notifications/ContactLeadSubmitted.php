<?php

namespace App\Notifications;

use App\Models\ContactLead;
use App\Notifications\Concerns\UsesConfiguredProjectChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactLeadSubmitted extends Notification
{
    use Queueable, UsesConfiguredProjectChannels;

    public function __construct(public ContactLead $contactLead) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('มีผู้ติดต่อใหม่จากเว็บไซต์: '.$this->contactLead->name)
            ->greeting('สวัสดี '.$notifiable->name)
            ->line('มีคำขอปรึกษาโครงการใหม่จากเว็บไซต์')
            ->line($this->contactLead->name.' · '.$this->contactLead->phone)
            ->line($this->contactLead->service_type ?: 'ยังไม่ระบุประเภทงาน')
            ->action('เปิดรายการผู้ติดต่อ', route('admin.contact-leads.index'));
    }

    public function toLine(object $notifiable): string
    {
        return "34 Build Master\nมีผู้ติดต่อใหม่จากเว็บไซต์\n{$this->contactLead->name} · {$this->contactLead->phone}\n".($this->contactLead->service_type ?: 'ยังไม่ระบุประเภทงาน')."\nเปิดรายการ: ".route('admin.contact-leads.index');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'contact_lead_submitted',
            'contact_lead_id' => $this->contactLead->id,
            'title' => 'ผู้ติดต่อใหม่: '.$this->contactLead->name,
            'message' => $this->contactLead->phone.' · '.($this->contactLead->service_type ?: 'ยังไม่ระบุประเภทงาน'),
        ];
    }
}
