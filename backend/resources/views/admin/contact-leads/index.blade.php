<x-admin-layout title="ผู้ติดต่อจากเว็บไซต์ | 34 Build Master Admin">
    <div class="topbar">
        <div>
            <p class="eyebrow">Contact Leads</p>
            <h1>ผู้ติดต่อจากเว็บไซต์</h1>
            <p class="muted" style="margin: 7px 0 0;">ติดตามคำขอปรึกษาโครงการ บันทึกการติดต่อ และเก็บรายละเอียดไว้ในพื้นที่เดียว</p>
        </div>
    </div>

    <section class="comment-summary-grid" aria-label="สรุปผู้ติดต่อ">
        @foreach ($statusLabels as $statusKey => $statusLabel)
            <a class="comment-summary-card {{ $status === $statusKey ? 'is-active' : '' }}" href="{{ route('admin.contact-leads.index', ['status' => $statusKey]) }}">
                <span>{{ $statusLabel }}</span>
                <strong>{{ $counts[$statusKey] ?? 0 }}</strong>
            </a>
        @endforeach
    </section>

    <section class="card panel comment-admin-panel">
        <div class="list-toolbar">
            <form method="GET" action="{{ route('admin.contact-leads.index') }}" style="display:flex; gap:10px; flex:1; max-width:560px;">
                @if (array_key_exists($status, $statusLabels))
                    <input name="status" type="hidden" value="{{ $status }}">
                @endif
                <input name="q" type="search" value="{{ $search }}" placeholder="ค้นหาชื่อ เบอร์โทร อีเมล หรือประเภทงาน..." style="flex:1;">
                <button class="button secondary" type="submit">ค้นหา</button>
            </form>
            <p class="result-note">{{ $leads->total() }} รายการ</p>
        </div>

        <div class="comment-admin-list">
            @forelse ($leads as $lead)
                <article class="comment-admin-card">
                    <header class="comment-admin-head">
                        <div>
                            <div class="comment-article-line">
                                <span class="comment-status comment-status--{{ $lead->status }}">{{ $statusLabels[$lead->status] ?? $lead->status }}</span>
                                <span>{{ $lead->service_type ?: 'ยังไม่ระบุประเภทงาน' }}</span>
                            </div>
                            <h2>{{ $lead->name }}</h2>
                            <p class="comment-private-meta">
                                <a href="tel:{{ preg_replace('/\s+/', '', $lead->phone) }}">{{ $lead->phone }}</a>
                                @if ($lead->email)<span aria-hidden="true">·</span><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>@endif
                                <span aria-hidden="true">·</span>{{ $lead->created_at->format('d/m/Y H:i') }} น.
                            </p>
                        </div>
                    </header>

                    @if ($lead->message)
                        <p class="comment-admin-body">{{ $lead->message }}</p>
                    @endif

                    <form class="comment-reply-form" method="POST" action="{{ route('admin.contact-leads.update', $lead) }}">
                        @csrf
                        @method('PUT')
                        <div class="comment-control-row">
                            <label for="lead-status-{{ $lead->id }}">สถานะ</label>
                            <select id="lead-status-{{ $lead->id }}" name="status">
                                @foreach ($statusLabels as $statusKey => $statusLabel)
                                    <option value="{{ $statusKey }}" @selected($lead->status === $statusKey)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label for="lead-note-{{ $lead->id }}">บันทึกภายใน</label>
                        <textarea id="lead-note-{{ $lead->id }}" name="admin_note" maxlength="5000" placeholder="เช่น โทรกลับแล้ว นัดสำรวจพื้นที่ หรือรายละเอียดเพิ่มเติม...">{{ old('admin_note', $lead->admin_note) }}</textarea>
                        <div class="field"><label for="lead-follow-up-{{ $lead->id }}">นัดติดตามครั้งถัดไป</label><input id="lead-follow-up-{{ $lead->id }}" name="next_follow_up_at" type="datetime-local" value="{{ $lead->next_follow_up_at?->format('Y-m-d\TH:i') }}"></div>
                        <div class="comment-control-row">
                            <button class="button" type="submit">บันทึกการติดตาม</button>
                            @if ($lead->assignee)<small>ดูแลล่าสุดโดย {{ $lead->assignee->name }}</small>@endif
                        </div>
                    </form>

                    @if($lead->convertedProject)
                        <div class="lead-converted-note"><strong>สร้างโครงการแล้ว</strong><a href="{{ route('admin.projects.show', $lead->convertedProject) }}">{{ $lead->convertedProject->code }} · {{ $lead->convertedProject->name }} →</a></div>
                    @else
                        <details class="lead-convert-panel">
                            <summary>เปลี่ยนเป็นโครงการ</summary>
                            <form class="launch-form compact" method="POST" action="{{ route('admin.contact-leads.convert', $lead) }}">
                                @csrf
                                <div class="field"><label for="lead-code-{{ $lead->id }}">รหัสโครงการ</label><input id="lead-code-{{ $lead->id }}" name="code" placeholder="BMC-001" required></div>
                                <div class="field"><label for="lead-project-name-{{ $lead->id }}">ชื่อโครงการ</label><input id="lead-project-name-{{ $lead->id }}" name="project_name" value="{{ $lead->name }}" required></div>
                                <div class="field"><label for="lead-type-{{ $lead->id }}">ประเภทงาน</label><select id="lead-type-{{ $lead->id }}" name="type">@foreach($typeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                                <div class="field"><label for="lead-customer-{{ $lead->id }}">บัญชีลูกค้า</label><select id="lead-customer-{{ $lead->id }}" name="customer_id" required><option value="">เลือกบัญชีลูกค้า</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }} · {{ $customer->email }}</option>@endforeach</select></div>
                                <div class="field launch-wide"><label for="lead-address-{{ $lead->id }}">ที่อยู่โครงการ</label><input id="lead-address-{{ $lead->id }}" name="address"></div>
                                <button class="button" type="submit">สร้างโครงการ</button>
                            </form>
                            <small>หากยังไม่มีบัญชีลูกค้า ให้สร้างจากเมนูจัดการผู้ใช้งานก่อน</small>
                        </details>
                    @endif

                    <footer class="comment-admin-footer">
                        <span>{{ $lead->source_url ? 'ที่มา: '.$lead->source_url : 'ส่งจากแบบฟอร์มเว็บไซต์' }}</span>
                        <form method="POST" action="{{ route('admin.contact-leads.destroy', $lead) }}" onsubmit="return confirm('ลบข้อมูลผู้ติดต่อนี้ถาวรใช่ไหม?')">
                            @csrf
                            @method('DELETE')
                            <button class="comment-delete-button" type="submit">ลบรายการ</button>
                        </form>
                    </footer>
                </article>
            @empty
                <div class="comment-empty-state">
                    <strong>ยังไม่มีผู้ติดต่อในรายการนี้</strong>
                    <p>ข้อมูลจากฟอร์มติดต่อบนเว็บไซต์จะปรากฏที่นี่โดยอัตโนมัติ</p>
                </div>
            @endforelse
        </div>

        <div class="pagination">{{ $leads->links() }}</div>
    </section>
</x-admin-layout>
