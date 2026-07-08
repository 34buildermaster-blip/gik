<x-admin-layout title="เพิ่มบทความ | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Create Article</p>
            <h1>เพิ่มบทความใหม่</h1>
            <p class="muted">ใส่เนื้อหา รูปภาพประกอบ และข้อมูล SEO สำหรับหน้าเว็บไซต์</p>
        </div>
        <a class="button secondary" href="{{ route('admin.articles.index') }}">กลับไปหน้ารายการ</a>
    </div>

    @include('admin.articles._form', ['article' => $article])
</x-admin-layout>
