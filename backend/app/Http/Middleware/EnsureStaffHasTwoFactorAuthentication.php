<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffHasTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (config('security.staff_2fa_required', true)
            && $user?->isStaff()
            && ! $user->hasTwoFactorAuthenticationEnabled()) {
            return redirect()
                ->route('admin.profile.edit')
                ->with('warning', 'บัญชีเจ้าหน้าที่ต้องเปิดการยืนยันตัวตนสองชั้นก่อนใช้งานส่วนจัดการ');
        }

        return $next($request);
    }
}
