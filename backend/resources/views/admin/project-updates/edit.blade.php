<x-admin-layout title="แก้ไขอัปเดตหน้างาน | 34 Build Master Admin">
    <div class="topbar"><div><p class="eyebrow">{{ $project->code }}</p><h1>แก้ไขอัปเดตหน้างาน</h1><p class="muted">{{ $update->title }}</p></div><a class="button secondary" href="{{ route('admin.projects.show', $project) }}">กลับไปโครงการ</a></div>
    @include('admin.project-updates._form')
</x-admin-layout>
