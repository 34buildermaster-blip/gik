<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\LoginSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(string $portal): View
    {
        $portals = $this->loginPortals();
        abort_unless(array_key_exists($portal, $portals), 404);

        return view('auth.login', [
            'portal' => $portal,
            'portalData' => $portals[$portal],
            'portals' => $portals,
        ]);
    }

    public function login(Request $request, LoginSecurity $loginSecurity): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'portal' => ['required', Rule::in(array_keys($this->loginPortals()))],
        ]);

        $loginField = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginField => $data['login'],
            'password' => $data['password'],
        ];
        $loginUser = User::where($loginField, $data['login'])->first();

        if ($loginUser?->isLoginLocked() || $loginSecurity->isThrottled($data['login'])) {
            AuditLog::record(null, 'auth.login.throttled', null, 'ระงับการเข้าสู่ระบบชั่วคราว', [
                'login_hash' => hash('sha256', Str::lower($data['login'])),
                'portal' => $data['portal'],
            ]);
            $databaseLockSeconds = $loginUser?->login_locked_until
                ? max(0, $loginUser->login_locked_until->getTimestamp() - now()->getTimestamp())
                : 0;
            $seconds = max($loginSecurity->availableIn($data['login']), $databaseLockSeconds);
            $message = 'ลองเข้าสู่ระบบหลายครั้งเกินไป กรุณารอ '.max(1, (int) ceil($seconds)).' วินาที';

            return back()
                ->withErrors(['login' => $message])
                ->with('auth_error', $message)
                ->onlyInput('login');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $loginSecurity->recordFailure($data['login'], $loginUser);
            AuditLog::record(null, 'auth.login.failed', null, 'เข้าสู่ระบบไม่สำเร็จ', [
                'login_hash' => hash('sha256', Str::lower($data['login'])),
                'portal' => $data['portal'],
            ]);
            $message = 'ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบแล้วลองอีกครั้ง';

            return back()
                ->withErrors(['login' => $message])
                ->with('auth_error', $message)
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        $expectedRole = $this->loginPortals()[$data['portal']]['role'];
        if ($request->user()->role !== $expectedRole) {
            $actualRole = User::ROLE_LABELS[$request->user()->role] ?? 'บัญชีประเภทอื่น';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = "บัญชีนี้เป็น {$actualRole} กรุณาเลือกทางเข้าสู่ระบบให้ตรงกับประเภทบัญชี";

            return redirect()
                ->route('login.'.$data['portal'])
                ->withErrors(['login' => $message])
                ->with('auth_error', $message)
                ->onlyInput('login');
        }

        $user = $request->user();
        $loginSecurity->clear($user);
        if ($user->hasTwoFactorAuthenticationEnabled()) {
            $request->session()->put('auth.two_factor', [
                'user_id' => $user->id,
                'remember' => $request->boolean('remember'),
                'portal' => $data['portal'],
            ]);
            Auth::logout();

            return redirect()->route('two-factor.challenge');
        }

        AuditLog::record($user, 'auth.login.succeeded', $user, 'เข้าสู่ระบบสำเร็จ');

        return redirect()->route($user->isStaff() ? 'admin.dashboard' : 'client.projects.index');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'accept_policy' => ['accepted'],
            'marketing_consent' => ['sometimes', 'accepted'],
        ], [
            'accept_policy.accepted' => 'กรุณายอมรับข้อกำหนดการใช้งานและรับทราบนโยบายความเป็นส่วนตัวก่อนสมัครสมาชิก',
            'marketing_consent.accepted' => 'รูปแบบความยินยอมรับข่าวสารไม่ถูกต้อง',
        ]);

        $marketingConsent = $request->boolean('marketing_consent');
        unset($data['accept_policy'], $data['marketing_consent']);

        $acceptedAt = now();
        $data['terms_accepted_at'] = $acceptedAt;
        $data['privacy_accepted_at'] = $acceptedAt;
        $data['marketing_consent_at'] = $marketingConsent ? $acceptedAt : null;
        $data['policy_version'] = config('legal.policy_version');
        $data['consent_ip_hash'] = $request->ip()
            ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
            : null;

        $user = User::create($data);

        AuditLog::record($user, 'auth.registration.completed', $user, 'สมัครบัญชีลูกค้าและบันทึกการรับทราบนโยบาย', [
            'policy_version' => config('legal.policy_version'),
            'marketing_consent' => $marketingConsent,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.projects.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function loginPortals(): array
    {
        return [
            'customer' => [
                'role' => 'user',
                'label' => 'ลูกค้า',
                'eyebrow' => 'CLIENT PORTAL',
                'title' => 'ติดตามบ้านของคุณได้ทุกขั้นตอน',
                'description' => 'ดูความคืบหน้า รูปหน้างาน และรายละเอียดการตรวจรับจากทีมงานในพื้นที่ส่วนตัวของคุณ',
                'form_title' => 'เข้าสู่พื้นที่ลูกค้า',
                'form_description' => 'สำหรับเจ้าของโครงการที่ต้องการติดตามงาน',
                'features' => ['ความคืบหน้าโครงการล่าสุด', 'รูปภาพและ Timeline หน้างาน', 'แจ้งเตือนเมื่อมีข้อมูลใหม่'],
            ],
            'inspector' => [
                'role' => 'inspector',
                'label' => 'ผู้ตรวจหน้างาน',
                'eyebrow' => 'SITE INSPECTION',
                'title' => 'บันทึกจากหน้างานอย่างเป็นระบบ',
                'description' => 'อัปเดตรูป ผลตรวจ และเปอร์เซ็นต์ความคืบหน้าของโครงการที่ได้รับมอบหมายจากทุกอุปกรณ์',
                'form_title' => 'เข้าสู่พื้นที่ตรวจงาน',
                'form_description' => 'สำหรับทีมตรวจและอัปเดตข้อมูลภาคสนาม',
                'features' => ['เฉพาะโครงการที่ได้รับมอบหมาย', 'บันทึกผลผ่าน ไม่ผ่าน และแก้ไข', 'อัปโหลดรูปหน้างานได้ทันที'],
            ],
            'admin' => [
                'role' => 'admin',
                'label' => 'Admin',
                'eyebrow' => 'ADMIN CONTROL',
                'title' => 'ควบคุมทุกส่วนจากพื้นที่เดียว',
                'description' => 'บริหารผู้ใช้งาน โครงการ เนื้อหาเว็บไซต์ และสิทธิ์ของทีมงานด้วยข้อมูลที่ตรวจสอบย้อนหลังได้',
                'form_title' => 'เข้าสู่ระบบผู้ดูแล',
                'form_description' => 'สำหรับผู้ดูแลระบบที่ได้รับสิทธิ์เท่านั้น',
                'features' => ['จัดการบทบาทและผู้ใช้งาน', 'บริหารโครงการและข้อมูลเว็บไซต์', 'ควบคุมสิทธิ์การเข้าถึงทั้งหมด'],
            ],
        ];
    }
}
