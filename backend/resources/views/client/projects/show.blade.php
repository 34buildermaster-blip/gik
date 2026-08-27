<x-admin-layout title="{{ $project->name }} | 34 Build Master">
    <div class="topbar project-detail-heading"><div><p class="eyebrow">{{ $project->code }}</p><h1>{{ $project->name }}</h1><p class="muted">{{ $typeLabels[$project->type] ?? $project->type }} · อัปเดตจากทีมงาน 34 Build Master</p></div><a class="button secondary" href="{{ route('client.projects.index') }}">กลับไปงานของฉัน</a></div>

    <section class="client-project-hero card">
        <div class="client-progress-summary"><div class="project-progress-ring" style="--progress:{{ $project->progress_percent }}"><strong>{{ $project->progress_percent }}%</strong></div><div><span class="project-status-label">{{ $statusLabels[$project->status] ?? $project->status }}</span><h2>ความคืบหน้าปัจจุบัน</h2><p>{{ $project->summary ?: 'ทีมงานกำลังดำเนินงานตามแผนที่กำหนด' }}</p></div></div>
        <div class="client-project-dates"><div><span>เริ่มงาน</span><strong>{{ $project->start_date?->format('d/m/Y') ?: '-' }}</strong></div><div><span>กำหนดส่ง</span><strong>{{ $project->estimated_end_date?->format('d/m/Y') ?: '-' }}</strong></div><div><span>ผู้ดูแลโครงการ</span><strong>{{ $project->manager?->name ?: 'ทีม 34 Build Master' }}</strong></div></div>
    </section>

    @php($hasConfiguredSteps = $project->steps->isNotEmpty() && (int) $project->steps->sum('weight_percent') === 100)

    @if($hasConfiguredSteps)
        <div class="client-workspace-tabs" role="tablist" aria-label="ข้อมูลความคืบหน้าโครงการ">
            <button type="button" role="tab" aria-selected="false" aria-controls="client-project-steps" data-client-workspace-tab="steps">
                ขั้นตอนงาน
                <span>{{ $project->steps->count() }}</span>
            </button>
            <button type="button" class="is-active" role="tab" aria-selected="true" aria-controls="client-project-updates" data-client-workspace-tab="updates">
                อัปเดตหน้างาน
                <span>{{ $project->updates->count() }}</span>
            </button>
        </div>
    @endif

    <div class="client-project-workspace {{ $hasConfiguredSteps ? '' : 'is-single-column' }}">
    @if($hasConfiguredSteps)
        <section class="card panel client-steps-panel client-workspace-panel" id="client-project-steps" data-client-workspace-panel="steps">
            <div class="panel-heading"><div><p class="eyebrow">PROJECT STEPS</p><h2>ความคืบหน้าแต่ละขั้นตอน</h2><p>ภาพรวมคำนวณตามน้ำหนักของงานที่กำหนดไว้ในแผนโครงการ</p></div><strong class="client-step-total">{{ $project->progress_percent }}%</strong></div>
            <div class="client-step-list">
                @foreach($project->steps as $step)
                    <article class="client-step-row {{ $step->status === 'needs_attention' ? 'needs-attention' : '' }}">
                        <div class="client-step-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="client-step-content">
                            <div class="client-step-title"><div><span>{{ $stepStatusLabels[$step->status] ?? $step->status }}</span><h3>{{ $step->name }}</h3></div><strong>{{ $step->progress_percent }}%</strong></div>
                            <div class="project-step-progress"><i style="width: {{ $step->progress_percent }}%"></i></div>
                            <div class="client-step-meta"><span>น้ำหนัก {{ $step->weight_percent }}% ของโครงการ</span><span>สร้างความคืบหน้ารวม {{ number_format($step->contributionPercent(), 1) }}%</span></div>
                            <div class="launch-step-schedule status-{{ $step->scheduleStatus() }}"><strong>{{ ['upcoming' => 'ยังไม่เริ่ม', 'in_progress' => 'ตามแผน', 'overdue' => 'เกินกำหนด', 'completed' => 'เสร็จแล้ว'][$step->scheduleStatus()] }}</strong><span>{{ $step->planned_start_date?->format('d/m/Y') ?: '-' }} ถึง {{ $step->planned_end_date?->format('d/m/Y') ?: '-' }}</span></div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="card panel client-timeline-panel client-workspace-panel is-active" id="client-project-updates" data-client-workspace-panel="updates">
        <div class="panel-heading"><div><p class="eyebrow">PROJECT TIMELINE</p><h2>อัปเดตหน้างาน</h2><p>รูปและรายละเอียดตามวันที่ดำเนินงานจริง</p></div><span class="client-update-count">{{ $project->updates->count() }} อัปเดต</span></div>
        <div class="project-timeline">
            @forelse($project->updates as $updateItem)
                <article class="timeline-entry">
                    <div class="timeline-marker"><span></span></div>
                    <div class="timeline-content client-update-card">
                        <button
                            type="button"
                            class="client-update-card-trigger"
                            data-update-dialog="client-update-{{ $updateItem->id }}"
                            aria-label="ดูรายละเอียดอัปเดต {{ $updateItem->title }}"
                        ></button>
                        <div class="timeline-meta"><span>{{ $stageLabels[$updateItem->stage] ?? $updateItem->stage }}</span><time>{{ $updateItem->work_performed_at->locale('th')->translatedFormat('d M Y · H:i น.') }}</time></div>
                        <div class="timeline-title-row"><div><h3>{{ $updateItem->title }}</h3><p>{{ $updateItem->description }}</p>@if($updateItem->projectStep)<small class="timeline-step-label">{{ $updateItem->projectStep->name }}</small>@endif</div><strong>{{ $updateItem->progress_percent }}%<small>{{ $updateItem->projectStep ? 'ของขั้นตอน' : 'ของโครงการ' }}</small></strong></div>
                        <div class="client-update-card-footer">
                            <small>อัปเดตโดย {{ $updateItem->creator?->name ?: 'ทีมงาน 34 Build Master' }}</small>
                            <span>ดูรูปและรายละเอียด <b aria-hidden="true">&rarr;</b></span>
                        </div>
                    </div>
                </article>

                <dialog class="client-update-dialog" id="client-update-{{ $updateItem->id }}" aria-labelledby="client-update-title-{{ $updateItem->id }}">
                    <div class="client-update-dialog-shell">
                        <header class="client-update-dialog-header">
                            <div>
                                <span>{{ $stageLabels[$updateItem->stage] ?? $updateItem->stage }}</span>
                                <time>{{ $updateItem->work_performed_at->locale('th')->translatedFormat('d M Y · H:i น.') }}</time>
                            </div>
                            <button type="button" data-close-update-dialog aria-label="ปิดรายละเอียด">&times;</button>
                        </header>

                        <div class="client-update-dialog-body">
                            <div class="client-update-dialog-title">
                                <div><p class="eyebrow">SITE UPDATE</p><h2 id="client-update-title-{{ $updateItem->id }}">{{ $updateItem->title }}</h2></div>
                                <strong>{{ $updateItem->progress_percent }}%</strong>
                            </div>
                            <p class="client-update-description">{{ $updateItem->description }}</p>
                            @if($updateItem->inspection_result || $updateItem->progress_reason)
                                <div class="timeline-inspection-summary">
                                    @if($updateItem->inspection_result)<span>{{ $inspectionLabels[$updateItem->inspection_result] ?? $updateItem->inspection_result }}</span>@endif
                                    @if($updateItem->progress_reason)<p>{{ $updateItem->progress_reason }}</p>@endif
                                </div>
                            @endif

                            <div class="client-update-facts">
                                <div><span>{{ $updateItem->projectStep ? 'ความคืบหน้าขั้นตอน' : 'ความคืบหน้าโครงการ' }}</span><strong>{{ $updateItem->progress_percent }}%</strong></div>
                                @if($updateItem->projectStep)<div><span>ขั้นตอนงาน</span><strong>{{ $updateItem->projectStep->name }}</strong></div>@endif
                                <div><span>วันที่ดำเนินงาน</span><strong>{{ $updateItem->work_performed_at->locale('th')->translatedFormat('d M Y') }}</strong></div>
                                <div><span>อัปเดตโดย</span><strong>{{ $updateItem->creator?->name ?: 'ทีมงาน 34 Build Master' }}</strong></div>
                            </div>

                            <section class="client-update-media-section">
                                <div class="client-update-media-heading"><h3>รูปภาพหน้างาน</h3><span>{{ $updateItem->media->count() }} รูป</span></div>
                                @if($updateItem->media->isNotEmpty())
                                    <div class="client-update-dialog-gallery">
                                        @foreach($updateItem->media as $media)
                                            <a href="{{ route('project-media.show',$media) }}" target="_blank" rel="noopener">
                                                <img src="{{ route('project-media.show',$media) }}" alt="{{ $media->original_name }}" loading="lazy">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="client-update-no-media"><strong>ยังไม่มีรูปภาพในอัปเดตนี้</strong><p>รายละเอียดการดำเนินงานยังสามารถตรวจสอบได้จากข้อความด้านบน</p></div>
                                @endif
                            </section>
                        </div>

                        <footer class="client-update-dialog-footer">
                            <span>เผยแพร่ {{ $updateItem->published_at?->locale('th')->diffForHumans() }}</span>
                            <button type="button" class="button secondary" data-close-update-dialog>ปิดหน้าต่าง</button>
                        </footer>
                    </div>
                </dialog>
            @empty
                <div class="client-empty-state compact"><h2>ยังไม่มีอัปเดตที่เผยแพร่</h2><p>ทีมงานจะแจ้งรายละเอียดหน้างานผ่านหน้านี้เมื่อพร้อมครับ</p></div>
            @endforelse
        </div>
    </section>
    </div>

    <div class="client-support-grid">
        <section class="card panel launch-panel">
            <div class="panel-heading"><div><p class="eyebrow">DOCUMENTS</p><h2>เอกสารของโครงการ</h2><p>เอกสารฉบับที่ทีมงานเปิดให้ลูกค้าตรวจสอบ</p></div><span class="client-update-count">{{ $project->documents->count() }} ไฟล์</span></div>
            <div class="launch-list">
                @forelse($project->documents as $document)
                    <a class="launch-list-row" href="{{ route('project-documents.show', $document) }}" target="_blank">
                        <div><strong>{{ $document->title }}</strong><small>{{ $documentCategoryLabels[$document->category] }} · v{{ $document->version }}</small></div><span>เปิดไฟล์ →</span>
                    </a>
                @empty
                    <div class="client-empty-state compact"><h2>ยังไม่มีเอกสาร</h2><p>เอกสารที่พร้อมเผยแพร่จะปรากฏในส่วนนี้</p></div>
                @endforelse
            </div>
        </section>

        <section class="card panel launch-panel">
            <div class="panel-heading"><div><p class="eyebrow">SITE ISSUES</p><h2>รายการตรวจและงานแก้ไข</h2><p>ติดตามปัญหาที่กำลังดำเนินการและรายการที่ตรวจรับแล้ว</p></div><span class="client-update-count">{{ $project->issues->count() }} รายการ</span></div>
            <div class="launch-issue-grid">
                @forelse($project->issues as $issue)
                    <article class="launch-issue-card priority-{{ $issue->priority }}">
                        <div class="launch-issue-head"><div><span>{{ $issuePriorityLabels[$issue->priority] }}</span><h3>{{ $issue->title }}</h3></div><strong>{{ $issueStatusLabels[$issue->status] }}</strong></div>
                        <p>{{ $issue->description ?: 'ทีมงานกำลังติดตามรายการนี้' }}</p>
                        @if($issue->media->isNotEmpty())<div class="launch-media">@foreach($issue->media as $media)<a href="{{ route('project-issue-media.show', $media) }}" target="_blank"><img src="{{ route('project-issue-media.show', $media) }}" alt=""></a>@endforeach</div>@endif
                        <small>ผู้รับผิดชอบ: {{ $issue->assignee?->name ?: 'ทีม 34 Build Master' }} · กำหนด {{ $issue->due_date?->format('d/m/Y') ?: '-' }}</small>
                    </article>
                @empty
                    <div class="client-empty-state compact"><h2>ไม่มีรายการแก้ไขค้าง</h2><p>รายการที่ทีมงานแจ้งให้ติดตามจะปรากฏในส่วนนี้</p></div>
                @endforelse
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const workspaceTabs = Array.from(document.querySelectorAll('[data-client-workspace-tab]'));
            const workspacePanels = Array.from(document.querySelectorAll('[data-client-workspace-panel]'));

            workspaceTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const target = tab.dataset.clientWorkspaceTab;

                    workspaceTabs.forEach((item) => {
                        const isActive = item === tab;
                        item.classList.toggle('is-active', isActive);
                        item.setAttribute('aria-selected', String(isActive));
                    });

                    workspacePanels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel.dataset.clientWorkspacePanel === target);
                    });
                });
            });

            document.querySelectorAll('[data-update-dialog]').forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    const dialog = document.getElementById(trigger.dataset.updateDialog);
                    if (dialog?.showModal) dialog.showModal();
                });
            });

            document.querySelectorAll('.client-update-dialog').forEach((dialog) => {
                dialog.querySelectorAll('[data-close-update-dialog]').forEach((button) => {
                    button.addEventListener('click', () => dialog.close());
                });

                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) dialog.close();
                });
            });
        });
    </script>
</x-admin-layout>
