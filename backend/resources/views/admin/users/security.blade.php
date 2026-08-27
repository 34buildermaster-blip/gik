<x-admin-layout title="ความปลอดภัยผู้ใช้งาน | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">ACCOUNT SECURITY</p>
            <h1>ความปลอดภัยของ {{ $managedUser->name }}</h1>
            <p class="muted" style="margin:7px 0 0;">จัดการการล็อกและออกสิทธิ์เข้าระบบใหม่โดยไม่สามารถดูรหัสผ่านเดิมได้</p>
        </div>
        <a class="button secondary" href="{{ route('admin.users.index') }}">กลับไปจัดการผู้ใช้งาน</a>
    </div>

    @if(session('temporary_password'))
        <section class="card user-temporary-password" role="status">
            <div><span>รหัสผ่านชั่วคราว</span><strong>{{ session('temporary_password') }}</strong></div>
            <p>ส่งให้ผู้ใช้งานผ่านช่องทางที่เชื่อถือได้ รหัสนี้จะแสดงเพียงครั้งเดียว และระบบจะบังคับให้เปลี่ยนหลัง Login</p>
        </section>
    @endif

    <div class="user-security-grid">
        <section class="card panel">
            <div class="panel-heading"><div><p class="eyebrow">LOGIN STATUS</p><h2>สถานะการเข้าสู่ระบบ</h2></div></div>
            <dl class="user-security-meta">
                <div><dt>ชื่อผู้ใช้</dt><dd>{{ $managedUser->username ?: '-' }}</dd></div>
                <div><dt>อีเมล</dt><dd>{{ $managedUser->email }}</dd></div>
                <div><dt>จำนวนครั้งที่ผิด</dt><dd>{{ $managedUser->failed_login_attempts }}</dd></div>
                <div><dt>สถานะล็อก</dt><dd class="{{ $managedUser->isLoginLocked() ? 'is-danger' : 'is-safe' }}">{{ $managedUser->isLoginLocked() ? 'ล็อกถึง '.$managedUser->login_locked_until->format('H:i:s') : 'พร้อมใช้งาน' }}</dd></div>
                <div><dt>ต้องเปลี่ยนรหัสผ่าน</dt><dd>{{ $managedUser->password_must_change ? 'ใช่' : 'ไม่' }}</dd></div>
                <div><dt>2FA</dt><dd>{{ $managedUser->hasTwoFactorAuthenticationEnabled() ? 'เปิดใช้งาน' : 'ยังไม่เปิด' }}</dd></div>
            </dl>
            <form method="POST" action="{{ route('admin.users.security.unlock', $managedUser) }}">
                @csrf
                @method('PUT')
                <button class="button secondary" type="submit" @disabled(! $managedUser->isLoginLocked() && $managedUser->failed_login_attempts === 0)>ปลดล็อกบัญชี</button>
            </form>
        </section>

        <section class="card panel">
            <div class="panel-heading"><div><p class="eyebrow">TEMPORARY PASSWORD</p><h2>ออกรหัสผ่านชั่วคราว</h2><p>Session เดิมทั้งหมดจะถูกยกเลิกทันที</p></div></div>
            <form class="user-security-form" method="POST" action="{{ route('admin.users.security.password', $managedUser) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="current_password">ยืนยันรหัสผ่าน Admin ของคุณ</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required @disabled(auth()->id() === $managedUser->id)>
                    @error('current_password', 'security') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <button class="button" type="submit" @disabled(auth()->id() === $managedUser->id)>สร้างรหัสผ่านชั่วคราว</button>
                @if(auth()->id() === $managedUser->id)<small>บัญชีของคุณต้องเปลี่ยนรหัสผ่านจากหน้าโปรไฟล์</small>@endif
            </form>
        </section>
    </div>
</x-admin-layout>
