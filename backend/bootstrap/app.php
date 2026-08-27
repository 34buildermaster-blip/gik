<?php

use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\EnsureStaffHasTwoFactorAuthentication;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [EnsurePasswordIsChanged::class]);
        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'staff.2fa' => EnsureStaffHasTwoFactorAuthentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $message = 'ไฟล์ที่เลือกมีขนาดรวมใหญ่เกินไป กรุณาอัปโหลดครั้งละไม่เกิน 100 MB';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()->withErrors(['upload' => $message]);
        });
    })->create();
