<x-admin-layout title="แก้ไขสไลด์หน้าแรก | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Edit Home Slide</p>
            <h1>แก้ไขสไลด์</h1>
            <p class="muted">อัปเดตรูป ข้อความ สถานะ หรือลำดับการแสดงบนหน้าแรก</p>
        </div>
        <a class="button secondary" href="{{ route('admin.home-slides.index') }}">กลับไปหน้าสไลด์</a>
    </div>

    @include('admin.home-slides._form', ['slide' => $slide])
</x-admin-layout>
