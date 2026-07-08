<form class="card panel form" method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
    @csrf
    @if ($article->exists)
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="title">ชื่อบทความ</label>
            <input id="title" name="title" type="text" value="{{ old('title', $article->title) }}" required>
        </div>

        <div class="field">
            <label for="status">สถานะ</label>
            <select id="status" name="status" required>
                <option value="draft" @selected(old('status', $article->status) === 'draft')>ฉบับร่าง</option>
                <option value="published" @selected(old('status', $article->status) === 'published')>เผยแพร่</option>
            </select>
        </div>

        <div class="field full">
            <label for="excerpt">คำอธิบายสั้น</label>
            <textarea id="excerpt" name="excerpt" style="min-height: 110px;">{{ old('excerpt', $article->excerpt) }}</textarea>
        </div>

        <div class="field full">
            <label for="content">เนื้อหาบทความ</label>
            <textarea id="content" name="content" required>{{ old('content', $article->content) }}</textarea>
        </div>

        <div class="field">
            <label for="cover_image">รูปภาพประกอบ</label>
            <input id="cover_image" name="cover_image" type="file" accept="image/*">
            <p class="muted" style="margin: 0;">รองรับ jpg, png, webp ขนาดไม่เกิน 4MB</p>
        </div>

        @if ($article->cover_image)
            <div class="field">
                <label>รูปปัจจุบัน</label>
                <img class="thumb" style="width: 220px; height: 140px;" src="{{ asset($article->cover_image) }}" alt="{{ $article->title }}">
            </div>
        @endif

        <div class="field">
            <label for="seo_title">SEO Title</label>
            <input id="seo_title" name="seo_title" type="text" value="{{ old('seo_title', $article->seo_title) }}">
        </div>

        <div class="field">
            <label for="seo_keywords">SEO Keywords</label>
            <input id="seo_keywords" name="seo_keywords" type="text" value="{{ old('seo_keywords', $article->seo_keywords) }}" placeholder="ออกแบบบ้าน, รีโนเวทบ้าน, บิวท์อิน">
        </div>

        <div class="field full">
            <label for="seo_description">SEO Description</label>
            <textarea id="seo_description" name="seo_description" style="min-height: 110px;">{{ old('seo_description', $article->seo_description) }}</textarea>
        </div>
    </div>

    <div class="actions">
        <button class="button" type="submit">{{ $article->exists ? 'บันทึกการแก้ไข' : 'สร้างบทความ' }}</button>
        <a class="button secondary" href="{{ route('admin.articles.index') }}">ยกเลิก</a>
    </div>
</form>
