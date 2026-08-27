@php($auth = true)
<x-admin-layout :auth="$auth" title="ยืนยันตัวตน | 34 Build Master">
    <div class="auth-page auth-challenge-page">
        <main class="auth-form-column">
            <form class="auth-card form" method="POST" action="{{ route('two-factor.challenge.store') }}">
                @csrf
                <div class="auth-form-heading">
                    <span>SECURITY CHECK</span>
                    <h2>ยืนยันตัวตนอีกหนึ่งขั้น</h2>
                    <p>กรอกรหัส 6 หลักจากแอป Authenticator หรือใช้รหัสกู้คืนหนึ่งชุด</p>
                </div>

                @if($errors->has('code'))
                    <div class="auth-login-error" id="two-factor-error" role="alert" aria-live="assertive">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v6"></path><path d="M12 17h.01"></path></svg>
                        </span>
                        <div><strong>ยืนยันตัวตนไม่สำเร็จ</strong><p>{{ $errors->first('code') }}</p></div>
                    </div>
                @endif

                <div class="field">
                    <label for="code">รหัสยืนยันหรือรหัสกู้คืน</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="32" required autofocus @class(['is-invalid' => $errors->has('code')]) @if($errors->has('code')) aria-invalid="true" aria-describedby="two-factor-error" @endif>
                </div>
                <button class="button auth-submit" type="submit">ยืนยันและเข้าสู่ระบบ</button>
                <a class="auth-challenge-cancel" href="{{ route('login') }}">ยกเลิกและกลับหน้าเข้าสู่ระบบ</a>
            </form>
        </main>
    </div>
</x-admin-layout>
