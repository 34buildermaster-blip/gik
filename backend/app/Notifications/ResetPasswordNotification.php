<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('ตั้งรหัสผ่านใหม่ | 34 Build Master')
            ->greeting('สวัสดี '.$notifiable->name)
            ->line('เราได้รับคำขอตั้งรหัสผ่านใหม่สำหรับบัญชีของคุณ')
            ->action('ตั้งรหัสผ่านใหม่', $url)
            ->line('ลิงก์นี้มีอายุ '.config('auth.passwords.users.expire').' นาที')
            ->line('หากคุณไม่ได้เป็นผู้ขอ สามารถละเว้นอีเมลฉบับนี้ได้');
    }
}
