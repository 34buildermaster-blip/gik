<x-admin-layout title="เพิ่มสไลด์หน้าแรก | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Create Home Slide</p>
            <h1>เพิ่มสไลด์หน้าแรก</h1>
            <p class="muted">เลือกรูป ใส่ข้อความ และกำหนดลำดับก่อนแสดงบนเว็บไซต์</p>
        </div>
        <a class="button secondary" href="{{ route('admin.home-slides.index') }}">กลับไปหน้าสไลด์</a>
    </div>

    @include('admin.home-slides._form', ['slide' => $slide])
</x-admin-layout>
