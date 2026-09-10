@php($auth = true)
<x-admin-layout :auth="$auth" title="เชื่อมบัญชี {{ $providerLabel }} | 34 Build Master">
    <div class="auth-page auth-register-page">
        <form class="auth-card card form auth-register-card auth-social-complete" method="POST" action="{{ route('social.complete.store') }}">
            @csrf
            <a class="auth-brand" href="{{ route('login.customer') }}">
                <span class="auth-brand-mark"><img src="{{ asset('brand-logo.webp') }}" alt="" aria-hidden="true"></span>
                <span><strong>34 Build Master</strong><span>Customer portal</span></span>
            </a>

            <div class="auth-register-heading">
                <p class="eyebrow">{{ strtoupper($providerLabel) }} LOGIN</p>
                <h1>{{ $existingCustomer ? 'ยืนยันเพื่อเชื่อมบัญชี' : 'ตั้งค่าบัญชีลูกค้า' }}</h1>
                <p class="muted">
                    {{ $existingCustomer
                        ? 'อีเมลนี้มีบัญชีลูกค้าอยู่แล้ว กรุณายืนยันรหัสผ่านเดิมเพียงครั้งเดียว'
                        : 'กรอกข้อมูลครั้งแรกให้ครบ หลังจากนี้คุณจะกดเข้าสู่ระบบผ่าน '.$providerLabel.' ได้ทันที' }}
                </p>
            </div>

            @if($errors->any())
                <div class="auth-login-error" role="alert" aria-live="assertive">
                    <span aria-hidden="true">!</span>
                    <div><strong>ตรวจสอบข้อมูลอีกครั้ง</strong><p>{{ $errors->first() }}</p></div>
                </div>
            @endif

            <div class="auth-register-grid">
                @unless($existingCustomer)
                    <div class="field">
                        <label for="name">ชื่อที่แสดง</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $pending['name']) }}" autocomplete="name" required autofocus>
                        @error('name') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
                    <div class="field">
                        <label for="username">ชื่อผู้ใช้สำรอง</label>
                        <input id="username" name="username" type="text" value="{{ old('username', $suggestedUsername) }}" autocomplete="username" required>
                        @error('username') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
                @endunless

                <div class="field auth-register-full">
                    <label for="email">อีเมลบัญชีลูกค้า</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $pending['email']) }}" autocomplete="email" required @readonly(filled($pending['email']))>
                    @if(filled($pending['email']))<small>ยืนยันโดย {{ $providerLabel }}</small>@endif
                    @error('email') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="field {{ $existingCustomer ? 'auth-register-full' : '' }}">
                    <label for="password">{{ $existingCustomer ? 'รหัสผ่านบัญชีลูกค้าเดิม' : 'ตั้งรหัสผ่านสำรอง' }}</label>
                    <input id="password" name="password" type="password" autocomplete="{{ $existingCustomer ? 'current-password' : 'new-password' }}" required>
                    @error('password') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                @unless($existingCustomer)
                    <div class="field">
                        <label for="password_confirmation">ยืนยันรหัสผ่านสำรอง</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    </div>
                @endunless
            </div>

            @unless($existingCustomer)
                <section class="auth-policy-panel" aria-labelledby="social-policy-heading">
                    <div class="auth-policy-heading">
                        <span>Privacy & Terms</span>
                        <h2 id="social-policy-heading">ข้อตกลงก่อนสร้างบัญชี</h2>
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
                    <label class="auth-policy-option">
                        <input name="marketing_consent" type="checkbox" value="1" @checked(old('marketing_consent'))>
                        <span>ยินยอมรับข่าวสารและข้อมูลบริการจาก 34 Build Master<small>ไม่บังคับ และถอนได้ภายหลัง</small></span>
                    </label>
                </section>
            @endunless

            <div class="actions auth-register-actions">
                <button class="button" type="submit">{{ $existingCustomer ? 'ยืนยันและเข้าสู่ระบบ' : 'สร้างบัญชีและเข้าสู่ระบบ' }}</button>
                <a class="button secondary" href="{{ route('login.customer') }}">ยกเลิก</a>
            </div>
            <p class="auth-social-privacy">ระบบเก็บเฉพาะรหัสอ้างอิงบัญชี ชื่อ อีเมล และรูปโปรไฟล์ที่จำเป็น โดยไม่จัดเก็บ access token ของ {{ $providerLabel }}</p>
        </form>
    </div>
</x-admin-layout>
