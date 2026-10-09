<x-admin-layout title="แดชบอร์ด | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">ADMIN OVERVIEW</p>
            <h1>ภาพรวมการดำเนินงาน</h1>
            <p class="muted" style="margin:7px 0 0;">ติดตามโครงการ ทีมงาน อัปเดตหน้างาน และข้อมูลเว็บไซต์ในหน้าเดียว</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('admin.calendar.index') }}">
                <x-ui-icon name="calendar" /> ปฏิทินโครงการ
            </a>
            <a class="button" href="{{ route('admin.projects.create') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                สร้างโครงการ
            </a>
            <a class="button secondary" href="{{ route('admin.users.create') }}">เพิ่มผู้ใช้งาน</a>
        </div>
    </div>

    <nav class="dashboard-range" aria-label="ช่วงเวลาของข้อมูล">
        <span>ช่วงข้อมูล</span>
        @foreach([7 => '7 วัน', 30 => '30 วัน', 90 => 'ไตรมาส'] as $days => $label)
            <a class="{{ $rangeDays === $days ? 'is-active' : '' }}" href="{{ route('admin.dashboard', ['range' => $days]) }}">{{ $label }}</a>
        @endforeach
        <small>ตั้งแต่ {{ $rangeStart->format('d/m/Y') }}</small>
    </nav>

    <section class="dashboard-system-status" aria-label="สถานะระบบอัตโนมัติ">
        <div><span>สำรองข้อมูลล่าสุด</span><strong>{{ $latestBackup?->created_at?->timezone(config('app.display_timezone'))->format('d/m/Y H:i') ?: 'รอรอบสำรองข้อมูล' }}</strong></div>
        <div><span>ตรวจสุขภาพระบบล่าสุด</span><strong>{{ $lastHealthCheckAt?->timezone(config('app.display_timezone'))->format('d/m/Y H:i') ?: 'รอรอบตรวจสอบ' }}</strong></div>
        <div><span>พื้นที่เก็บไฟล์</span><strong>{{ $mediaStorageDriver === 'google' ? 'Google Drive' : 'พื้นที่เซิร์ฟเวอร์' }}</strong></div>
    </section>

    <section class="dashboard-stats" aria-label="สรุปการดำเนินงาน">
        <article class="card stat-card is-primary">
            <p class="stat-label">โครงการที่กำลังดำเนินงาน</p>
            <div class="stat-value">{{ $activeProjectCount }}</div>
            <p class="stat-caption">จากทั้งหมด {{ $projectCount }} โครงการ · สำเร็จแล้ว {{ $completedProjectCount }}</p>
            <a class="stat-link" href="{{ route('admin.projects.index', ['status' => 'in_progress']) }}" aria-label="ดูโครงการที่กำลังดำเนินงาน"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg></a>
        </article>
        <article class="card stat-card">
            <p class="stat-label">ความคืบหน้าเฉลี่ย</p>
            <div class="stat-value">{{ $averageProgress }}%</div>
            <p class="stat-caption">คำนวณจากทุกโครงการในระบบ</p>
            <a class="stat-link" href="{{ route('admin.projects.index') }}" aria-label="ตรวจความคืบหน้าโครงการ"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg></a>
        </article>
        <article class="card stat-card">
            <p class="stat-label">อัปเดตหน้างาน {{ $rangeDays }} วัน</p>
            <div class="stat-value">{{ $updatesInRange }}</div>
            <p class="stat-caption">รูป รายงาน และ Timeline ล่าสุด</p>
            <a class="stat-link" href="{{ route('admin.projects.index') }}" aria-label="ดูอัปเดตหน้างาน"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg></a>
        </article>
        <article class="card stat-card">
            <p class="stat-label">ผู้ใช้งานทั้งหมด</p>
            <div class="stat-value">{{ $userCount }}</div>
            <p class="stat-caption">ลูกค้า {{ $customerCount }} · ผู้ตรวจ {{ $inspectorCount }}</p>
            <a class="stat-link" href="{{ route('admin.users.index') }}" aria-label="ดูผู้ใช้งานทั้งหมด"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7"></path><path d="M7 7h10v10"></path></svg></a>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="card panel dashboard-projects-panel">
            <div class="panel-heading">
                <div><p class="eyebrow">PROJECT OPERATIONS</p><h2>โครงการที่ต้องดูแล</h2><p>รายการที่อยู่ระหว่างเตรียมงาน ดำเนินงาน หรือพักงาน</p></div>
                <a class="text-link" href="{{ route('admin.projects.index') }}">ดูทั้งหมด</a>
            </div>
            <div class="dashboard-project-list">
                @forelse($latestProjects as $project)
                    <a class="dashboard-project-row" href="{{ route('admin.projects.show', $project) }}">
                        <span class="dashboard-project-code">{{ $project->code }}</span>
                        <span class="dashboard-project-copy"><strong>{{ $project->name }}</strong><small>{{ $project->manager?->name ?: 'ยังไม่กำหนดผู้ดูแล' }} · {{ $project->updates_count }} อัปเดต</small></span>
                        <span class="dashboard-project-progress"><strong>{{ $project->progress_percent }}%</strong><span class="progress-track"><i style="width:{{ $project->progress_percent }}%"></i></span></span>
                        <span class="project-status-label">{{ $projectStatusLabels[$project->status] ?? $project->status }}</span>
                    </a>
                @empty
                    <div class="dashboard-empty"><strong>ยังไม่มีโครงการที่กำลังดำเนินงาน</strong><a href="{{ route('admin.projects.create') }}">สร้างโครงการแรก</a></div>
                @endforelse
            </div>
        </article>

        <article class="card panel dashboard-attention-panel">
            <div class="panel-heading"><div><p class="eyebrow">ATTENTION</p><h2>สิ่งที่ต้องติดตาม</h2><p>รายการที่ควรตรวจสอบหรือดำเนินการต่อ</p></div></div>
            <div class="dashboard-attention-list">
                <a href="{{ route('admin.calendar.index') }}"><span class="attention-dot is-danger"></span><span><strong>ขั้นตอนงานเกินกำหนด</strong><small>ยังดำเนินการไม่ครบ 100%</small></span><b>{{ $overdueStepCount }}</b></a>
                <a href="{{ route('admin.projects.index') }}"><span class="attention-dot is-danger"></span><span><strong>โครงการเกินกำหนดส่ง</strong><small>ยังไม่ได้ปิดเป็นโครงการเสร็จสิ้น</small></span><b>{{ $overdueProjectCount }}</b></a>
                <a href="{{ route('admin.projects.index') }}"><span class="attention-dot is-warning"></span><span><strong>ปัญหาหน้างานที่ยังเปิดอยู่</strong><small>เร่งด่วน {{ $urgentIssueCount }} รายการ</small></span><b>{{ $openIssueCount }}</b></a>
                <a href="{{ route('admin.projects.index') }}"><span class="attention-dot is-danger"></span><span><strong>งานไม่ผ่านหรือรอแก้ไข</strong><small>ขั้นตอนใน {{ $attentionProjectCount }} โครงการ</small></span><b>{{ $attentionProjectCount }}</b></a>
                <a href="{{ route('admin.projects.index') }}"><span class="attention-dot is-warning"></span><span><strong>อัปเดตรอ Admin ตรวจ</strong><small>ยังไม่กระทบเปอร์เซ็นต์และลูกค้ายังไม่เห็น</small></span><b>{{ $pendingReviewCount }}</b></a>
                <a href="{{ route('admin.projects.index') }}"><span class="attention-dot is-warning"></span><span><strong>ยังไม่มีผู้ดูแลโครงการ</strong><small>ควรมอบหมาย Admin หรือผู้ตรวจ</small></span><b>{{ $unassignedProjectCount }}</b></a>
                <a href="{{ route('admin.projects.index') }}"><span class="attention-dot"></span><span><strong>อัปเดตหน้างานฉบับร่าง</strong><small>ยังไม่แสดงให้ลูกค้าเห็น</small></span><b>{{ $draftUpdateCount }}</b></a>
                <a href="{{ route('admin.articles.index', ['status' => 'draft']) }}"><span class="attention-dot"></span><span><strong>บทความฉบับร่าง</strong><small>รอตรวจสอบก่อนเผยแพร่</small></span><b>{{ $draftCount }}</b></a>
            </div>
        </article>
    </section>

    <section class="dashboard-operations-grid">
        <article class="card panel">
            <div class="panel-heading"><div><p class="eyebrow">PROJECT CALENDAR</p><h2>กำหนดการที่กำลังจะถึง</h2><p>นัดหมายเรียงตามวันและเวลาที่ใกล้ที่สุด</p></div><a class="text-link" href="{{ route('admin.calendar.index') }}">เปิดปฏิทิน</a></div>
            <div class="dashboard-event-list">
                @forelse($upcomingEvents as $event)
                    <a href="{{ route('admin.calendar.index', ['month' => $event->starts_at->format('Y-m'), 'event' => $event->id]) }}#event-{{ $event->id }}">
                        <time><strong>{{ $event->starts_at->format('d') }}</strong><span>{{ $event->starts_at->locale('th')->translatedFormat('M') }}</span></time>
                        <span><em>{{ $event->project->code }} · {{ \App\Models\ProjectEvent::TYPE_LABELS[$event->type] }}</em><strong>{{ $event->title }}</strong><small>{{ $event->starts_at->format('H:i') }} น.{{ $event->assignee ? ' · '.$event->assignee->name : '' }}</small></span>
                        <x-ui-icon name="chevron-right" />
                    </a>
                @empty
                    <div class="dashboard-empty"><strong>ยังไม่มีนัดหมาย</strong><a href="{{ route('admin.calendar.index') }}#calendar-create">เพิ่มนัดหมายแรก</a></div>
                @endforelse
            </div>
        </article>

        <article class="card panel">
            <div class="panel-heading"><div><p class="eyebrow">PROJECT HEALTH</p><h2>สถานะโครงการ</h2><p>เปรียบเทียบจำนวนโครงการในแต่ละสถานะ</p></div></div>
            @php($maxStatusCount = max(1, (int) $statusCounts->max()))
            <div class="dashboard-status-bars">
                @foreach($projectStatusLabels as $status => $label)
                    <div><header><span>{{ $label }}</span><strong>{{ $statusCounts[$status] ?? 0 }}</strong></header><i><b style="width:{{ (($statusCounts[$status] ?? 0) / $maxStatusCount) * 100 }}%"></b></i></div>
                @endforeach
            </div>
            <div class="dashboard-workload">
                <h3>ภาระงานของผู้ดูแล</h3>
                @forelse($managerWorkload as $manager)
                    <div><span>{{ $manager->name }}<small>{{ $manager->role === 'inspector' ? 'ผู้ตรวจหน้างาน' : 'Admin' }}</small></span><strong>{{ $manager->active_projects_count }} โครงการ</strong></div>
                @empty
                    <p class="muted">ยังไม่มีโครงการที่มอบหมายผู้ดูแล</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="dashboard-lower-grid">
        <article class="card panel">
            <div class="panel-heading">
                <div><p class="eyebrow">RECENT SITE UPDATES</p><h2>กิจกรรมหน้างานล่าสุด</h2><p>อัปเดตที่ทีมงานบันทึกเข้าระบบล่าสุด</p></div>
                <a class="text-link" href="{{ route('admin.projects.index') }}">เปิดโครงการ</a>
            </div>
            <div class="dashboard-update-list">
                @forelse($latestProjectUpdates as $updateItem)
                    <a class="dashboard-update-row" href="{{ route('admin.projects.show', $updateItem->project) }}#project-updates">
                        <span class="dashboard-update-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span><em>{{ $updateItem->project->code }} · {{ $projectStageLabels[$updateItem->stage] ?? $updateItem->stage }}</em><strong>{{ $updateItem->title }}</strong><small>{{ $updateItem->creator?->name ?: 'ทีมงาน' }} · {{ $updateItem->work_performed_at->format('d/m/Y H:i') }} · {{ \App\Models\ProjectUpdate::STATUS_LABELS[$updateItem->status] ?? $updateItem->status }}</small></span>
                        <span class="dashboard-update-percent">{{ $updateItem->progress_percent }}%</span>
                    </a>
                @empty
                    <div class="dashboard-empty"><strong>ยังไม่มีอัปเดตหน้างาน</strong><span>รายการใหม่จะแสดงที่นี่เมื่อทีมเริ่มบันทึกข้อมูล</span></div>
                @endforelse
            </div>
        </article>

        <article class="card panel">
            <div class="panel-heading"><div><p class="eyebrow">QUICK ACTIONS</p><h2>งานที่ทำได้ทันที</h2><p>ทางลัดสำหรับงานที่ใช้เป็นประจำ</p></div></div>
            <div class="dashboard-action-list">
                <a href="{{ route('admin.calendar.index') }}#calendar-create"><span class="quick-icon"><x-ui-icon name="calendar" /></span><span><strong>เพิ่มนัดหมาย</strong><small>นัดตรวจ ประชุม หรือส่งมอบ</small></span><b><x-ui-icon name="chevron-right" /></b></a>
                <a href="{{ route('admin.projects.create') }}"><span class="quick-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg></span><span><strong>สร้างโครงการใหม่</strong><small>กำหนดลูกค้าและผู้ดูแล</small></span><b><x-ui-icon name="chevron-right" /></b></a>
                <a href="{{ route('admin.users.create') }}"><span class="quick-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M19 8v6"></path><path d="M16 11h6"></path></svg></span><span><strong>เพิ่มผู้ใช้งาน</strong><small>ลูกค้า ผู้ตรวจ หรือ Admin</small></span><b><x-ui-icon name="chevron-right" /></b></a>
                <a href="{{ route('admin.articles.create') }}"><span class="quick-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg></span><span><strong>เขียนบทความใหม่</strong><small>เพิ่มเนื้อหาและข้อมูล SEO</small></span><b><x-ui-icon name="chevron-right" /></b></a>
                <a href="{{ route('admin.settings.edit') }}"><span class="quick-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v3M12 19v3M4.9 4.9 7 7M17 17l2.1 2.1M2 12h3M19 12h3"></path></svg></span><span><strong>ตั้งค่าหน้าเว็บไซต์</strong><small>ข้อมูลบริษัทและช่องทางติดต่อ</small></span><b><x-ui-icon name="chevron-right" /></b></a>
            </div>
        </article>
    </section>

    <section class="card panel dashboard-content-panel">
        <div class="panel-heading">
            <div><p class="eyebrow">WEBSITE CONTENT</p><h2>สถานะคอนเทนต์เว็บไซต์</h2><p>บทความยังอยู่ใน Dashboard แต่เป็นส่วนสนับสนุนการดำเนินงาน</p></div>
            <a class="text-link" href="{{ route('admin.articles.index') }}">จัดการบทความ</a>
        </div>
        <div class="dashboard-content-layout">
            <div class="dashboard-content-metrics">
                <div><span>ทั้งหมด</span><strong>{{ $articleCount }}</strong></div>
                <div><span>เผยแพร่แล้ว</span><strong>{{ $publishedCount }}</strong></div>
                <div><span>ฉบับร่าง</span><strong>{{ $draftCount }}</strong></div>
                <div><span>SEO พร้อมใช้</span><strong>{{ $seoReadyCount }}</strong></div>
            </div>
            <div class="dashboard-article-list">
                @forelse($latestArticles as $article)
                    <a href="{{ route('admin.articles.edit', $article) }}"><span><strong>{{ $article->title }}</strong><small>{{ $article->user?->name ?? 'ทีมงาน' }} · {{ $article->updated_at->format('d/m/Y H:i') }}</small></span><em class="{{ $article->status === 'published' ? 'is-published' : '' }}">{{ $article->status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}</em></a>
                @empty
                    <div class="dashboard-empty"><strong>ยังไม่มีบทความ</strong><a href="{{ route('admin.articles.create') }}">เริ่มเขียนบทความแรก</a></div>
                @endforelse
            </div>
        </div>
    </section>
</x-admin-layout>
