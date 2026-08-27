<x-admin-layout title="สร้างโครงการ | 34 Build Master Admin">
    <div class="topbar">
        <div><p class="eyebrow">NEW PROJECT</p><h1>สร้างโครงการลูกค้า</h1><p class="muted">กำหนดข้อมูล ระยะเวลา ผู้ดูแล และลูกค้าที่มีสิทธิ์ติดตามงาน</p></div>
        <a class="button secondary" href="{{ route('admin.projects.index') }}">กลับหน้ารวม</a>
    </div>
    @include('admin.projects._form')
</x-admin-layout>
