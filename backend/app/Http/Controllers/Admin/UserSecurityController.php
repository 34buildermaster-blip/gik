<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\LoginSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserSecurityController extends Controller
{
    public function show(User $user): View
    {
        return view('admin.users.security', ['managedUser' => $user]);
    }

    public function resetPassword(Request $request, User $user, LoginSecurity $loginSecurity): RedirectResponse
    {
        $request->validateWithBag('security', [
            'current_password' => ['required', 'current_password'],
        ]);

        if ($request->user()->is($user)) {
            return back()->withErrors(['current_password' => 'กรุณาเปลี่ยนรหัสผ่านของบัญชีคุณจากหน้าโปรไฟล์'], 'security');
        }

        $temporaryPassword = Str::password(16, true, true, false, false);
        $user->forceFill([
            'password' => $temporaryPassword,
            'password_must_change' => true,
            'password_changed_at' => now(),
            'failed_login_attempts' => 0,
            'login_locked_until' => null,
            'remember_token' => Str::random(60),
        ])->save();
        $loginSecurity->clear($user);
        DB::table('sessions')->where('user_id', $user->id)->delete();
        AuditLog::record($request->user(), 'user.password.temporary_issued', $user, "ออกรหัสผ่านชั่วคราวให้ {$user->name}");

        return back()
            ->with('success', 'ออกรหัสผ่านชั่วคราวและยกเลิก Session เดิมเรียบร้อยแล้ว')
            ->with('temporary_password', $temporaryPassword);
    }

    public function unlock(Request $request, User $user, LoginSecurity $loginSecurity): RedirectResponse
    {
        $loginSecurity->clear($user);
        AuditLog::record($request->user(), 'user.login.unlocked', $user, "ปลดล็อกการเข้าสู่ระบบของ {$user->name}");

        return back()->with('success', 'ปลดล็อกบัญชีเรียบร้อยแล้ว');
    }
}
