<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink(['email' => $data['email']]);

        AuditLog::record(null, 'auth.password_reset.requested', null, 'ขอลิงก์ตั้งรหัสผ่านใหม่', [
            'email_hash' => hash('sha256', mb_strtolower($data['email'])),
            'accepted' => $status === Password::RESET_LINK_SENT,
        ]);

        return back()->with(
            'success',
            'หากอีเมลนี้มีบัญชีอยู่ ระบบจะส่งลิงก์ตั้งรหัสผ่านใหม่ให้ กรุณาตรวจสอบกล่องจดหมาย',
        );
    }
}
