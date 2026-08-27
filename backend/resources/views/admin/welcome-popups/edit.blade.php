<x-admin-layout title="แก้ไข Welcome Popup | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Edit Promotion</p>
            <h1>แก้ไข Welcome Popup</h1>
            <p class="muted">เปลี่ยนรูป ลิงก์ ช่วงเวลา หรือตั้งค่าการแสดงผล</p>
        </div>
        <a class="button secondary" href="{{ route('admin.welcome-popups.index') }}">กลับไปรายการ</a>
    </div>

    @include('admin.welcome-popups._form', ['popup' => $popup])
</x-admin-layout>
