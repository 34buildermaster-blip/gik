<x-admin-layout title="ความคิดเห็นบทความ | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Article Comments</p>
            <h1>จัดการความคิดเห็น</h1>
            <p class="muted" style="margin: 7px 0 0;">ตรวจสอบข้อความก่อนเผยแพร่ ตอบกลับในนามทีมงาน และซ่อนข้อความที่ไม่เหมาะสม</p>
        </div>
    </div>

    <section class="comment-summary-grid" aria-label="สรุปความคิดเห็น">
        @foreach ($statusLabels as $statusKey => $statusLabel)
            <a class="comment-summary-card {{ $status === $statusKey ? 'is-active' : '' }}" href="{{ route('admin.comments.index', ['status' => $statusKey]) }}">
                <span>{{ $statusLabel }}</span>
                <strong>{{ $counts[$statusKey] ?? 0 }}</strong>
            </a>
        @endforeach
    </section>

    <section class="card panel comment-admin-panel">
        <div class="list-toolbar">
            <div class="filter-tabs" aria-label="กรองสถานะความคิดเห็น">
                <a class="filter-chip {{ ! array_key_exists($status, $statusLabels) ? 'is-active' : '' }}" href="{{ route('admin.comments.index', array_filter(['q' => $search])) }}">ทั้งหมด</a>
                @foreach ($statusLabels as $statusKey => $statusLabel)
                    <a class="filter-chip {{ $status === $statusKey ? 'is-active' : '' }}" href="{{ route('admin.comments.index', array_filter(['q' => $search, 'status' => $statusKey])) }}">{{ $statusLabel }}</a>
                @endforeach
            </div>
            <p class="result-note">
                @if ($search !== '')
                    ผลการค้นหา “{{ $search }}” ·
                @endif
                {{ $comments->total() }} รายการ
            </p>
        </div>

        <div class="comment-admin-list">
            @forelse ($comments as $comment)
                <article class="comment-admin-card">
                    <header class="comment-admin-head">
                        <div>
                            <div class="comment-article-line">
                                <span class="comment-status comment-status--{{ $comment->status }}">{{ $statusLabels[$comment->status] ?? $comment->status }}</span>
                                <a href="{{ rtrim(config('app.frontend_url'), '/') }}/blog/{{ $comment->article_slug }}" target="_blank" rel="noreferrer">
                                    {{ $comment->article_title ?: $comment->article_slug }}
                                </a>
                            </div>
                            <h2>{{ $comment->author_name }}</h2>
                            <p class="comment-private-meta">
                                {{ $comment->author_email ?: 'ไม่ได้ระบุอีเมล' }}
                                <span aria-hidden="true">·</span>
                                {{ $comment->created_at->format('d/m/Y H:i') }} น.
                            </p>
                        </div>
                    </header>

                    <p class="comment-admin-body">{{ $comment->body }}</p>

                    <div class="comment-admin-actions">
                        <form class="comment-status-form" method="POST" action="{{ route('admin.comments.status', $comment) }}">
                            @csrf
                            @method('PUT')
                            <label for="comment-status-{{ $comment->id }}">สถานะการแสดงผล</label>
                            <div class="comment-control-row">
                                <select id="comment-status-{{ $comment->id }}" name="status">
                                    @foreach ($statusLabels as $statusKey => $statusLabel)
                                        <option value="{{ $statusKey }}" @selected($comment->status === $statusKey)>{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                                <button class="button" type="submit">บันทึกสถานะ</button>
                            </div>
                            @if ($comment->moderator)
                                <small>ตรวจล่าสุดโดย {{ $comment->moderator->name }}</small>
                            @endif
                        </form>

                        <form class="comment-reply-form" method="POST" action="{{ route('admin.comments.reply', $comment) }}">
                            @csrf
                            @method('PUT')
                            <label for="comment-reply-{{ $comment->id }}">คำตอบจากทีมงาน</label>
                            <textarea id="comment-reply-{{ $comment->id }}" name="admin_reply" maxlength="2000" placeholder="เขียนคำตอบที่จะแสดงใต้ความคิดเห็น...">{{ old('admin_reply', $comment->admin_reply) }}</textarea>
                            <div class="comment-control-row">
                                <button class="button secondary" type="submit">{{ $comment->admin_reply ? 'แก้ไขคำตอบ' : 'ส่งคำตอบ' }}</button>
                                @if ($comment->admin_reply)
                                    <small>ตอบโดย {{ $comment->replier?->name ?: 'ทีมงาน' }}</small>
                                @endif
                            </div>
                        </form>
                    </div>

                    <footer class="comment-admin-footer">
                        <span>Slug: {{ $comment->article_slug }}</span>
                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('ลบความคิดเห็นนี้ถาวรใช่ไหม?')">
                            @csrf
                            @method('DELETE')
                            <button class="comment-delete-button" type="submit">ลบความคิดเห็น</button>
                        </form>
                    </footer>
                </article>
            @empty
                <div class="comment-empty-state">
                    <strong>ยังไม่มีความคิดเห็นในรายการนี้</strong>
                    <p>เมื่อผู้อ่านส่งความคิดเห็น รายการที่รอตรวจสอบจะปรากฏที่นี่</p>
                </div>
            @endforelse
        </div>

        <div class="pagination">
            {{ $comments->links() }}
        </div>
    </section>
</x-admin-layout>
