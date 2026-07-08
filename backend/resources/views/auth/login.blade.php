@php($auth = true)
<x-admin-layout :auth="$auth" title="เข้าสู่ระบบ | 34 Build Master Admin">
    <div class="auth-page">
        <form class="auth-card card form" method="POST" action="{{ route('login.store') }}">
            @csrf
            <div>
                <p class="eyebrow">Admin Login</p>
                <h1>เข้าสู่ระบบ</h1>
                <p class="muted">จัดการบทความและข้อมูล SEO ของเว็บไซต์ 34 Build Master Construction</p>
            </div>
            <div class="field">
                <label for="email">อีเมล</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="field">
                <label for="password">รหัสผ่าน</label>
                <input id="password" name="password" type="password" required>
            </div>
            <label>
                <input name="remember" type="checkbox" value="1">
                จดจำการเข้าสู่ระบบ
            </label>
            <div class="actions">
                <button class="button" type="submit">เข้าสู่ระบบ</button>
                <a class="button secondary" href="{{ route('register') }}">สมัครสมาชิก</a>
            </div>
        </form>
    </div>
</x-admin-layout>
