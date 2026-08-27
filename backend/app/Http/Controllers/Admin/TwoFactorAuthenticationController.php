<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\TwoFactorAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends Controller
{
    public function start(Request $request, TwoFactorAuthentication $twoFactor): RedirectResponse
    {
        $request->validateWithBag('twoFactor', [
            'current_password' => ['required', 'current_password'],
        ]);

        $request->session()->put('two_factor_setup_secret', $twoFactor->generateSecret());

        return back()->with('success', 'สร้างกุญแจยืนยันตัวตนแล้ว กรุณากรอกรหัสจากแอปเพื่อเปิดใช้งาน');
    }

    public function confirm(Request $request, TwoFactorAuthentication $twoFactor): RedirectResponse
    {
        $data = $request->validateWithBag('twoFactor', [
            'code' => ['required', 'digits:6'],
        ]);
        $secret = $request->session()->get('two_factor_setup_secret');

        if (! is_string($secret) || ! $twoFactor->verify($secret, $data['code'])) {
            return back()->withErrors(['code' => 'รหัสยืนยันไม่ถูกต้อง กรุณาตรวจสอบเวลาในโทรศัพท์แล้วลองอีกครั้ง'], 'twoFactor');
        }

        $recoveryCodes = $twoFactor->recoveryCodes();
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $request->session()->forget('two_factor_setup_secret');
        AuditLog::record($user, 'auth.two_factor.enabled', $user, 'เปิดใช้การยืนยันตัวตนสองชั้น');

        return back()
            ->with('success', 'เปิดใช้การยืนยันตัวตนสองชั้นเรียบร้อยแล้ว')
            ->with('two_factor_recovery_codes', $recoveryCodes);
    }

    public function destroy(Request $request, TwoFactorAuthentication $twoFactor): RedirectResponse
    {
        if (config('security.staff_2fa_required', true) && $request->user()->isStaff()) {
            return back()->withErrors([
                'code' => 'บัญชีผู้ดูแลและผู้ตรวจหน้างานไม่สามารถปิดการยืนยันตัวตนสองชั้นได้',
            ], 'twoFactor');
        }

        $data = $request->validateWithBag('twoFactor', [
            'current_password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'max:32'],
        ]);
        $user = $request->user();

        if (! $twoFactor->verifyOrConsumeRecoveryCode($user, $data['code'])) {
            return back()->withErrors(['code' => 'รหัสยืนยันไม่ถูกต้อง'], 'twoFactor');
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        AuditLog::record($user, 'auth.two_factor.disabled', $user, 'ปิดการยืนยันตัวตนสองชั้น');

        return back()->with('success', 'ปิดการยืนยันตัวตนสองชั้นเรียบร้อยแล้ว');
    }
}
