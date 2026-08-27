@php($auth = true)
<x-admin-layout :auth="$auth" title="เปลี่ยนรหัสผ่านชั่วคราว | 34 Build Master">
    <div class="auth-page auth-challenge-page">
        <main class="auth-form-column">
            <form class="auth-card form" method="POST" action="{{ route('password.change-required.update') }}">
                @csrf
                @method('PUT')
                <div class="auth-form-heading">
                    <span>FIRST LOGIN SECURITY</span>
                    <h2>ตั้งรหัสผ่านส่วนตัว</h2>
                    <p>บัญชีนี้ใช้รหัสผ่านชั่วคราว กรุณาตั้งรหัสใหม่ก่อนเข้าใช้งานระบบ</p>
                </div>
                <div class="field">
                    <label for="current_password">รหัสผ่านชั่วคราว</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required autofocus>
                    @error('current_password') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <div class="field">
                    <label for="password">รหัสผ่านใหม่</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                    @error('password') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
                <button class="button auth-submit" type="submit">บันทึกและเข้าใช้งานระบบ</button>
            </form>
        </main>
    </div>
</x-admin-layout>
