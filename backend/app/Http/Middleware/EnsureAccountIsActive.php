<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isDisabled()) {
            return $next($request);
        }

        $portal = $user->isAdmin() ? 'admin' : ($user->isInspector() ? 'inspector' : 'customer');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $message = 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ';

        return redirect()
            ->route('login.'.$portal)
            ->withErrors(['login' => $message])
            ->with('auth_error', $message);
    }
}
