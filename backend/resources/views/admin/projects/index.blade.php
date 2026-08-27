<x-admin-layout title="โครงการลูกค้า | 34 Build Master Admin">
    @php($isAdmin = auth()->user()->isAdmin())
    <div class="topbar">
        <div><p class="eyebrow">CLIENT PROJECTS</p><h1>{{ $isAdmin ? 'โครงการลูกค้า' : 'โครงการที่ได้รับมอบหมาย' }}</h1><p class="muted" style="margin:7px 0 0;">{{ $isAdmin ? 'จัดการโครงการ มอบหมายลูกค้า และอัปเดตความคืบหน้าหน้างาน' : 'เปิดโครงการเพื่อบันทึกผลตรวจ รูปภาพ และความคืบหน้าหน้างาน' }}</p></div>
        @if($isAdmin)<a class="button" href="{{ route('admin.projects.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>สร้างโครงการ</a>@endif
    </div>

    <section class="project-stats {{ $isAdmin ? 'has-archive' : '' }}">
        <article class="card user-stat-card is-primary"><span>โครงการทั้งหมด</span><strong>{{ $totalProjects }}</strong><small>ทุกสถานะในระบบ</small></article>
        <article class="card user-stat-card"><span>กำลังดำเนินงาน</span><strong>{{ $activeProjects }}</strong><small>โครงการที่กำลังทำงาน</small></article>
        <article class="card user-stat-card"><span>เสร็จสิ้นแล้ว</span><strong>{{ $completedProjects }}</strong><small>ส่งมอบเรียบร้อยแล้ว</small></article>
        @if($isAdmin)
            <article class="card user-stat-card"><span>คลังโครงการ</span><strong>{{ $archivedProjects }}</strong><small>เก็บข้อมูลและไฟล์ไว้ครบ</small></article>
        @endif
    </section>

    <section class="card panel">
        <div class="list-toolbar">
            <div class="filter-tabs">
                @if($isAdmin)
                    <a class="filter-chip {{ $view === 'active' ? 'is-active' : '' }}" href="{{ route('admin.projects.index', array_filter(['q' => $search])) }}">โครงการที่ใช้งาน</a>
                    <a class="filter-chip {{ $view === 'archived' ? 'is-active' : '' }}" href="{{ route('admin.projects.index', array_filter(['q' => $search, 'view' => 'archived'])) }}">คลังโครงการ {{ $archivedProjects }}</a>
                @endif
                <a class="filter-chip {{ ! array_key_exists($status, $statusLabels) ? 'is-active' : '' }}" href="{{ route('admin.projects.index', array_filter(['q' => $search, 'view' => $view === 'archived' ? 'archived' : null])) }}">ทุกสถานะ</a>
                @foreach ($statusLabels as $value => $label)
                    <a class="filter-chip {{ $status === $value ? 'is-active' : '' }}" href="{{ route('admin.projects.index', array_filter(['q' => $search, 'status' => $value, 'view' => $view === 'archived' ? 'archived' : null])) }}">{{ $label }}</a>
                @endforeach
            </div>
            <p class="result-note">@if($search !== '') ผลการค้นหา “{{ $search }}” · @endif {{ $projects->total() }} {{ $view === 'archived' ? 'โครงการในคลัง' : 'โครงการ' }}</p>
        </div>

        <div class="project-card-list">
            @forelse ($projects as $project)
                @if($view === 'archived')
                    <article class="project-list-card is-archived">
                @else
                    <a class="project-list-card" href="{{ route('admin.projects.show', $project) }}">
                @endif
                    <div class="project-list-main">
                        <span class="project-code">{{ $project->code }}</span>
                        <h2>{{ $project->name }}</h2>
                        <p>{{ $project->address ?: 'ยังไม่ได้ระบุที่อยู่หน้างาน' }}</p>
                        <div class="project-customer-row">
                            @foreach ($project->customers->take(3) as $customer)<span>{{ mb_strtoupper(mb_substr($customer->name,0,1)) }}</span>@endforeach
                            <small>{{ $project->customers->pluck('name')->join(', ') ?: 'ยังไม่มีลูกค้า' }}</small>
                        </div>
                    </div>
                    <div class="project-list-progress">
                        <div><span>{{ $statusLabels[$project->status] ?? $project->status }}</span><strong>{{ $project->progress_percent }}%</strong></div>
                        <div class="progress-track"><i style="width: {{ $project->progress_percent }}%"></i></div>
                        <small>{{ $project->updates_count }} อัปเดต · {{ $view === 'archived' ? 'เก็บเมื่อ '.$project->deleted_at?->format('d/m/Y H:i') : 'ผู้ดูแล '.($project->manager?->name ?: 'ยังไม่กำหนด') }}</small>
                    </div>
                    @if($view === 'archived')
                        <form class="project-restore-action" method="POST" action="{{ route('admin.projects.restore', $project->id) }}">
                            @csrf
                            <button class="button secondary" type="submit">กู้คืน</button>
                        </form>
                    @else
                        <span class="project-open-icon">&rsaquo;</span>
                    @endif
                @if($view === 'archived')
                    </article>
                @else
                    </a>
                @endif
            @empty
                <div class="project-empty">
                    <h2>{{ $view === 'archived' ? 'ยังไม่มีโครงการในคลัง' : ($isAdmin ? 'ยังไม่มีโครงการ' : 'ยังไม่มีโครงการที่ได้รับมอบหมาย') }}</h2>
                    <p>{{ $view === 'archived' ? 'โครงการที่เก็บถาวรจะแสดงที่นี่และสามารถกู้คืนได้ทุกเมื่อ' : ($isAdmin ? 'เริ่มสร้างโครงการแรกและมอบหมายลูกค้าเพื่อเปิดการติดตามหน้างาน' : 'ติดต่อ Admin เพื่อมอบหมายโครงการให้บัญชีของคุณ') }}</p>
                    @if($isAdmin && $view !== 'archived')<a class="button" href="{{ route('admin.projects.create') }}">สร้างโครงการ</a>@endif
                </div>
            @endforelse
        </div>
        <div class="pagination">{{ $projects->links() }}</div>
    </section>
</x-admin-layout>
