<x-admin-layout title="งานของฉัน | 34 Build Master">
    @php
        $lineAddFriendUrl = trim((string) config('project_notifications.line_add_friend_url'));
        $lineConnectUrl = $lineAddFriendUrl !== '' ? $lineAddFriendUrl : route('admin.profile.edit').'#line-account';
    @endphp
    <div class="topbar client-heading">
        <div><p class="eyebrow">MY PROJECTS</p><h1>งานของฉัน</h1><p class="muted">ติดตามสถานะ ความคืบหน้า และรูปอัปเดตล่าสุดจากทีมงาน</p></div>
    </div>

    @if(blank(auth()->user()->line_recipient_id))
        <section class="card client-line-onboarding" aria-labelledby="client-line-title">
            <span class="client-line-onboarding-icon" aria-hidden="true"><x-ui-icon name="line" /></span>
            <div class="client-line-onboarding-copy">
                <p class="eyebrow">LINE NOTIFICATION</p>
                <h2 id="client-line-title">รับแจ้งเตือนความคืบหน้าผ่าน LINE</h2>
                <p>เชื่อมเพียงครั้งเดียว แล้วระบบจะแจ้งเมื่อ Admin ตรวจและอนุมัติอัปเดตงานของคุณ</p>
                <ol aria-label="ขั้นตอนเชื่อม LINE">
                    <li>เปิด LINE OA</li>
                    <li>ส่งคำว่า “เชื่อมบัญชี”</li>
                    <li>กดลิงก์ที่ได้รับในแชต</li>
                </ol>
            </div>
            <a class="button client-line-onboarding-action" href="{{ route('admin.profile.edit') }}#line-account">เริ่มเชื่อม LINE</a>
        </section>
    @endif

    @if(session('prompt_line_connect') && blank(auth()->user()->line_recipient_id))
        <dialog class="line-onboarding-dialog" id="line-onboarding-dialog" aria-labelledby="line-onboarding-dialog-title">
            <button class="line-onboarding-dialog-close" type="button" data-line-dialog-close aria-label="ปิด"><x-ui-icon name="x" /></button>
            <span class="line-onboarding-dialog-icon" aria-hidden="true"><x-ui-icon name="line" /></span>
            <p class="eyebrow">ONE MORE STEP</p>
            <h2 id="line-onboarding-dialog-title">เชื่อม LINE เพื่อไม่พลาดอัปเดตบ้าน</h2>
            <p>ระบบจะแจ้งเตือนหลัง Admin ตรวจและอนุมัติความคืบหน้า รูปหน้างาน หรือข้อมูลสำคัญของโครงการแล้ว</p>
            <ol>
                <li><strong>เพิ่มเพื่อน LINE OA</strong><span>เปิดบัญชีทางการของ 34 Build Master</span></li>
                <li><strong>เชื่อมบัญชีครั้งเดียว</strong><span>กดลิงก์ที่ระบบส่งให้ในแชต LINE</span></li>
                <li><strong>รับแจ้งเตือนอัตโนมัติ</strong><span>ไม่ต้องกลับมากรอก LINE ID เอง</span></li>
            </ol>
            <div class="line-onboarding-dialog-actions">
                <a class="button" href="{{ $lineConnectUrl }}" @if($lineAddFriendUrl !== '') target="_blank" rel="noopener noreferrer" @endif>
                    <x-ui-icon name="line" /> เชื่อม LINE ตอนนี้
                </a>
                <button class="button secondary" type="button" data-line-dialog-close>ไว้ภายหลัง</button>
            </div>
        </dialog>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dialog = document.getElementById('line-onboarding-dialog');
                if (!dialog || typeof dialog.showModal !== 'function') return;
                dialog.showModal();
                dialog.querySelectorAll('[data-line-dialog-close]').forEach(function (button) {
                    button.addEventListener('click', function () { dialog.close(); });
                });
                dialog.addEventListener('click', function (event) {
                    if (event.target === dialog) dialog.close();
                });
            });
        </script>
    @endif

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
