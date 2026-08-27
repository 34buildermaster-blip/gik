<x-admin-layout title="แก้ไขแบบบ้าน | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Edit House Design</p>
            <h1>แก้ไขแบบบ้าน</h1>
            <p class="muted">อัปเดตข้อมูล รูปปก รายละเอียด SEO และจัดการภาพในแกลเลอรี</p>
        </div>
        <a class="button secondary" href="{{ route('admin.house-designs.index') }}">กลับไปหน้ารวม</a>
    </div>

    @include('admin.house-designs._form', ['design' => $design])
</x-admin-layout>
