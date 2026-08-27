<x-admin-layout title="เพิ่มแบบบ้าน | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Create House Design</p>
            <h1>เพิ่มแบบบ้าน</h1>
            <p class="muted">กรอกข้อมูลหลัก สเปก แนวคิด รูปปก และแกลเลอรีสำหรับหน้า Detail</p>
        </div>
        <a class="button secondary" href="{{ route('admin.house-designs.index') }}">กลับไปหน้ารวม</a>
    </div>

    @include('admin.house-designs._form', ['design' => $design])
</x-admin-layout>
