<x-admin-layout title="โปรไฟล์ผู้ใช้งาน | 34 Build Master Admin">
    <div class="topbar profile-heading">
        <div>
            <p class="eyebrow">ACCOUNT SETTINGS</p>
            <h1>โปรไฟล์ผู้ใช้งาน</h1>
            <p class="muted">จัดการข้อมูลบัญชี รูปโปรไฟล์ และความปลอดภัยของคุณ</p>
        </div>
        <a class="button secondary" href="{{ $user->isAdmin() ? route('admin.dashboard') : url('/') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>
            {{ $user->isAdmin() ? 'กลับไปแดชบอร์ด' : 'กลับหน้าหลัก' }}
        </a>
    </div>

    <div class="profile-layout">
        <aside class="card profile-summary">
            <div class="profile-avatar-wrap">
                @if ($user->avatar_file_id || $user->avatar_path)
                    <img class="profile-avatar" src="{{ route('admin.profile.avatar') }}" alt="รูปโปรไฟล์ของ {{ $user->name }}">
                @else
                    <span class="profile-avatar profile-avatar-fallback">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                @endif
                <span class="profile-status-dot" title="กำลังใช้งาน"></span>
            </div>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <span class="profile-role">{{ $user->isAdmin() ? 'Administrator' : 'Member' }}</span>

            <dl class="profile-meta">
                <div><dt>ชื่อผู้ใช้</dt><dd>{{ $user->username ?: 'ยังไม่ได้ตั้งค่า' }}</dd></div>
                <div><dt>เริ่มใช้งานเมื่อ</dt><dd>{{ $user->created_at->locale('th')->translatedFormat('d M Y') }}</dd></div>
                <div><dt>สถานะบัญชี</dt><dd class="is-active">พร้อมใช้งาน</dd></div>
            </dl>
        </aside>

        <div class="profile-forms">
            <section class="card profile-form-card">
                <div class="profile-card-heading">
                    <span class="profile-heading-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                    <div><h2>ข้อมูลส่วนตัว</h2><p>ข้อมูลนี้ใช้แสดงในระบบจัดการเว็บไซต์</p></div>
                </div>

                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="profile-avatar-picker">
                        <label class="avatar-preview" for="avatar" data-avatar-preview>
                            @if ($user->avatar_file_id || $user->avatar_path)
                                <img src="{{ route('admin.profile.avatar') }}" alt="รูปโปรไฟล์ปัจจุบัน">
                            @else
                                <span>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            @endif
                        </label>
                        <div>
                            <label class="button secondary upload-button" for="avatar">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4"></path><path d="m7 9 5-5 5 5"></path><path d="M5 20h14"></path></svg>
                                เลือกรูปใหม่
                            </label>
                            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" hidden data-avatar-input>
                            <p>JPG, PNG หรือ WebP ไม่เกิน 2MB</p>
                            @error('avatar') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="profile-form-grid">
                        <div class="field">
                            <label for="name">ชื่อที่แสดง</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                            @error('name') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="field">
                            <label for="username">ชื่อผู้ใช้</label>
                            <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" autocomplete="username" placeholder="เช่น admin34build">
                            @error('username') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="field full">
                            <label for="email">อีเมล</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                            @error('email') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="field full">
                            <label for="line_recipient_id">LINE User ID สำหรับรับการแจ้งเตือน</label>
                            <input id="line_recipient_id" name="line_recipient_id" type="text" value="{{ old('line_recipient_id', $user->line_recipient_id) }}" placeholder="เช่น Uxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                            <small>เว้นว่างได้ ระบบจะส่ง LINE เมื่อเปิดใช้งาน Messaging API และบัญชีนี้มี User ID เท่านั้น</small>
                            @error('line_recipient_id') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="profile-form-actions"><button class="button" type="submit">บันทึกข้อมูล</button></div>
                </form>

                @if ($user->avatar_file_id || $user->avatar_path)
                    <form class="remove-avatar-form" method="POST" action="{{ route('admin.profile.avatar.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit">ลบรูปโปรไฟล์ปัจจุบัน</button>
                    </form>
                @endif
            </section>

            <section class="card profile-form-card">
                <div class="profile-card-heading">
                    <span class="profile-heading-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path><path d="M12 15v2"></path></svg></span>
                    <div><h2>เปลี่ยนรหัสผ่าน</h2><p>ใช้รหัสผ่านอย่างน้อย 8 ตัว พร้อมตัวอักษรและตัวเลข</p></div>
                </div>

                <form method="POST" action="{{ route('admin.profile.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="profile-form-grid">
                        <div class="field full">
                            <label for="current_password">รหัสผ่านปัจจุบัน</label>
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                            @error('current_password', 'password') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="field">
                            <label for="password">รหัสผ่านใหม่</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required>
                            @error('password', 'password') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="field">
                            <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="profile-form-actions"><button class="button secondary" type="submit">อัปเดตรหัสผ่าน</button></div>
                </form>
            </section>

            @include('admin.profile._two-factor')
        </div>
    </div>

    <script>
        (() => {
            const input = document.querySelector('[data-avatar-input]');
            const preview = document.querySelector('[data-avatar-preview]');
            input?.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file || !preview) return;
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = 'ตัวอย่างรูปโปรไฟล์ใหม่';
                image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
                preview.replaceChildren(image);
            });
        })();
    </script>
</x-admin-layout>
