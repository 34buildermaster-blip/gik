<x-admin-layout title="แดชบอร์ด | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Dashboard</p>
            <h1>แดชบอร์ดหลังบ้าน</h1>
            <p class="muted">ภาพรวมบทความและคอนเทนต์สำหรับอัปเดต SEO ของเว็บไซต์</p>
        </div>
        <a class="button" href="{{ route('admin.articles.create') }}">+ เพิ่มบทความ</a>
    </div>

    <section class="grid stats">
        <article class="card panel">
            <p class="eyebrow">All Articles</p>
            <div class="stat-number">{{ $articleCount }}</div>
            <p class="muted">บทความทั้งหมดในระบบ</p>
        </article>
        <article class="card panel">
            <p class="eyebrow">Published</p>
            <div class="stat-number">{{ $publishedCount }}</div>
            <p class="muted">บทความที่เผยแพร่แล้ว</p>
        </article>
        <article class="card panel">
            <p class="eyebrow">Draft</p>
            <div class="stat-number">{{ $draftCount }}</div>
            <p class="muted">บทความฉบับร่าง</p>
        </article>
    </section>

    <section class="card panel" style="margin-top: 22px;">
        <div class="topbar" style="margin-bottom: 10px;">
            <div>
                <p class="eyebrow">Latest Updates</p>
                <h2>บทความล่าสุด</h2>
            </div>
            <a class="button secondary" href="{{ route('admin.articles.index') }}">จัดการทั้งหมด</a>
        </div>

        @forelse ($latestArticles as $article)
            <div class="row-actions" style="justify-content: space-between; border-top: 1px solid var(--line); padding: 16px 0;">
                <div>
                    <h3>{{ $article->title }}</h3>
                    <p class="muted" style="margin: 4px 0 0;">{{ $article->updated_at->format('d/m/Y H:i') }}</p>
                </div>
                <span class="badge {{ $article->status === 'published' ? 'published' : '' }}">
                    {{ $article->status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                </span>
            </div>
        @empty
            <p class="muted">ยังไม่มีบทความ เริ่มจากการเพิ่มบทความแรกได้เลย</p>
        @endforelse
    </section>
</x-admin-layout>
