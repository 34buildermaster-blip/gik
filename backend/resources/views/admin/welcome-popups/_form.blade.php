<form class="card panel form home-slide-form" method="POST" enctype="multipart/form-data" action="{{ $popup->exists ? route('admin.welcome-popups.update', $popup) : route('admin.welcome-popups.store') }}">
    @csrf
    @if ($popup->exists)
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field full">
            <label for="name">ชื่อรายการสำหรับแอดมิน</label>
            <input id="name" name="name" type="text" maxlength="255" value="{{ old('name', $popup->name) }}" placeholder="เช่น โปรสร้างบ้านเดือนสิงหาคม" required>
            @error('name')<small class="field-error">{{ $message }}</small>@enderror
        </div>

        <div class="field full">
            <label for="alt_text">คำอธิบายรูปสำหรับผู้ใช้โปรแกรมอ่านหน้าจอ</label>
            <input id="alt_text" name="alt_text" type="text" maxlength="255" value="{{ old('alt_text', $popup->alt_text) }}" placeholder="เช่น โปรโมชั่นรับออกแบบบ้านพร้อมประเมินงบเบื้องต้น" required>
            @error('alt_text')<small class="field-error">{{ $message }}</small>@enderror
        </div>

        <div class="field full">
            <label for="link_url">ลิงก์เมื่อคลิกรูป (ไม่บังคับ)</label>
            <input id="link_url" name="link_url" type="text" maxlength="2048" value="{{ old('link_url', $popup->link_url) }}" placeholder="/contact หรือ https://line.me/...">
            <small>ใช้ /contact สำหรับหน้าภายใน หรือใส่ลิงก์ https:// สำหรับเว็บไซต์ภายนอก</small>
            @error('link_url')<small class="field-error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="starts_at">เริ่มแสดง (ไม่บังคับ)</label>
            <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $popup->starts_at?->timezone(config('app.display_timezone'))->format('Y-m-d\TH:i')) }}">
            @error('starts_at')<small class="field-error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="ends_at">สิ้นสุดการแสดง (ไม่บังคับ)</label>
            <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $popup->ends_at?->timezone(config('app.display_timezone'))->format('Y-m-d\TH:i')) }}">
            @error('ends_at')<small class="field-error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="sort_order">ลำดับความสำคัญ</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $popup->sort_order) }}" required>
            <small>เลขน้อยจะแสดงก่อนเมื่อมีหลายรายการ</small>
        </div>

        <label class="settings-toggle">
            <span><strong>เปิดใช้งาน</strong><small>ปิดชั่วคราวได้โดยไม่ต้องลบรูป</small></span>
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $popup->is_active))>
            <i></i>
        </label>
    </div>

    <div class="welcome-popup-upload-grid">
        <section class="home-slide-upload">
            <div class="home-slide-current" data-popup-preview="desktop">
                @if ($popup->desktopImage?->publicUrl())
                    <img src="{{ $popup->desktopImage->publicUrl() }}" alt="{{ $popup->alt_text }}">
                @else
                    <span>เลือกภาพเดสก์ท็อปเพื่อดูตัวอย่าง</span>
                @endif
            </div>
            <div class="field">
                <label for="desktop_image">{{ $popup->exists ? 'เปลี่ยนรูปหลัก' : 'รูปหลัก' }}</label>
                <input id="desktop_image" name="desktop_image" type="file" accept="image/jpeg,image/png,image/webp" {{ $popup->exists ? '' : 'required' }} data-popup-image="desktop">
                <small>แนะนำอัตราส่วนประมาณ 4:5 หรือ 1:1 ระบบจะแปลงเป็น WebP ให้อัตโนมัติ</small>
                @error('desktop_image')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </section>

        <section class="home-slide-upload">
            <div class="home-slide-current" data-popup-preview="mobile">
                @if ($popup->mobileImage?->publicUrl())
                    <img src="{{ $popup->mobileImage->publicUrl() }}" alt="{{ $popup->alt_text }}">
                @else
                    <span>ไม่ใส่ก็ได้ ระบบจะใช้รูปหลักแทน</span>
                @endif
            </div>
            <div class="field">
                <label for="mobile_image">รูปสำหรับมือถือ (ไม่บังคับ)</label>
                <input id="mobile_image" name="mobile_image" type="file" accept="image/jpeg,image/png,image/webp" data-popup-image="mobile">
                <small>แนะนำภาพแนวตั้ง เพื่อให้ข้อความในภาพอ่านง่ายบนจอเล็ก</small>
                @error('mobile_image')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            @if ($popup->mobile_stored_file_id)
                <label class="auth-remember"><input type="checkbox" name="remove_mobile_image" value="1"> ลบรูปมือถือเดิมและใช้รูปหลักแทน</label>
            @endif
        </section>
    </div>

    <div class="actions">
        <button class="button" type="submit">{{ $popup->exists ? 'บันทึกการแก้ไข' : 'เพิ่ม Popup' }}</button>
        <a class="button secondary" href="{{ route('admin.welcome-popups.index') }}">ยกเลิก</a>
    </div>
</form>

<script>
    (() => {
        document.querySelectorAll('[data-popup-image]').forEach((input) => {
            input.addEventListener('change', () => {
                const file = input.files?.[0];
                const preview = document.querySelector(`[data-popup-preview="${input.dataset.popupImage}"]`);
                if (!file || !preview) return;
                const url = URL.createObjectURL(file);
                preview.innerHTML = '';
                const image = document.createElement('img');
                image.src = url;
                image.alt = 'ตัวอย่างรูปที่เลือก';
                image.onload = () => URL.revokeObjectURL(url);
                preview.appendChild(image);
            });
        });
    })();
</script>
