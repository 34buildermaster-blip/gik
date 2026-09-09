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

            <section class="card profile-form-card line-account-card">
                <div class="profile-card-heading">
                    <span class="profile-heading-icon line-heading-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.5 10.4 10.4 0 0 1-3.8-.7L3 21l1.6-4.3A8.1 8.1 0 0 1 3 11.5 8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5Z"></path></svg>
                    </span>
                    <div><h2>แจ้งเตือนผ่าน LINE</h2><p>รับเหตุการณ์สำคัญตามหน้าที่และโครงการที่เกี่ยวข้อง</p></div>
                    <span class="line-account-status {{ $user->line_recipient_id ? 'is-connected' : '' }}">
                        {{ $user->line_recipient_id ? 'เชื่อมต่อแล้ว' : 'ยังไม่เชื่อมต่อ' }}
                    </span>
                </div>

                @if ($user->line_recipient_id)
                    <div class="line-account-content is-connected">
                        <span class="line-account-mark" aria-hidden="true">LINE</span>
                        <div>
                            <strong>บัญชีนี้พร้อมรับการแจ้งเตือน</strong>
                            <p>ระบบจะส่งข้อความตามประเภทเหตุการณ์และช่องทางที่คุณเลือกไว้ด้านล่าง</p>
                        </div>
                        <form method="POST" action="{{ route('line.account.disconnect') }}" onsubmit="return confirm('ยืนยันยกเลิกการเชื่อมต่อ LINE?')">
                            @csrf
                            @method('DELETE')
                            <button class="button secondary" type="submit">ยกเลิกการเชื่อมต่อ</button>
                        </form>
                    </div>
                @elseif ($lineAccountLinkConfigured)
                    <div class="line-account-content">
                        <span class="line-account-mark" aria-hidden="true">LINE</span>
                        <div>
                            <strong>เชื่อมต่อเพียงครั้งเดียว</strong>
                            <p>กดเปิด LINE เพิ่มเพื่อน Official Account แล้วส่งคำว่า “เชื่อมบัญชี” จากนั้นเปิดลิงก์ที่ได้รับและเข้าสู่ระบบบัญชีนี้</p>
                        </div>
                        <a class="button line-connect-button" href="{{ $lineAddFriendUrl }}" target="_blank" rel="noopener noreferrer">เปิด LINE เพื่อเชื่อมต่อ</a>
                    </div>
                @else
                    <div class="line-account-content is-disabled">
                        <span class="line-account-mark" aria-hidden="true">LINE</span>
                        <div>
                            <strong>รอเปิดใช้งาน LINE Official Account</strong>
                            <p>ผู้ดูแลระบบต้องเพิ่ม Channel Access Token, Channel Secret และลิงก์เพิ่มเพื่อนบนเซิร์ฟเวอร์ก่อน</p>
                        </div>
                        <button class="button secondary" type="button" disabled>ยังไม่พร้อมเชื่อมต่อ</button>
                    </div>
                @endif
            </section>

            <section class="card profile-form-card notification-preferences-card">
                <div class="profile-card-heading">
                    <span class="profile-heading-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg></span>
                    <div><h2>ตั้งค่าการแจ้งเตือน</h2><p>เลือกเหตุการณ์และช่องทางที่เหมาะกับหน้าที่ของคุณ</p></div>
                </div>

                <form method="POST" action="{{ route('admin.profile.notifications') }}">
                    @csrf
                    @method('PUT')
                    <div class="notification-preference-section">
                        <strong>ช่องทางรับแจ้งเตือน</strong>
                        <div class="notification-choice-grid">
                            @foreach ($notificationChannelLabels as $value => $label)
                                <label class="notification-choice">
                                    <input type="checkbox" name="channels[]" value="{{ $value }}" @checked(in_array($value, old('channels', $notificationSettings['channels']), true))>
                                    <span><b>{{ $label }}</b><small>{{ $value === 'database' ? 'แสดงในศูนย์แจ้งเตือน' : ($value === 'line' ? ($user->line_recipient_id ? 'เชื่อมต่อพร้อมใช้งาน' : 'ต้องเชื่อมบัญชี LINE ก่อน') : 'ต้องตั้งค่า SMTP บนเซิร์ฟเวอร์') }}</small></span>
                                </label>
                            @endforeach
                        </div>
                        @error('channels', 'notifications') <small class="field-error">{{ $message }}</small> @enderror
                    </div>

                    <div class="notification-preference-section">
                        <strong>ประเภทเหตุการณ์</strong>
                        <div class="notification-event-list">
                            @foreach ($user->availableNotificationEvents() as $event)
                                <label class="notification-choice is-compact">
                                    <input type="checkbox" name="events[]" value="{{ $event }}" @checked(in_array($event, old('events', $notificationSettings['events']), true))>
                                    <span><b>{{ $notificationEventLabels[$event] }}</b></span>
                                </label>
                            @endforeach
                        </div>
                        @error('events', 'notifications') <small class="field-error">{{ $message }}</small> @enderror
                    </div>

                    @if ($user->isAdmin())
                        <div class="notification-preference-section">
                            <label class="notification-choice notification-oversight-choice">
                                <input type="hidden" name="all_projects" value="0">
                                <input type="checkbox" name="all_projects" value="1" @checked(old('all_projects', $notificationSettings['all_projects']))>
                                <span><b>รับแจ้งเตือนจากทุกโครงการ</b><small>เหมาะสำหรับ Super Admin หรือผู้ดูแลภาพรวม แม้โครงการจะมี Admin ผู้ตรวจอนุมัติแล้ว</small></span>
                            </label>
                        </div>
                    @endif

                    <div class="profile-form-actions"><button class="button" type="submit">บันทึกการแจ้งเตือน</button></div>
                </form>
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
