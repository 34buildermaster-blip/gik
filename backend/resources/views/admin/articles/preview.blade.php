<x-admin-layout title="Preview: {{ $article->title }} | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Article Preview</p>
            <h1>ตัวอย่างบทความ</h1>
            <p class="muted">ดูภาพรวมบทความก่อนเผยแพร่จริงบนหน้าเว็บไซต์</p>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('admin.articles.edit', $article) }}">แก้ไขบทความ</a>
            <a class="button secondary" href="{{ route('admin.articles.index') }}">กลับไปหน้ารายการ</a>
        </div>
    </div>

    <section class="preview-shell">
        <article class="preview-article">
            <div class="preview-hero">
                <div>
                    <span class="badge {{ $article->status === 'published' ? 'published' : '' }}">
                        {{ $article->status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                    </span>
                    <p class="eyebrow" style="margin-top: 22px;">34 Build Master Construction</p>
                    <h2 class="preview-title">{{ $article->title }}</h2>
                    @if ($article->excerpt)
                        <p class="preview-excerpt">{{ $article->excerpt }}</p>
                    @endif
                    <div class="preview-meta">
                        <span>อัปเดต {{ $article->updated_at->format('d/m/Y') }}</span>
                        <span>{{ number_format($characterCount) }} ตัวอักษร</span>
                    </div>
                </div>

                @if ($article->coverUrl())
                    <figure class="preview-cover">
                        <img src="{{ $article->coverUrl() }}" alt="{{ $article->title }}">
                    </figure>
                @endif
            </div>

            <div class="preview-content">
                {!! $sanitizedContent !!}
            </div>
        </article>

        <aside class="preview-seo card panel">
            <p class="eyebrow">SEO Check</p>
            <h3>ข้อมูล SEO</h3>
            <dl>
                <div>
                    <dt>SEO Title</dt>
                    <dd>{{ $article->seo_title ?: $article->title }}</dd>
                </div>
                <div>
                    <dt>Description</dt>
                    <dd>{{ $article->seo_description ?: $article->excerpt ?: 'ยังไม่มี SEO description' }}</dd>
                </div>
                <div>
                    <dt>Keywords</dt>
                    <dd>{{ $article->seo_keywords ?: 'ยังไม่มี keyword' }}</dd>
                </div>
                <div>
                    <dt>Slug</dt>
                    <dd>{{ $article->slug }}</dd>
                </div>
            </dl>
        </aside>
    </section>
</x-admin-layout>
