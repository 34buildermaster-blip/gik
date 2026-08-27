<x-admin-layout title="เพิ่ม Welcome Popup | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Create Promotion</p>
            <h1>เพิ่ม Welcome Popup</h1>
            <p class="muted">อัปโหลดรูปหลัก แล้วกำหนดลิงก์หรือช่วงเวลาเพิ่มเติมได้ตามต้องการ</p>
        </div>
        <a class="button secondary" href="{{ route('admin.welcome-popups.index') }}">กลับไปรายการ</a>
    </div>

    @include('admin.welcome-popups._form', ['popup' => $popup])
</x-admin-layout>
