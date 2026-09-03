@php($auth = true)
<x-admin-layout :auth="$auth" title="{{ $portalData['form_title'] }} | 34 Build Master">
    <div class="auth-page auth-portal auth-portal--{{ $portal }}">
        <section class="auth-portal-visual">
            <a class="auth-visual-brand" href="{{ config('app.frontend_url') }}">
                <span class="auth-visual-logo">
                    <img src="{{ asset('brand-logo.webp') }}" alt="" aria-hidden="true">
                </span>
                <strong>Build Master<small>Construction</small></strong>
            </a>

            <div class="auth-visual-copy">
                <p>{{ $portalData['eyebrow'] }}</p>
                <h1>{{ $portalData['title'] }}</h1>
                <span>{{ $portalData['description'] }}</span>
                <ul>
                    @foreach($portalData['features'] as $feature)
                        <li><i aria-hidden="true">&#10003;</i>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="auth-visual-status"><span></span><strong>34 Build Master</strong><small>ระบบบริหารและติดตามงานก่อสร้าง</small></div>
        </section>

        <main class="auth-form-column">
            <nav class="auth-portal-switch" aria-label="เลือกประเภทผู้ใช้งาน">
                <a class="{{ $portal === 'customer' ? 'is-active' : '' }}" href="{{ route('login.customer') }}">ลูกค้า</a>
                <a class="{{ $portal === 'inspector' ? 'is-active' : '' }}" href="{{ route('login.inspector') }}">ผู้ตรวจหน้างาน</a>
                <a class="{{ $portal === 'admin' ? 'is-active' : '' }}" href="{{ route('login.admin') }}">Admin</a>
            </nav>

            <form class="auth-card form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <input name="portal" type="hidden" value="{{ $portal }}">
                <div class="auth-form-heading">
                    <span>{{ $portalData['eyebrow'] }}</span>
                    <h2>{{ $portalData['form_title'] }}</h2>
                    <p>{{ $portalData['form_description'] }}</p>
                </div>

                @if(session('success'))
                    <div class="auth-success-message" role="status">{{ session('success') }}</div>
                @endif

                @php($authError = session('auth_error') ?: $errors->first())
                @if($authError)
                    <div class="auth-login-error" id="login-error" role="alert" aria-live="assertive">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v6"></path><path d="M12 17h.01"></path></svg>
                        </span>
                        <div>
                            <strong>เข้าสู่ระบบไม่สำเร็จ</strong>
                            <p>{{ $authError }}</p>
                        </div>
                    </div>
                @endif

                <div class="field">
                    <label for="login">ชื่อผู้ใช้หรืออีเมล</label>
                    <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" required autofocus @class(['is-invalid' => $errors->has('login')]) @if($errors->has('login')) aria-invalid="true" aria-describedby="login-error" @endif>
                </div>
                <div class="field">
                    <label for="password">รหัสผ่าน</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required @class(['is-invalid' => $errors->has('login') || $errors->has('password')]) @if($errors->has('login') || $errors->has('password')) aria-invalid="true" aria-describedby="login-error" @endif>
                </div>
                <label class="auth-remember">
                    <input name="remember" type="checkbox" value="1">
                    จดจำการเข้าสู่ระบบบนอุปกรณ์นี้
                </label>
                <a class="auth-forgot-link" href="{{ route('password.request') }}">ลืมรหัสผ่าน?</a>
                <button class="button auth-submit" type="submit">เข้าสู่ระบบ{{ $portalData['label'] }}</button>

                @if($portal === 'customer')
                    <p class="auth-register-note">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิกสำหรับลูกค้า</a></p>
                @else
                    <p class="auth-access-note">บัญชีสำหรับส่วนนี้ต้องได้รับสิทธิ์จาก Admin ก่อนเข้าใช้งาน</p>
                @endif
            </form>
        </main>
    </div>
</x-admin-layout>
