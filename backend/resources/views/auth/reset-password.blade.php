@php($auth = true)
<x-admin-layout :auth="$auth" title="ตั้งรหัสผ่านใหม่ | 34 Build Master">
    <div class="auth-page auth-challenge-page">
        <main class="auth-form-column">
            <form class="auth-card form" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input name="token" type="hidden" value="{{ $token }}">
                <div class="auth-form-heading">
                    <span>NEW PASSWORD</span>
                    <h2>ตั้งรหัสผ่านใหม่</h2>
                    <p>ใช้รหัสผ่านอย่างน้อย 8 ตัว พร้อมตัวอักษรและตัวเลข</p>
                </div>
                <div class="field">
                    <label for="email">อีเมล</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required readonly>
                    @error('email') <small class="field-error">{{ $message }}</small> @enderror
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
                <button class="button auth-submit" type="submit">บันทึกรหัสผ่านใหม่</button>
            </form>
        </main>
    </div>
</x-admin-layout>
