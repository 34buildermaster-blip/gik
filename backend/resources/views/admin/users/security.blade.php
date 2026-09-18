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
        <section class="card panel user-security-full account-status-panel {{ $managedUser->isDisabled() ? 'is-suspended' : '' }}">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">ACCOUNT STATUS</p>
                    <h2>{{ $managedUser->isDisabled() ? 'บัญชีถูกระงับ' : 'บัญชีพร้อมใช้งาน' }}</h2>
                    <p>{{ $managedUser->isDisabled() ? 'Session และการเชื่อมต่อ LINE/Google ถูกยกเลิกแล้ว' : 'ผู้ใช้สามารถเข้าสู่ระบบและรับการแจ้งเตือนได้ตามปกติ' }}</p>
                </div>
                <span class="account-status-badge {{ $managedUser->isDisabled() ? 'is-suspended' : 'is-active' }}">
                    {{ $managedUser->isDisabled() ? 'ถูกระงับ' : 'พร้อมใช้งาน' }}
                </span>
            </div>

            @if($managedUser->isDisabled())
                <dl class="account-suspension-detail">
                    <div><dt>ระงับเมื่อ</dt><dd>{{ $managedUser->disabled_at->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>เหตุผล</dt><dd>{{ $managedUser->disabled_reason }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('admin.users.status', $managedUser) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="restore">
                    <button class="button" type="submit" @disabled(auth()->id() === $managedUser->id)>
                        <x-ui-icon name="unlock" /> เปิดใช้งานบัญชี
                    </button>
                </form>
            @else
                <form class="user-security-form" method="POST" action="{{ route('admin.users.status', $managedUser) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="suspend">
                    <div class="field">
                        <label for="suspension_reason">เหตุผลในการระงับ</label>
                        <textarea id="suspension_reason" name="reason" rows="3" maxlength="500" required @disabled(auth()->id() === $managedUser->id)>{{ old('reason') }}</textarea>
                        @error('reason', 'status') <small class="field-error">{{ $message }}</small> @enderror
                        @error('action', 'status') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
                    <button class="button danger" type="submit" @disabled(auth()->id() === $managedUser->id)>
                        <x-ui-icon name="lock" /> ระงับบัญชี
                    </button>
                </form>
            @endif
        </section>

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

        <section class="card panel user-security-full user-danger-zone">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">PERMANENT DELETE</p>
                    <h2>ลบบัญชีถาวร</h2>
                    <p>ดำเนินการได้เฉพาะบัญชีที่ถูกระงับและไม่มีข้อมูลการทำงานเกี่ยวข้อง</p>
                </div>
                <span class="danger-zone-icon" aria-hidden="true"><x-ui-icon name="trash-2" /></span>
            </div>

            @if($deletionBlockers !== [])
                <div class="deletion-blockers">
                    <strong>ยังลบถาวรไม่ได้</strong>
                    <ul>
                        @foreach($deletionBlockers as $blocker)<li>{{ $blocker }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form class="user-security-form deletion-form" method="POST" action="{{ route('admin.users.destroy', $managedUser) }}">
                @csrf
                @method('DELETE')
                <div class="user-delete-fields">
                    <div class="field">
                        <label for="delete_current_password">รหัสผ่าน Admin ของคุณ</label>
                        <input id="delete_current_password" name="current_password" type="password" autocomplete="current-password" required @disabled(auth()->id() === $managedUser->id || ! $managedUser->isDisabled() || $deletionBlockers !== [])>
                        @error('current_password', 'deletion') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
                    <div class="field">
                        <label for="delete_confirmation">พิมพ์ {{ $deletionConfirmation }} เพื่อยืนยัน</label>
                        <input id="delete_confirmation" name="confirmation" type="text" autocomplete="off" required @disabled(auth()->id() === $managedUser->id || ! $managedUser->isDisabled() || $deletionBlockers !== [])>
                        @error('confirmation', 'deletion') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
                </div>
                <button class="button danger" type="submit" @disabled(auth()->id() === $managedUser->id || ! $managedUser->isDisabled() || $deletionBlockers !== [])>
                    <x-ui-icon name="trash-2" /> ลบบัญชีถาวร
                </button>
            </form>
        </section>
    </div>
</x-admin-layout>
