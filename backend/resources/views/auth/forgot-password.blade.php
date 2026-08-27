@php($auth = true)
<x-admin-layout :auth="$auth" title="ลืมรหัสผ่าน | 34 Build Master">
    <div class="auth-page auth-challenge-page">
        <main class="auth-form-column">
            <form class="auth-card form" method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="auth-form-heading">
                    <span>ACCOUNT RECOVERY</span>
                    <h2>ลืมรหัสผ่าน</h2>
                    <p>กรอกอีเมลที่ใช้สมัคร ระบบจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ให้</p>
                </div>
                @if(session('success'))
                    <div class="auth-success-message" role="status">{{ session('success') }}</div>
                @endif
                <div class="field">
                    <label for="email">อีเมล</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <button class="button auth-submit" type="submit">ส่งลิงก์ตั้งรหัสผ่านใหม่</button>
                <a class="auth-challenge-cancel" href="{{ route('login.customer') }}">กลับหน้าเข้าสู่ระบบ</a>
            </form>
        </main>
    </div>
</x-admin-layout>
