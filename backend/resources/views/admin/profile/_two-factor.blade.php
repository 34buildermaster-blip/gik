<section class="card profile-form-card two-factor-card">
        <div class="profile-card-heading">
            <span class="profile-heading-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6l-8-3Z"></path><path d="m9 12 2 2 4-4"></path></svg></span>
            <div>
                <h2>การยืนยันตัวตนสองชั้น</h2>
                <p>เพิ่มรหัสจาก Authenticator หลังกรอกรหัสผ่าน เพื่อป้องกันบัญชีของคุณ</p>
            </div>
            <span class="two-factor-status {{ $user->hasTwoFactorAuthenticationEnabled() ? 'is-enabled' : '' }}">
                {{ $user->hasTwoFactorAuthenticationEnabled() ? 'เปิดใช้งานแล้ว' : 'ยังไม่เปิดใช้งาน' }}
            </span>
        </div>

        @if(session('two_factor_recovery_codes'))
            <div class="two-factor-recovery" role="status">
                <strong>บันทึกรหัสกู้คืนเหล่านี้ไว้ในที่ปลอดภัย</strong>
                <p>แต่ละรหัสใช้ได้หนึ่งครั้ง และจะไม่แสดงอีกหลังออกจากหน้านี้</p>
                <div>
                    @foreach(session('two_factor_recovery_codes') as $recoveryCode)
                        <code>{{ $recoveryCode }}</code>
                    @endforeach
                </div>
            </div>
        @endif

        @if($user->hasTwoFactorAuthenticationEnabled())
            <form class="two-factor-form" method="POST" action="{{ route('admin.profile.two-factor.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="field">
                    <label for="two_factor_disable_password">รหัสผ่านปัจจุบัน</label>
                    <input id="two_factor_disable_password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <div class="field">
                    <label for="two_factor_disable_code">รหัส Authenticator หรือรหัสกู้คืน</label>
                    <input id="two_factor_disable_code" name="code" type="text" autocomplete="one-time-code" required>
                </div>
                <button class="button secondary" type="submit">ปิดการยืนยันตัวตนสองชั้น</button>
            </form>
        @elseif($twoFactorSetupSecret)
            <div class="two-factor-setup">
                <ol>
                    <li>เปิดแอป Google Authenticator, Microsoft Authenticator หรือ 1Password</li>
                    <li>เลือกเพิ่มบัญชีด้วย Setup key แล้วกรอกกุญแจด้านล่าง</li>
                    <li>กรอกรหัส 6 หลักเพื่อยืนยันการเปิดใช้งาน</li>
                </ol>
                <div class="two-factor-secret"><span>Setup key</span><code>{{ $twoFactorSetupSecret }}</code></div>
                <details><summary>แสดง Provisioning URI สำหรับแอปที่รองรับ</summary><code>{{ $twoFactorProvisioningUri }}</code></details>
                <form class="two-factor-confirm" method="POST" action="{{ route('admin.profile.two-factor.confirm') }}">
                    @csrf
                    <div class="field">
                        <label for="two_factor_code">รหัส 6 หลัก</label>
                        <input id="two_factor_code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                        @error('code', 'twoFactor') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
                    <button class="button" type="submit">ยืนยันและเปิดใช้งาน</button>
                </form>
            </div>
        @else
            <form class="two-factor-start" method="POST" action="{{ route('admin.profile.two-factor.start') }}">
                @csrf
                <div class="field">
                    <label for="two_factor_password">ยืนยันรหัสผ่านปัจจุบัน</label>
                    <input id="two_factor_password" name="current_password" type="password" autocomplete="current-password" required>
                    @error('current_password', 'twoFactor') <small class="field-error">{{ $message }}</small> @enderror
                </div>
                <button class="button" type="submit">เริ่มตั้งค่า 2FA</button>
            </form>
        @endif
</section>
