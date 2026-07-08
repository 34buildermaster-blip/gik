@php($auth = true)
<x-admin-layout :auth="$auth" title="สมัครสมาชิก | 34 Build Master Admin">
    <div class="auth-page">
        <form class="auth-card card form" method="POST" action="{{ route('register.store') }}">
            @csrf
            <div>
                <p class="eyebrow">Create Admin</p>
                <h1>สมัครสมาชิก</h1>
                <p class="muted">สร้างผู้ดูแลระบบสำหรับจัดการบทความและ SEO</p>
            </div>
            <div class="field">
                <label for="name">ชื่อที่แสดง</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required>
            </div>
            <div class="field">
                <label for="email">อีเมล</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            </div>
            <div class="field">
                <label for="password">รหัสผ่าน</label>
                <input id="password" name="password" type="password" required>
            </div>
            <div class="field">
                <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
            </div>
            <div class="actions">
                <button class="button" type="submit">สร้างบัญชี</button>
                <a class="button secondary" href="{{ route('login') }}">มีบัญชีแล้ว</a>
            </div>
        </form>
    </div>
</x-admin-layout>
