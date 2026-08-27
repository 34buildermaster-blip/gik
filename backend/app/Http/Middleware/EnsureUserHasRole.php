<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless(
            $request->user() && in_array($request->user()->role, $roles, true),
            403,
            'บัญชีนี้ไม่มีสิทธิ์เข้าถึงส่วนจัดการเว็บไซต์',
        );

        return $next($request);
    }
}
