<x-admin-layout title="งานตรวจหน้างาน | 34 Build Master">
    <div class="topbar">
        <div>
            <p class="eyebrow">SITE INSPECTION</p>
            <h1>งานที่ได้รับมอบหมาย</h1>
            <p class="muted" style="margin:7px 0 0;">ตรวจความคืบหน้า บันทึกผล และอัปเดตรูปหน้างานให้ลูกค้า</p>
        </div>
        <a class="button" href="{{ route('admin.projects.index') }}">ดูทุกโครงการ</a>
    </div>

    <section class="project-stats" aria-label="สรุปงานตรวจหน้างาน">
        <article class="card user-stat-card is-primary"><span>โครงการที่รับผิดชอบ</span><strong>{{ $totalProjects }}</strong><small>เฉพาะงานที่มอบหมายให้คุณ</small></article>
        <article class="card user-stat-card"><span>กำลังดำเนินงาน</span><strong>{{ $activeProjects }}</strong><small>พร้อมบันทึกความคืบหน้า</small></article>
        <article class="card user-stat-card"><span>รอ Admin ตรวจ</span><strong>{{ $pendingReviewCount }}</strong><small>ลูกค้ายังไม่เห็นรายการเหล่านี้</small></article>
        <article class="card user-stat-card"><span>ต้องติดตามแก้ไข</span><strong>{{ $attentionProjects }}</strong><small>มีขั้นตอนที่ไม่ผ่านการตรวจ</small></article>
    </section>

    <section class="card panel">
        <div class="panel-heading">
            <div><p class="eyebrow">ASSIGNED PROJECTS</p><h2>โครงการล่าสุด</h2><p>เลือกโครงการเพื่อเพิ่มรูป อัปเดต Timeline หรือบันทึกผลการตรวจ</p></div>
        </div>
        <div class="project-card-list">
            @forelse($assignedProjects as $project)
                <a class="project-list-card" href="{{ route('admin.projects.show', $project) }}">
                    <div class="project-list-main">
                        <span class="project-code">{{ $project->code }}</span>
                        <h2>{{ $project->name }}</h2>
                        <p>{{ $project->address ?: 'ยังไม่ได้ระบุที่อยู่หน้างาน' }}</p>
                        <div class="project-customer-row">
                            @foreach($project->customers->take(3) as $customer)<span>{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>@endforeach
                            <small>{{ $project->customers->pluck('name')->join(', ') ?: 'ยังไม่มีลูกค้า' }}</small>
                        </div>
                    </div>
                    <div class="project-list-progress">
                        <div><span>{{ $statusLabels[$project->status] ?? $project->status }}</span><strong>{{ $project->progress_percent }}%</strong></div>
                        <div class="progress-track"><i style="width:{{ $project->progress_percent }}%"></i></div>
                        <small>{{ $project->updates_count }} อัปเดต</small>
                    </div>
                    <span class="project-open-icon">&rsaquo;</span>
                </a>
            @empty
                <div class="project-empty"><h2>ยังไม่มีโครงการที่ได้รับมอบหมาย</h2><p>ติดต่อ Admin เพื่อกำหนดให้คุณเป็นผู้ดูแลโครงการ</p></div>
            @endforelse
        </div>
    </section>
</x-admin-layout>
