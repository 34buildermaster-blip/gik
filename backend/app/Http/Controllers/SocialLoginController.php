<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SocialIdentity;
use App\Models\User;
use App\Services\LoginSecurity;
use App\Services\SocialLogin;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class SocialLoginController extends Controller
{
    private const FLOW_TTL_SECONDS = 600;

    public function redirect(Request $request, string $provider, SocialLogin $socialLogin): RedirectResponse
    {
        abort_unless($socialLogin->isSupported($provider), 404);

        if (! $socialLogin->isConfigured($provider)) {
            return $this->loginError('ช่องทางเข้าสู่ระบบนี้ยังไม่พร้อมใช้งาน กรุณาใช้ชื่อผู้ใช้และรหัสผ่านก่อน');
        }

        $state = Str::random(64);
        $nonce = Str::random(64);
        $request->session()->forget('auth.social.pending');
        $request->session()->put("auth.social.flow.{$provider}", [
            'state' => $state,
            'nonce' => $nonce,
            'expires_at' => now()->addSeconds(self::FLOW_TTL_SECONDS)->getTimestamp(),
        ]);

        return redirect()->away($socialLogin->authorizationUrl($provider, $state, $nonce));
    }

    public function callback(Request $request, string $provider, SocialLogin $socialLogin, LoginSecurity $loginSecurity): RedirectResponse
    {
        abort_unless($socialLogin->isSupported($provider), 404);

        if ($request->filled('error')) {
            $request->session()->forget("auth.social.flow.{$provider}");

            return $this->loginError('ยกเลิกการเข้าสู่ระบบ หรือไม่ได้อนุญาตให้เข้าถึงข้อมูลที่จำเป็น');
        }

        $flow = $request->session()->pull("auth.social.flow.{$provider}");
        if (! $this->validFlow($flow, (string) $request->query('state'))) {
            AuditLog::record(null, 'auth.social.invalid_state', null, 'ปฏิเสธ Social Login ที่ตรวจสอบสถานะไม่ได้', [
                'provider' => $provider,
            ]);

            return $this->loginError('คำขอเข้าสู่ระบบหมดอายุหรือไม่ถูกต้อง กรุณาเริ่มใหม่อีกครั้ง');
        }

        $code = (string) $request->query('code');
        if ($code === '') {
            return $this->loginError('ผู้ให้บริการไม่ได้ส่งรหัสยืนยันกลับมา กรุณาลองใหม่อีกครั้ง');
        }

        try {
            $profile = $socialLogin->fetchIdentity($provider, $code, (string) $flow['nonce']);
        } catch (Throwable $exception) {
            Log::warning('Social login callback failed.', [
                'provider' => $provider,
                'exception' => $exception::class,
            ]);
            AuditLog::record(null, 'auth.social.failed', null, 'Social Login ไม่สำเร็จ', [
                'provider' => $provider,
            ]);

            return $this->loginError('ไม่สามารถยืนยันตัวตนกับผู้ให้บริการได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง');
        }

        $identity = SocialIdentity::query()
            ->with('user')
            ->where('provider', $provider)
            ->where('provider_user_id', $profile['provider_user_id'])
            ->first();

        if ($identity) {
            if (! $identity->user || $identity->user->role !== 'user') {
                return $this->loginError('บัญชีนี้ไม่สามารถเข้าสู่พื้นที่ลูกค้าผ่านช่องทางดังกล่าวได้');
            }

            if ($identity->user->isLoginLocked()) {
                return $this->loginError('บัญชีถูกระงับการเข้าสู่ระบบชั่วคราว กรุณารอสักครู่หรือติดต่อผู้ดูแล');
            }

            $identity->update([
                'provider_email' => $profile['email'],
                'display_name' => $profile['name'],
                'avatar_url' => $profile['avatar_url'],
            ]);

            return $this->finishLogin($request, $identity->user, $provider, $loginSecurity);
        }

        $request->session()->put('auth.social.pending', [
            ...$profile,
            'expires_at' => now()->addSeconds(self::FLOW_TTL_SECONDS)->getTimestamp(),
        ]);

        return redirect()->route('social.complete');
    }

    public function showComplete(Request $request): View|RedirectResponse
    {
        $pending = $this->pendingIdentity($request);
        if (! $pending) {
            return $this->loginError('ขั้นตอนเชื่อมบัญชีหมดอายุแล้ว กรุณาเริ่มเข้าสู่ระบบใหม่');
        }

        $existingCustomer = filled($pending['email'])
            ? $this->userByEmail((string) $pending['email'])
            : null;

        return view('auth.social-complete', [
            'pending' => $pending,
            'providerLabel' => config("social_login.{$pending['provider']}.label", ucfirst($pending['provider'])),
            'existingCustomer' => $existingCustomer?->role === 'user',
            'suggestedUsername' => $this->suggestedUsername($pending),
        ]);
    }

    public function complete(Request $request, LoginSecurity $loginSecurity): RedirectResponse
    {
        $pending = $this->pendingIdentity($request);
        if (! $pending) {
            return $this->loginError('ขั้นตอนเชื่อมบัญชีหมดอายุแล้ว กรุณาเริ่มเข้าสู่ระบบใหม่');
        }

        $base = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        $email = Str::lower(trim($base['email']));

        if (filled($pending['email']) && ! hash_equals(Str::lower((string) $pending['email']), $email)) {
            return back()->withErrors(['email' => 'อีเมลต้องตรงกับบัญชีที่ผู้ให้บริการยืนยันมา'])->withInput();
        }

        $existing = $this->userByEmail($email);
        $newUserData = null;
        if ($existing) {
            if ($existing->role !== 'user') {
                return back()->withErrors(['email' => 'อีเมลนี้ไม่สามารถผูกกับพื้นที่ลูกค้าได้'])->withInput();
            }

            if ($existing->isLoginLocked() || $loginSecurity->isThrottled($email)) {
                return back()->withErrors(['password' => 'บัญชีถูกระงับการเข้าสู่ระบบชั่วคราว กรุณารอสักครู่แล้วลองใหม่']);
            }

            if (! Hash::check($base['password'], $existing->password)) {
                $loginSecurity->recordFailure($email, $existing);
                AuditLog::record($existing, 'auth.social.link_failed', $existing, 'ยืนยันรหัสผ่านเพื่อเชื่อม Social Login ไม่สำเร็จ', [
                    'provider' => $pending['provider'],
                ]);

                return back()->withErrors(['password' => 'รหัสผ่านของบัญชีลูกค้าไม่ถูกต้อง'])->withInput();
            }

            if ($existing->socialIdentities()->where('provider', $pending['provider'])->exists()) {
                return back()->withErrors(['email' => 'บัญชีลูกค้านี้เชื่อมกับผู้ให้บริการดังกล่าวอยู่แล้ว'])->withInput();
            }

            $user = $existing;
        } else {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/', Rule::unique(User::class, 'username')],
                'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
                'password' => ['required', 'confirmed', Password::min(8)],
                'accept_policy' => ['accepted'],
                'marketing_consent' => ['sometimes', 'accepted'],
            ], [
                'accept_policy.accepted' => 'กรุณายอมรับข้อกำหนดการใช้งานและรับทราบนโยบายความเป็นส่วนตัวก่อนสร้างบัญชี',
            ]);

            $acceptedAt = now();
            $newUserData = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $email,
                'email_verified_at' => filled($pending['email']) ? $acceptedAt : null,
                'password' => $data['password'],
                'role' => 'user',
                'terms_accepted_at' => $acceptedAt,
                'privacy_accepted_at' => $acceptedAt,
                'marketing_consent_at' => $request->boolean('marketing_consent') ? $acceptedAt : null,
                'policy_version' => config('legal.policy_version'),
                'consent_ip_hash' => $request->ip()
                    ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                    : null,
            ];
            $user = null;
        }

        try {
            $user = DB::transaction(function () use ($newUserData, $pending, $user): User {
                $user ??= User::create($newUserData);
                SocialIdentity::create([
                    'user_id' => $user->id,
                    'provider' => $pending['provider'],
                    'provider_user_id' => $pending['provider_user_id'],
                    'provider_email' => $pending['email'],
                    'display_name' => $pending['name'],
                    'avatar_url' => $pending['avatar_url'],
                ]);

                return $user;
            });
        } catch (QueryException) {
            return $this->loginError('บัญชีผู้ให้บริการนี้ถูกเชื่อมไปแล้ว กรุณาเข้าสู่ระบบใหม่อีกครั้ง');
        }

        $request->session()->forget('auth.social.pending');
        if ($newUserData !== null) {
            AuditLog::record($user, 'auth.registration.completed', $user, 'สมัครบัญชีลูกค้าผ่าน Social Login และบันทึกการรับทราบนโยบาย', [
                'provider' => $pending['provider'],
                'policy_version' => config('legal.policy_version'),
                'marketing_consent' => $request->boolean('marketing_consent'),
            ]);
        }
        AuditLog::record($user, 'auth.social.linked', $user, 'เชื่อมบัญชี Social Login สำเร็จ', [
            'provider' => $pending['provider'],
        ]);

        return $this->finishLogin($request, $user, $pending['provider'], $loginSecurity);
    }

    private function finishLogin(Request $request, User $user, string $provider, LoginSecurity $loginSecurity): RedirectResponse
    {
        $loginSecurity->clear($user);

        if ($user->hasTwoFactorAuthenticationEnabled()) {
            $request->session()->put('auth.two_factor', [
                'user_id' => $user->id,
                'remember' => false,
                'portal' => 'customer',
                'source' => $provider,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user);
        $request->session()->regenerate();
        AuditLog::record($user, 'auth.login.succeeded', $user, 'เข้าสู่ระบบลูกค้าด้วย Social Login', [
            'provider' => $provider,
        ]);

        return redirect()->intended(route('client.projects.index'));
    }

    private function validFlow(mixed $flow, string $state): bool
    {
        return is_array($flow)
            && filled($flow['state'] ?? null)
            && filled($flow['nonce'] ?? null)
            && (int) ($flow['expires_at'] ?? 0) >= now()->getTimestamp()
            && hash_equals((string) $flow['state'], $state);
    }

    private function pendingIdentity(Request $request): ?array
    {
        $pending = $request->session()->get('auth.social.pending');

        if (! is_array($pending)
            || ! in_array($pending['provider'] ?? null, SocialLogin::PROVIDERS, true)
            || blank($pending['provider_user_id'] ?? null)
            || (int) ($pending['expires_at'] ?? 0) < now()->getTimestamp()) {
            $request->session()->forget('auth.social.pending');

            return null;
        }

        return $pending;
    }

    private function userByEmail(string $email): ?User
    {
        return User::query()->whereRaw('LOWER(email) = ?', [Str::lower(trim($email))])->first();
    }

    private function suggestedUsername(array $pending): string
    {
        $source = filled($pending['email'] ?? null)
            ? Str::before((string) $pending['email'], '@')
            : (string) ($pending['name'] ?? 'customer');
        $username = trim((string) preg_replace('/[^A-Za-z0-9_.-]+/', '_', Str::ascii($source)), '._-');

        return Str::limit($username !== '' ? $username : 'customer', 40, '').'_'.Str::lower(Str::random(5));
    }

    private function loginError(string $message): RedirectResponse
    {
        return redirect()
            ->route('login.customer')
            ->withErrors(['login' => $message])
            ->with('auth_error', $message);
    }
}
