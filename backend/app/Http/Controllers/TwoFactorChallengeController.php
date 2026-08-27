<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth.two_factor.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthentication $twoFactor): RedirectResponse
    {
        $pending = $request->session()->get('auth.two_factor');
        if (! is_array($pending) || ! isset($pending['user_id'])) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);
        $user = User::find($pending['user_id']);
        if (! $user || ! $user->hasTwoFactorAuthenticationEnabled()) {
            $request->session()->forget('auth.two_factor');

            return redirect()->route('login');
        }

        $key = 'two-factor:'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'code' => 'ลองรหัสหลายครั้งเกินไป กรุณารอ '.RateLimiter::availableIn($key).' วินาที',
            ]);
        }

        if (! $twoFactor->verifyOrConsumeRecoveryCode($user, $data['code'])) {
            RateLimiter::hit($key, 60);
            AuditLog::record($user, 'auth.two_factor.failed', $user, 'ยืนยันตัวตนสองชั้นไม่สำเร็จ');

            return back()->withErrors(['code' => 'รหัสยืนยันไม่ถูกต้องหรือหมดอายุแล้ว']);
        }

        RateLimiter::clear($key);
        $request->session()->forget('auth.two_factor');
        Auth::login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();
        AuditLog::record($user, 'auth.login.succeeded', $user, 'เข้าสู่ระบบด้วยการยืนยันตัวตนสองชั้น');

        return redirect()->route($user->isStaff() ? 'admin.dashboard' : 'client.projects.index');
    }
}
