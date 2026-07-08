<x-admin-layout title="แก้ไขบทความ | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Edit Article</p>
            <h1>แก้ไขบทความ</h1>
            <p class="muted">ปรับเนื้อหา รูปหน้าปก สถานะเผยแพร่ และ SEO ได้จากหน้านี้</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('admin.articles.preview', $article) }}" target="_blank">Preview</a>
            <a class="button secondary" href="{{ route('admin.articles.index') }}">กลับไปหน้ารายการ</a>
        </div>
    </div>

    @include('admin.articles._form', ['article' => $article])
</x-admin-layout>
