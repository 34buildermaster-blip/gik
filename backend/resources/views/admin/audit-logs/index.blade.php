<x-admin-layout title="ประวัติกิจกรรม | 34 Build Master Admin">
    <div class="topbar">
        <div><p class="eyebrow">AUDIT LOG</p><h1>ประวัติกิจกรรม</h1><p class="muted">ตรวจสอบว่าใครทำอะไรในระบบและเมื่อใด</p></div>
    </div>

    <section class="card panel launch-panel">
        <form class="launch-toolbar" method="GET">
            <input name="q" type="search" value="{{ $search }}" placeholder="ค้นหากิจกรรม...">
            <button class="button secondary" type="submit">ค้นหา</button>
        </form>
        <div class="launch-list">
            @forelse($logs as $log)
                <article class="launch-list-row">
                    <div><strong>{{ $log->description }}</strong><small>{{ $log->action }} · {{ $log->user?->name ?: 'ระบบ' }}</small></div>
                    <time>{{ $log->created_at->format('d/m/Y H:i:s') }}</time>
                </article>
            @empty
                <div class="project-empty compact"><h2>ยังไม่มีกิจกรรม</h2><p>กิจกรรมสำคัญจะเริ่มปรากฏที่นี่</p></div>
            @endforelse
        </div>
        <div class="pagination">{{ $logs->links() }}</div>
    </section>
</x-admin-layout>
