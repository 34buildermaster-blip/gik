<x-admin-layout title="แก้ไขโครงการ | 34 Build Master Admin">
    <div class="topbar">
        <div><p class="eyebrow">EDIT PROJECT</p><h1>แก้ไขโครงการ</h1><p class="muted">อัปเดตข้อมูล ระยะเวลา ความคืบหน้า และสิทธิ์ของลูกค้า</p></div>
        <a class="button secondary" href="{{ route('admin.projects.show', $project) }}">กลับหน้ารายละเอียด</a>
    </div>
    @include('admin.projects._form')
</x-admin-layout>
