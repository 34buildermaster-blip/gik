@php($auth = true)
<x-admin-layout :auth="$auth" title="สมัครสมาชิก | 34 Build Master">
    <div class="auth-page auth-register-page">
        <form class="auth-card card form auth-register-card" method="POST" action="{{ route('register.store') }}">
            @csrf
            <a class="auth-brand" href="{{ route('login') }}">
                <span class="auth-brand-mark">
                    <img src="{{ asset('brand-logo.png') }}" alt="" aria-hidden="true">
                </span>
                <span><strong>34 Build Master</strong><span>Customer portal</span></span>
            </a>

            <div class="auth-register-heading">
                <p class="eyebrow">Create Account</p>
                <h1>สมัครสมาชิก</h1>
                <p class="muted">สร้างบัญชีลูกค้าเพื่อติดตามข้อมูลและความคืบหน้าของโครงการ</p>
            </div>

            <div class="auth-register-grid">
                <div class="field">
                    <label for="name">ชื่อที่แสดง</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
                    @error('name') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <div class="field">
                    <label for="username">ชื่อผู้ใช้</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required>
                    @error('username') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <div class="field auth-register-full">
                    <label for="email">อีเมล</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <div class="field">
                    <label for="password">รหัสผ่าน</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                    @error('password') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
            </div>

            <section class="auth-policy-panel" aria-labelledby="policy-heading">
                <div class="auth-policy-heading">
                    <span>Privacy & Terms</span>
                    <h2 id="policy-heading">ข้อตกลงก่อนสมัครสมาชิก</h2>
                </div>

                <label class="auth-policy-option auth-policy-option--required">
                    <input name="accept_policy" type="checkbox" value="1" @checked(old('accept_policy')) required>
                    <span>
                        ข้าพเจ้าได้อ่านและยอมรับ
                        <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener">ข้อกำหนดการใช้งาน</a>
                        และรับทราบ
                        <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener">นโยบายความเป็นส่วนตัว</a>
                        <em>จำเป็น</em>
                    </span>
                </label>
                @error('accept_policy') <small class="field-error auth-policy-error">{{ $message }}</small> @enderror

                <label class="auth-policy-option">
                    <input name="marketing_consent" type="checkbox" value="1" @checked(old('marketing_consent'))>
                    <span>
                        ยินยอมรับข่าวสาร โปรโมชัน และข้อมูลบริการจาก 34 Build Master
                        <small>ไม่บังคับ และไม่กระทบต่อการสมัครหรือการใช้บริการ</small>
                    </span>
                </label>
            </section>

            <div class="actions auth-register-actions">
                <button class="button" type="submit">ยืนยันและสร้างบัญชี</button>
                <a class="button secondary" href="{{ route('login') }}">มีบัญชีแล้ว</a>
            </div>
        </form>
    </div>
</x-admin-layout>
