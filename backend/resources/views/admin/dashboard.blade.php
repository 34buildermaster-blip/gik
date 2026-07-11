<x-admin-layout title="แดชบอร์ด | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Dashboard</p>
            <h1>ภาพรวมคอนเทนต์</h1>
            <p class="muted" style="margin: 7px 0 0;">จัดการบทความ การเผยแพร่ และความพร้อมด้าน SEO ในที่เดียว</p>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('admin.articles.create') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                เพิ่มบทความ
            </a>
            <a class="button secondary" href="{{ url('/') }}" target="_blank">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7"></path><path d="m10 14 11-11"></path><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"></path></svg>
                ดูเว็บไซต์
            </a>
        </div>
    </div>

    <section class="dashboard-stats" aria-label="สถิติบทความ">
        <article class="card stat-card is-primary">
            <p class="stat-label">บทความทั้งหมด</p>
            <div class="stat-value">{{ $articleCount }}</div>
            <p class="stat-caption">{{ $recentlyUpdatedCount }} รายการมีการอัปเดตใน 7 วัน</p>
            <a class="stat-link" href="{{ route('admin.articles.index') }}" aria-label="ดูบทความทั้งหมด">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg>
            </a>
        </article>
        <article class="card stat-card">
            <p class="stat-label">เผยแพร่แล้ว</p>
            <div class="stat-value">{{ $publishedCount }}</div>
            <p class="stat-caption">พร้อมแสดงบนหน้าเว็บไซต์</p>
            <a class="stat-link" href="{{ route('admin.articles.index', ['status' => 'published']) }}" aria-label="ดูบทความที่เผยแพร่แล้ว">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg>
            </a>
        </article>
        <article class="card stat-card">
            <p class="stat-label">ฉบับร่าง</p>
            <div class="stat-value">{{ $draftCount }}</div>
            <p class="stat-caption">รอตรวจสอบก่อนเผยแพร่</p>
            <a class="stat-link" href="{{ route('admin.articles.index', ['status' => 'draft']) }}" aria-label="ดูบทความฉบับร่าง">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg>
            </a>
        </article>
        <article class="card stat-card">
            <p class="stat-label">SEO พร้อมใช้</p>
            <div class="stat-value">{{ $seoReadyCount }}</div>
            <p class="stat-caption">มี Title และ Description ครบ</p>
            <a class="stat-link" href="{{ route('admin.articles.index') }}" aria-label="ตรวจข้อมูล SEO">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg>
            </a>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="card panel">
            <div class="panel-heading">
                <div>
                    <h2>กิจกรรมคอนเทนต์</h2>
                    <p>จำนวนบทความที่แก้ไขในช่วง 7 วันล่าสุด</p>
                </div>
                <span class="badge published">7 วัน</span>
            </div>
            <div class="activity-chart" aria-label="กราฟกิจกรรมบทความ 7 วัน">
                @foreach ($weeklyActivity as $day)
                    <div class="activity-day" title="{{ $day['label'] }} {{ $day['count'] }} รายการ">
                        <div class="activity-track">
                            <span class="activity-bar" style="--bar: {{ $day['height'] }}%;"></span>
                        </div>
                        <span>{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="card panel">
            <div class="panel-heading">
                <div>
                    <h2>งานที่ทำได้ทันที</h2>
                    <p>ทางลัดสำหรับจัดการคอนเทนต์</p>
                </div>
            </div>
            <div class="quick-actions">
                <a class="quick-action" href="{{ route('admin.articles.create') }}">
                    <span class="quick-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg></span>
                    <span><strong>เขียนบทความใหม่</strong><small>เพิ่มเนื้อหา รูปภาพ และ SEO</small></span>
                    <span>&rsaquo;</span>
                </a>
                <a class="quick-action" href="{{ route('admin.articles.index', ['status' => 'draft']) }}">
                    <span class="quick-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"></path></svg></span>
                    <span><strong>ตรวจฉบับร่าง</strong><small>{{ $draftCount }} รายการรอการตรวจสอบ</small></span>
                    <span>&rsaquo;</span>
                </a>
                <a class="quick-action" href="{{ route('admin.articles.index') }}">
                    <span class="quick-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5V5a2 2 0 0 1 2-2h11a3 3 0 0 1 3 3v15H6a2 2 0 0 1-2-1.5z"></path><path d="M8 8h8"></path><path d="M8 12h8"></path></svg></span>
                    <span><strong>จัดการบทความ</strong><small>ค้นหา แก้ไข และดูตัวอย่าง</small></span>
                    <span>&rsaquo;</span>
                </a>
            </div>
        </article>
    </section>

    <section class="dashboard-lower-grid">
        <article class="card panel">
            <div class="panel-heading">
                <div>
                    <h2>บทความล่าสุด</h2>
                    <p>รายการที่มีการแก้ไขล่าสุดในระบบ</p>
                </div>
                <a class="text-link" href="{{ route('admin.articles.index') }}">ดูทั้งหมด</a>
            </div>
            <div class="article-list">
                @forelse ($latestArticles as $article)
                    <a class="article-row" href="{{ route('admin.articles.edit', $article) }}">
                        <span class="article-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>
                            <strong>{{ $article->title }}</strong>
                            <small>แก้ไขโดย {{ $article->user?->name ?? 'ทีมงาน' }} · {{ $article->updated_at->format('d/m/Y H:i') }}</small>
                        </span>
                        <span class="badge {{ $article->status === 'published' ? 'published' : '' }}">
                            {{ $article->status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                        </span>
                    </a>
                @empty
                    <div style="padding: 30px 0; text-align: center;">
                        <p class="muted">ยังไม่มีบทความ เริ่มจากสร้างบทความแรกได้เลย</p>
                        <a class="button" href="{{ route('admin.articles.create') }}">เพิ่มบทความ</a>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="card panel">
            <div class="panel-heading">
                <div>
                    <h2>ความคืบหน้าการเผยแพร่</h2>
                    <p>สัดส่วนบทความที่ออนไลน์แล้ว</p>
                </div>
            </div>
            <div class="progress-wrap">
                <div class="progress-ring" style="--progress: {{ $publishPercent * 3.6 }}deg;">
                    <div class="progress-value">
                        <strong>{{ $publishPercent }}%</strong>
                        <span>เผยแพร่แล้ว</span>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="card panel dashboard-note" style="margin-top: 14px;">
        <div class="panel-heading" style="margin-bottom: 0;">
            <div>
                <h2>รักษาคอนเทนต์ให้สดใหม่อยู่เสมอ</h2>
                <p style="margin-bottom: 0;">อัปเดตบทความและข้อมูล SEO อย่างสม่ำเสมอ เพื่อช่วยให้ลูกค้าค้นหา 34 Build Master ได้ง่ายขึ้น</p>
            </div>
            <a class="button" href="{{ route('admin.articles.create') }}">เริ่มเขียนบทความ</a>
        </div>
    </section>
</x-admin-layout>
