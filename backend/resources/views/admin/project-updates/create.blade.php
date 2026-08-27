<x-admin-layout title="เพิ่มอัปเดตหน้างาน | 34 Build Master Admin">
    <div class="topbar"><div><p class="eyebrow">{{ $project->code }}</p><h1>เพิ่มอัปเดตหน้างาน</h1><p class="muted">{{ $project->name }} · บันทึกรูป วันที่ เวลา และความคืบหน้า</p></div><a class="button secondary" href="{{ route('admin.projects.show', $project) }}">กลับไปโครงการ</a></div>
    @include('admin.project-updates._form')
</x-admin-layout>
