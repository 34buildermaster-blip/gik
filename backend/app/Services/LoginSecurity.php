<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class LoginSecurity
{
    public function key(string $login): string
    {
        return 'login:'.hash('sha256', mb_strtolower(trim($login)));
    }

    public function isThrottled(string $login): bool
    {
        return RateLimiter::tooManyAttempts($this->key($login), 5);
    }

    public function availableIn(string $login): int
    {
        return RateLimiter::availableIn($this->key($login));
    }

    public function recordFailure(string $login, ?User $user): void
    {
        RateLimiter::hit($this->key($login), 60);

        if (! $user) {
            return;
        }

        $attempts = min(255, $user->failed_login_attempts + 1);
        $user->forceFill([
            'failed_login_attempts' => $attempts,
            'login_locked_until' => $attempts >= 5 ? now()->addMinute() : null,
        ])->save();
    }

    public function clear(User $user): void
    {
        foreach (array_filter([$user->username, $user->email]) as $identifier) {
            RateLimiter::clear($this->key($identifier));
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'login_locked_until' => null,
        ])->save();
    }
}
