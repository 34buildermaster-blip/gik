<x-admin-layout title="การแจ้งเตือน | 34 Build Master">
    <div class="topbar">
        <div><p class="eyebrow">NOTIFICATIONS</p><h1>การแจ้งเตือน</h1><p class="muted">ติดตามอัปเดตหน้างานล่าสุดจากทีม 34 Build Master</p></div>
        @if($unreadCount>0)<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="button secondary" type="submit">อ่านทั้งหมด</button></form>@endif
    </div>

    <section class="card panel notification-panel">
        <div class="list-toolbar"><div class="filter-tabs"><a class="filter-chip {{ $filter!=='unread'?'is-active':'' }}" href="{{ route('notifications.index') }}">ทั้งหมด</a><a class="filter-chip {{ $filter==='unread'?'is-active':'' }}" href="{{ route('notifications.index',['filter'=>'unread']) }}">ยังไม่อ่าน @if($unreadCount>0)({{ $unreadCount }})@endif</a></div><p class="result-note">{{ $notifications->total() }} รายการ</p></div>
        <div class="notification-list">
            @forelse($notifications as $notification)
                <a class="notification-item {{ $notification->read_at ? '' : 'is-unread' }}" href="{{ route('notifications.open',$notification->id) }}">
                    <span class="notification-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V7l7-4 7 4v14"></path><path d="M9 21v-6h6v6"></path></svg></span>
                    <span class="notification-copy"><span class="notification-meta"><em>{{ isset($notification->data['contact_lead_id']) ? 'CONTACT' : ($notification->data['project_code'] ?? 'PROJECT') }}</em><time>{{ $notification->created_at->locale('th')->diffForHumans() }}</time></span><strong>{{ $notification->data['title'] ?? 'อัปเดตหน้างานใหม่' }}</strong><small>{{ $notification->data['message'] ?? (($notification->data['project_name'] ?? '').' · ความคืบหน้า '.($notification->data['progress_percent'] ?? 0).'%') }}</small></span>
                    @unless($notification->read_at)<i>ใหม่</i>@endunless
                    <span class="notification-arrow">&rsaquo;</span>
                </a>
            @empty
                <div class="client-empty-state compact"><span class="access-denied-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg></span><h2>ยังไม่มีการแจ้งเตือน</h2><p>เมื่อทีมงานเผยแพร่อัปเดตโครงการ รายการใหม่จะปรากฏที่นี่</p></div>
            @endforelse
        </div>
        <div class="pagination">{{ $notifications->links() }}</div>
    </section>
</x-admin-layout>
