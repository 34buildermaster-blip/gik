<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\LoginSecurity;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(Request $request, LoginSecurity $loginSecurity): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset($data, function (User $user, string $password) use ($loginSecurity): void {
            $user->forceFill([
                'password' => $password,
                'password_must_change' => false,
                'password_changed_at' => now(),
                'failed_login_attempts' => 0,
                'login_locked_until' => null,
                'remember_token' => Str::random(60),
            ])->save();
            $loginSecurity->clear($user);
            DB::table('sessions')->where('user_id', $user->id)->delete();
            AuditLog::record($user, 'auth.password_reset.completed', $user, 'ตั้งรหัสผ่านใหม่ผ่านลิงก์อีเมล');
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)])->onlyInput('email');
        }

        return redirect()->route('login.customer')->with('success', 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว สามารถเข้าสู่ระบบได้ทันที');
    }
}
