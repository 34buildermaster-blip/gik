<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->password_must_change
            && ! $request->routeIs('password.change-required', 'password.change-required.update', 'logout')) {
            return redirect()->route('password.change-required');
        }

        return $next($request);
    }
}
