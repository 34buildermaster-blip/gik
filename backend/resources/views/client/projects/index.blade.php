<x-admin-layout title="งานของฉัน | 34 Build Master">
    <div class="topbar client-heading">
        <div><p class="eyebrow">MY PROJECTS</p><h1>งานของฉัน</h1><p class="muted">ติดตามสถานะ ความคืบหน้า และรูปอัปเดตล่าสุดจากทีมงาน</p></div>
    </div>

    @if($projects->isEmpty())
        <section class="card client-empty-state"><span class="access-denied-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V7l7-4 7 4v14"></path><path d="M9 21v-6h6v6"></path></svg></span><h2>ยังไม่มีโครงการในบัญชีนี้</h2><p>เมื่อทีมงานมอบหมายโครงการให้คุณ รายละเอียดและการอัปเดตจะปรากฏที่นี่</p></section>
    @else
        <section class="client-project-grid">
            @foreach($projects as $project)
                @php($latestUpdate = $project->updates->first())
                @php($cover = $latestUpdate?->media->first())
                <a class="card client-project-card" href="{{ route('client.projects.show',$project) }}">
                    <div class="client-project-cover">
                        @if($cover)<img src="{{ route('project-media.show',$cover) }}" alt="อัปเดตล่าสุดของ {{ $project->name }}">@else<span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V7l7-4 7 4v14"></path><path d="M9 21v-6h6v6"></path></svg></span>@endif
                        @if($project->unread_updates_count>0)<em>{{ $project->unread_updates_count }} อัปเดตใหม่</em>@endif
                    </div>
                    <div class="client-project-body">
                        <div class="client-project-meta"><span>{{ $project->code }}</span><small>{{ $typeLabels[$project->type] ?? $project->type }}</small></div>
                        <h2>{{ $project->name }}</h2><p>{{ $latestUpdate?->title ?: 'ทีมงานกำลังเตรียมข้อมูลอัปเดต' }}</p>
                        <div class="client-progress-row"><span>{{ $statusLabels[$project->status] ?? $project->status }}</span><strong>{{ $project->progress_percent }}%</strong></div>
                        <div class="progress-track"><i style="width:{{ $project->progress_percent }}%"></i></div>
                        <div class="client-project-footer"><small>{{ $project->published_updates_count }} อัปเดต</small><small>กำหนดส่ง {{ $project->estimated_end_date?->format('d/m/Y') ?: 'ยังไม่ระบุ' }}</small></div>
                    </div>
                </a>
            @endforeach
        </section>
    @endif
</x-admin-layout>
