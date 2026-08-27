<x-admin-layout title="Welcome Popup | 34 Build Master Admin">
    <div class="topbar home-slide-heading">
        <div>
            <p class="eyebrow">Website Promotion</p>
            <h1>Welcome Popup</h1>
            <p class="muted">จัดการรูปประกาศหรือโปรโมชันที่แสดงเมื่อผู้ชมเปิดเว็บไซต์</p>
        </div>
        <a class="button" href="{{ route('admin.welcome-popups.create') }}">เพิ่ม Popup ใหม่</a>
    </div>

    <section class="home-slide-section">
        <header class="home-slide-section-head">
            <div>
                <p class="eyebrow">DISPLAY RULE</p>
                <h2>รายการ Popup</h2>
                <p>ระบบเลือก Popup ที่เปิดใช้งาน อยู่ในช่วงเวลาเผยแพร่ และมีลำดับน้อยที่สุดมาแสดง</p>
            </div>
        </header>

        @if ($popups->isEmpty())
            <div class="card panel empty-state">
                <h3>ยังไม่มี Welcome Popup</h3>
                <p class="muted">เพิ่มรูปโปรโมชันรายการแรกเพื่อเริ่มแสดงบนหน้าบ้าน</p>
                <a class="button" href="{{ route('admin.welcome-popups.create') }}">เพิ่ม Popup</a>
            </div>
        @else
            <div class="home-slide-grid welcome-popup-grid">
                @foreach ($popups as $popup)
                    <article class="home-slide-card {{ $popup->is_active ? '' : 'is-inactive' }}">
                        <div class="home-slide-preview welcome-popup-preview">
                            @if ($popup->desktopImage?->publicUrl())
                                <img src="{{ $popup->desktopImage->publicUrl() }}" alt="{{ $popup->alt_text }}">
                            @else
                                <span>ไม่มีรูปภาพ</span>
                            @endif
                            <span class="home-slide-order">ลำดับ {{ $popup->sort_order }}</span>
                            <span class="home-slide-status {{ $popup->isCurrentlyVisible() ? 'is-active' : '' }}">
                                {{ $popup->isCurrentlyVisible() ? 'กำลังแสดง' : ($popup->is_active ? 'รอตามกำหนดเวลา' : 'ปิดใช้งาน') }}
                            </span>
                        </div>
                        <div class="home-slide-body">
                            <div>
                                <h3>{{ $popup->name }}</h3>
                                <p>
                                    {{ $popup->starts_at ? 'เริ่ม '.$popup->starts_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i') : 'แสดงได้ทันที' }}
                                    · {{ $popup->ends_at ? 'สิ้นสุด '.$popup->ends_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i') : 'ไม่กำหนดวันสิ้นสุด' }}
                                </p>
                                @if ($popup->mobile_stored_file_id)<small>มีรูปสำหรับมือถือ</small>@endif
                            </div>
                            <div class="home-slide-actions">
                                <a class="button secondary" href="{{ route('admin.welcome-popups.edit', $popup) }}">แก้ไข</a>
                                <form method="POST" action="{{ route('admin.welcome-popups.destroy', $popup) }}" onsubmit="return confirm('ต้องการลบ Popup นี้ใช่ไหม? รูปที่อัปโหลดไว้จะถูกลบจากพื้นที่จัดเก็บด้วย')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button danger" type="submit">ลบ</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-admin-layout>
