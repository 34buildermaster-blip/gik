<x-admin-layout title="{{ $customer->name }} | ข้อมูลลูกค้า">
    <div class="topbar customer-detail-heading">
        <div>
            <p class="eyebrow">CUSTOMER PROFILE</p>
            <h1>{{ $customer->name }}</h1>
            <p class="muted" style="margin:7px 0 0;">ภาพรวมข้อมูลลูกค้า บัญชี และโครงการที่ได้รับสิทธิ์ติดตาม</p>
        </div>
        <div class="customer-heading-actions">
            <a class="button secondary" href="{{ route('admin.customers.index') }}">กลับหน้ารวม</a>
            <a class="button secondary" href="{{ route('admin.users.security.show', $customer) }}">ความปลอดภัยบัญชี</a>
            <a class="button" href="{{ route('admin.customers.edit', $customer) }}">แก้ไขข้อมูล</a>
        </div>
    </div>

    <section class="customer-summary-grid">
        <article class="card customer-profile-summary">
            <span class="customer-profile-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
            <div>
                <span class="customer-status status-{{ $customer->customer_status }}">{{ $statusLabels[$customer->customer_status] ?? $customer->customer_status }}</span>
                <h2>{{ $customer->name }}</h2>
                <p>{{ '@'.$customer->username }} · สมาชิกตั้งแต่ {{ $customer->created_at->format('d/m/Y') }}</p>
            </div>
        </article>
        <article class="card customer-summary-stat"><span>โครงการ</span><strong>{{ $customer->allProjects->count() }}</strong><small>{{ $customer->allProjects->whereNull('deleted_at')->where('status', 'in_progress')->count() }} โครงการกำลังดำเนินงาน</small></article>
        <article class="card customer-summary-stat"><span>เอกสาร</span><strong>{{ $documentCount }}</strong><small>ทุกระดับสิทธิ์ในโครงการ</small></article>
        <article class="card customer-summary-stat"><span>2FA</span><strong class="customer-security-value {{ $customer->hasTwoFactorAuthenticationEnabled() ? 'is-safe' : '' }}">{{ $customer->hasTwoFactorAuthenticationEnabled() ? 'เปิด' : 'ปิด' }}</strong><small>{{ $customer->isLoginLocked() ? 'บัญชีถูกล็อกชั่วคราว' : 'บัญชีเข้าใช้งานได้' }}</small></article>
    </section>

    <section class="customer-info-layout">
        <article class="card panel customer-info-card">
            <div class="panel-heading"><div><p class="eyebrow">CONTACT</p><h2>ข้อมูลติดต่อ</h2></div></div>
            <dl class="customer-info-list">
                <div><dt>อีเมล</dt><dd><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></dd></div>
                <div><dt>โทรศัพท์</dt><dd>{{ $customer->phone ?: '-' }}</dd></div>
                <div><dt>ช่องทางที่สะดวก</dt><dd>{{ $contactChannelLabels[$customer->preferred_contact_channel] ?? '-' }}</dd></div>
                <div><dt>LINE Recipient ID</dt><dd>{{ $customer->line_recipient_id ?: '-' }}</dd></div>
                <div class="full"><dt>ที่อยู่</dt><dd>{{ $customer->address ?: '-' }}</dd></div>
            </dl>
        </article>

        <article class="card panel customer-info-card">
            <div class="panel-heading"><div><p class="eyebrow">BILLING & EMERGENCY</p><h2>ออกเอกสารและผู้ติดต่อสำรอง</h2></div></div>
            <dl class="customer-info-list">
                <div><dt>ชื่อสำหรับออกเอกสาร</dt><dd>{{ $customer->billing_name ?: '-' }}</dd></div>
                <div><dt>เลขประจำตัวผู้เสียภาษี</dt><dd>{{ $customer->maskedTaxId() ?: '-' }}</dd></div>
                <div><dt>ผู้ติดต่อสำรอง</dt><dd>{{ $customer->emergency_contact_name ?: '-' }}</dd></div>
                <div><dt>เบอร์ผู้ติดต่อสำรอง</dt><dd>{{ $customer->emergency_contact_phone ?: '-' }}</dd></div>
            </dl>
        </article>
    </section>

    <section class="card panel customer-projects-panel">
        <div class="panel-heading">
            <div><p class="eyebrow">PROJECTS</p><h2>โครงการของลูกค้า</h2><p>รวมโครงการปัจจุบันและโครงการที่จัดเก็บเข้าคลัง</p></div>
            <span class="client-update-count">{{ $customer->allProjects->count() }} โครงการ</span>
        </div>
        <div class="customer-project-list">
            @forelse($customer->allProjects as $project)
                @php($latestUpdate = $latestUpdates->get($project->id))
                <a class="customer-project-row {{ $project->trashed() ? 'is-archived' : '' }}" href="{{ $project->trashed() ? route('admin.projects.index', ['view' => 'archived', 'q' => $project->code]) : route('admin.projects.show', $project) }}">
                    <span class="customer-project-main">
                        <small>{{ $project->code }} · {{ $projectTypeLabels[$project->type] ?? $project->type }}</small>
                        <strong>{{ $project->name }}</strong>
                        <span>{{ $project->manager?->name ? 'ดูแลโดย '.$project->manager->name : 'ยังไม่กำหนดผู้ดูแล' }}</span>
                    </span>
                    <span class="customer-project-progress">
                        <span><small>{{ $project->trashed() ? 'จัดเก็บแล้ว' : ($projectStatusLabels[$project->status] ?? $project->status) }}</small><strong>{{ $project->progress_percent }}%</strong></span>
                        <i><b style="width:{{ $project->progress_percent }}%"></b></i>
                    </span>
                    <span class="customer-project-activity">
                        <small>อัปเดตล่าสุด</small>
                        <strong>{{ $latestUpdate?->title ?: 'ยังไม่มีอัปเดตที่เผยแพร่' }}</strong>
                        <span>{{ $latestUpdate?->work_performed_at?->format('d/m/Y H:i') ?: '-' }} · {{ $project->documents_count }} เอกสาร</span>
                    </span>
                    <span class="customer-open-icon" aria-hidden="true">›</span>
                </a>
            @empty
                <div class="customer-empty-state"><h2>ยังไม่มีโครงการ</h2><p>สร้างโครงการใหม่แล้วเลือกบัญชีลูกค้านี้เพื่อเปิดพื้นที่ติดตามงาน</p><a class="button" href="{{ route('admin.projects.create') }}">สร้างโครงการ</a></div>
            @endforelse
        </div>
    </section>

    <section class="customer-info-layout customer-bottom-layout">
        <article class="card panel customer-info-card">
            <div class="panel-heading"><div><p class="eyebrow">INTERNAL NOTE</p><h2>หมายเหตุภายใน</h2><p>แสดงเฉพาะ Admin และไม่ส่งให้ลูกค้า</p></div></div>
            <p class="customer-internal-note">{{ $customer->internal_notes ?: 'ยังไม่มีหมายเหตุภายใน' }}</p>
        </article>
        <article class="card panel customer-info-card">
            <div class="panel-heading"><div><p class="eyebrow">CONSENT</p><h2>การยินยอมและบัญชี</h2></div></div>
            <dl class="customer-info-list">
                <div><dt>ข้อกำหนดการใช้งาน</dt><dd>{{ $customer->terms_accepted_at?->format('d/m/Y H:i') ?: 'ยังไม่ยอมรับ' }}</dd></div>
                <div><dt>นโยบายความเป็นส่วนตัว</dt><dd>{{ $customer->privacy_accepted_at?->format('d/m/Y H:i') ?: 'ยังไม่ยอมรับ' }}</dd></div>
                <div><dt>รับข้อมูลการตลาด</dt><dd>{{ $customer->marketing_consent_at ? 'ยินยอม' : 'ไม่ยินยอม' }}</dd></div>
                <div><dt>เวอร์ชัน Policy</dt><dd>{{ $customer->policy_version ?: '-' }}</dd></div>
            </dl>
        </article>
    </section>
</x-admin-layout>
