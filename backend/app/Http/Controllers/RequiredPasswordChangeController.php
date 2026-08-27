<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RequiredPasswordChangeController extends Controller
{
    public function edit(): View
    {
        return view('auth.change-required');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $user = $request->user();
        $user->forceFill([
            'password' => $data['password'],
            'password_must_change' => false,
            'password_changed_at' => now(),
            'remember_token' => Str::random(60),
        ])->save();
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        AuditLog::record($user, 'auth.password.temporary_changed', $user, 'เปลี่ยนรหัสผ่านชั่วคราวเรียบร้อยแล้ว');

        return redirect()->route($user->isStaff() ? 'admin.dashboard' : 'client.projects.index')
            ->with('success', 'ตั้งรหัสผ่านส่วนตัวเรียบร้อยแล้ว');
    }
}
