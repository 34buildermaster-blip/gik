<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\LoginSecurity;
use App\Services\UserAccountManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserSecurityController extends Controller
{
    public function show(User $user, UserAccountManager $accounts): View
    {
        return view('admin.users.security', [
            'managedUser' => $user,
            'deletionBlockers' => $accounts->deletionBlockers($user),
            'deletionConfirmation' => $user->username ?: $user->email,
        ]);
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

    public function updateStatus(Request $request, User $user, UserAccountManager $accounts): RedirectResponse
    {
        $data = $request->validateWithBag('status', [
            'action' => ['required', 'in:suspend,restore'],
            'reason' => ['nullable', 'required_if:action,suspend', 'string', 'max:500'],
        ]);

        if ($request->user()->is($user)) {
            return back()->withErrors(['action' => 'ไม่สามารถระงับบัญชีที่กำลังใช้งานอยู่ได้'], 'status');
        }

        if ($data['action'] === 'suspend') {
            if ($user->isAdmin() && ! $user->isDisabled() && $this->activeAdminCount() <= 1) {
                return back()->withErrors(['action' => 'ระบบต้องมี Admin ที่ใช้งานได้อย่างน้อย 1 บัญชี'], 'status');
            }

            if (! $user->isDisabled()) {
                $accounts->suspend($request->user(), $user, trim((string) $data['reason']));
            }

            return back()->with('success', 'ระงับบัญชีและยกเลิก Session กับการเชื่อมต่อภายนอกแล้ว');
        }

        if ($user->isDisabled()) {
            $accounts->restore($request->user(), $user);
        }

        return back()->with('success', 'เปิดใช้งานบัญชีเรียบร้อยแล้ว ผู้ใช้สามารถเข้าสู่ระบบด้วยรหัสผ่านเดิม');
    }

    public function destroy(Request $request, User $user, UserAccountManager $accounts): RedirectResponse
    {
        $data = $request->validateWithBag('deletion', [
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', 'string', 'max:255'],
        ]);

        if ($request->user()->is($user)) {
            return back()->withErrors(['confirmation' => 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้'], 'deletion');
        }

        if (! $user->isDisabled()) {
            return back()->withErrors(['confirmation' => 'กรุณาระงับบัญชีก่อนลบถาวร'], 'deletion');
        }

        if ($user->isAdmin()
            && User::query()
                ->where('role', 'admin')
                ->whereNull('disabled_at')
                ->whereKeyNot($user->getKey())
                ->doesntExist()) {
            return back()->withErrors(['confirmation' => 'ไม่สามารถลบ Admin คนสุดท้ายที่ยังใช้งานอยู่ได้'], 'deletion');
        }

        $expected = $user->username ?: $user->email;
        if (! hash_equals($expected, trim($data['confirmation']))) {
            return back()->withErrors(['confirmation' => "กรุณาพิมพ์ {$expected} ให้ตรงกัน"], 'deletion');
        }

        $blockers = $accounts->deletionBlockers($user);
        if ($blockers !== []) {
            return back()->withErrors([
                'confirmation' => 'ยังลบบัญชีถาวรไม่ได้ เนื่องจากมีข้อมูลการทำงานที่ต้องเก็บประวัติ',
            ], 'deletion');
        }

        $accounts->permanentlyDelete($request->user(), $user);

        return redirect()->route('admin.users.index')->with('success', 'ลบบัญชีถาวรเรียบร้อยแล้ว');
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->where('role', 'admin')
            ->whereNull('disabled_at')
            ->count();
    }
}
