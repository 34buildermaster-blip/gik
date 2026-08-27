<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthentication
{
    public function __construct(private readonly Google2FA $google2fa) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        return preg_match('/^\d{6}$/', $code) === 1
            && $this->google2fa->verifyKey($secret, $code, 1);
    }

    public function provisioningUri(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            (string) config('app.name', '34 Build Master'),
            $user->email,
            $secret,
        );
    }

    /** @return array<int, string> */
    public function recoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn (): string => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    /** @param array<int, string> $codes */
    public function hashRecoveryCodes(array $codes): array
    {
        return array_map(fn (string $code): string => Hash::make(Str::lower($code)), $codes);
    }

    public function verifyOrConsumeRecoveryCode(User $user, string $code): bool
    {
        if ($user->two_factor_secret && $this->verify($user->two_factor_secret, $code)) {
            return true;
        }

        $normalized = Str::lower(trim($code));
        foreach ($user->two_factor_recovery_codes ?? [] as $index => $hash) {
            if (! Hash::check($normalized, $hash)) {
                continue;
            }

            $codes = $user->two_factor_recovery_codes;
            unset($codes[$index]);
            $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

            return true;
        }

        return false;
    }
}
