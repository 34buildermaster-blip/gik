<x-admin-layout title="{{ $project->name }} | 34 Build Master Admin">
    @php($isAdmin = auth()->user()->isAdmin())
    <div class="topbar project-detail-heading">
        <div><p class="eyebrow">{{ $project->code }}</p><h1>{{ $project->name }}</h1><p class="muted">{{ $typeLabels[$project->type] ?? $project->type }} · {{ $project->address ?: 'ยังไม่ได้ระบุที่อยู่' }}</p></div>
        <div class="actions">
            @if($isAdmin)
                <a class="button secondary" href="{{ route('admin.projects.edit', $project) }}">แก้ไขโครงการ</a>
                <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('เก็บโครงการนี้เข้าคลังใช่ไหม? ข้อมูล Timeline และไฟล์ใน Google Drive จะยังอยู่ครบ')">
                    @csrf
                    @method('DELETE')
                    <button class="button archive-button" type="submit">เก็บเข้าคลัง</button>
                </form>
            @endif
            <a class="button" href="{{ route('admin.project-updates.create',$project) }}">เพิ่มอัปเดต</a>
        </div>
    </div>

    <section class="project-overview-grid">
        <article class="card project-progress-card">
            <div class="project-progress-ring" style="--progress: {{ $project->progress_percent }}"><strong>{{ $project->progress_percent }}%</strong></div>
            <div><span class="project-status-label">{{ $statusLabels[$project->status] ?? $project->status }}</span><h2>ความคืบหน้ารวม</h2><p>{{ $project->summary ?: 'ยังไม่มีคำอธิบายโครงการ' }}</p></div>
        </article>
        <article class="card project-facts">
            <div><span>วันที่เริ่มงาน</span><strong>{{ $project->start_date?->format('d/m/Y') ?: '-' }}</strong></div>
            <div><span>กำหนดส่ง</span><strong>{{ $project->estimated_end_date?->format('d/m/Y') ?: '-' }}</strong></div>
            <div><span>ผู้ดูแล</span><strong>{{ $project->manager?->name ?: '-' }}</strong></div>
            <div><span>จำนวนอัปเดต</span><strong>{{ $project->updates->count() }}</strong></div>
        </article>
    </section>

    <section id="project-steps" class="card panel project-steps-panel">
        <div class="panel-heading project-steps-heading">
            <div><p class="eyebrow">WEIGHTED PROGRESS</p><h2>ขั้นตอนและน้ำหนักงาน</h2><p>เปอร์เซ็นต์รวมคำนวณจากน้ำหนัก × ความสำเร็จของแต่ละขั้น</p></div>
            <div class="step-weight-summary {{ $isStepPlanReady ? 'is-ready' : '' }}">
                <span>น้ำหนักที่กำหนด</span><strong>{{ $stepWeightTotal }}<small>/100%</small></strong>
            </div>
        </div>

        @if(! $isStepPlanReady)
            <div class="step-plan-notice"><strong>ตั้งค่าแผนงานให้ครบ 100%</strong><span>เหลืออีก {{ 100 - $stepWeightTotal }}% จึงจะเริ่มอัปเดตและคำนวณความคืบหน้าอัตโนมัติ</span></div>
        @endif

        @if($isAdmin)
        <form class="project-step-create" method="POST" action="{{ route('admin.project-steps.store', $project) }}">
            @csrf
            <div class="field"><label for="step_name">ชื่อขั้นตอน</label><input id="step_name" name="name" value="{{ old('name') }}" placeholder="เช่น งานฐานราก" required></div>
            <div class="field"><label for="step_weight">น้ำหนัก (%)</label><input id="step_weight" name="weight_percent" type="number" min="1" max="{{ max(1, 100 - $stepWeightTotal) }}" value="{{ old('weight_percent') }}" placeholder="{{ max(0, 100 - $stepWeightTotal) }}" required @disabled($stepWeightTotal >= 100)></div>
            <div class="field step-description-field"><label for="step_description">รายละเอียด</label><input id="step_description" name="description" value="{{ old('description') }}" placeholder="ขอบเขตงานหรือเงื่อนไขการตรวจรับ"></div>
            <div class="field"><label for="step_start">แผนเริ่ม</label><input id="step_start" name="planned_start_date" type="date" value="{{ old('planned_start_date') }}"></div>
            <div class="field"><label for="step_end">แผนเสร็จ</label><input id="step_end" name="planned_end_date" type="date" value="{{ old('planned_end_date') }}"></div>
            <button class="button" type="submit" @disabled($stepWeightTotal >= 100)>เพิ่มขั้นตอน</button>
        </form>
        @endif

        <div class="project-step-list">
            @forelse($project->steps as $step)
                @php($latestLog = $step->progressLogs->first())
                <article class="project-step-card {{ $step->status === 'needs_attention' ? 'needs-attention' : '' }}">
                    <div class="project-step-card-head">
                        <div class="project-step-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="project-step-title"><span>{{ $stepStatusLabels[$step->status] ?? $step->status }}</span><h3>{{ $step->name }}</h3><p>{{ $step->description ?: 'ยังไม่ได้ระบุรายละเอียดขั้นตอน' }}</p></div>
                        <div class="project-step-metrics"><div><small>น้ำหนัก</small><strong>{{ $step->weight_percent }}%</strong></div><div><small>ทำเสร็จ</small><strong>{{ $step->progress_percent }}%</strong></div><div><small>คิดเป็นงานรวม</small><strong>{{ number_format($step->contributionPercent(), 1) }}%</strong></div></div>
                    </div>

                    <div class="project-step-progress"><i style="width: {{ $step->progress_percent }}%"></i></div>
                    <div class="launch-step-schedule status-{{ $step->scheduleStatus() }}">
                        <strong>{{ ['upcoming' => 'ยังไม่เริ่ม', 'in_progress' => 'ตามแผน', 'overdue' => 'เกินกำหนด', 'completed' => 'เสร็จแล้ว'][$step->scheduleStatus()] }}</strong>
                        <span>{{ $step->planned_start_date?->format('d/m/Y') ?: '-' }} ถึง {{ $step->planned_end_date?->format('d/m/Y') ?: '-' }}</span>
                    </div>

                    @if($isAdmin)
                        <form class="step-progress-form" method="POST" action="{{ route('admin.project-steps.progress', [$project, $step]) }}">
                            @csrf @method('PUT')
                            <div class="field"><label for="step_progress_{{ $step->id }}">ปรับเปอร์เซ็นต์โดย Admin</label><input id="step_progress_{{ $step->id }}" name="progress_percent" type="number" min="0" max="100" value="{{ $step->progress_percent }}" required @disabled(! $isStepPlanReady)></div>
                            <div class="field"><label for="inspection_{{ $step->id }}">ผลการตรวจ</label><select id="inspection_{{ $step->id }}" name="inspection_result" required @disabled(! $isStepPlanReady)>@foreach($inspectionLabels as $value => $label)<option value="{{ $value }}" @selected(($latestLog?->inspection_result ?? 'not_checked') === $value)>{{ $label }}</option>@endforeach</select></div>
                            <div class="field step-reason-field"><label for="reason_{{ $step->id }}">เหตุผล / หมายเหตุ</label><input id="reason_{{ $step->id }}" name="reason" placeholder="จำเป็นเมื่อเปอร์เซ็นต์ลดลงหรืองานไม่ผ่าน"></div>
                            <button class="button" type="submit" @disabled(! $isStepPlanReady)>บันทึกโดยตรง</button>
                        </form>
                    @else
                        <div class="step-review-hint">ต้องการเสนอเปอร์เซ็นต์ใหม่ ให้เพิ่มอัปเดตหน้างานและส่งให้ Admin ตรวจ</div>
                    @endif

                    @if($step->progressLogs->isNotEmpty())
                        <div class="step-history">
                            <strong>ประวัติล่าสุด</strong>
                            @foreach($step->progressLogs->take(3) as $log)
                                <div><span class="step-history-change {{ $log->new_progress < $log->previous_progress ? 'is-decrease' : '' }}">{{ $log->previous_progress }}% → {{ $log->new_progress }}%</span><span>{{ $inspectionLabels[$log->inspection_result] ?? $log->inspection_result }}</span><span>{{ $log->reason ?: 'ไม่มีหมายเหตุ' }}</span><time>{{ $log->created_at->format('d/m/Y H:i') }}</time></div>
                            @endforeach
                        </div>
                    @endif

                    @if($isAdmin)
                    <details class="step-settings">
                        <summary>แก้ไขข้อมูลขั้นตอน</summary>
                        <form class="step-settings-form" method="POST" action="{{ route('admin.project-steps.update', [$project, $step]) }}">
                            @csrf @method('PUT')
                            <div class="field"><label for="edit_name_{{ $step->id }}">ชื่อขั้นตอน</label><input id="edit_name_{{ $step->id }}" name="name" value="{{ $step->name }}" required></div>
                            <div class="field"><label for="edit_weight_{{ $step->id }}">น้ำหนัก (%)</label><input id="edit_weight_{{ $step->id }}" name="weight_percent" type="number" min="1" max="100" value="{{ $step->weight_percent }}" required></div>
                            <div class="field"><label for="edit_order_{{ $step->id }}">ลำดับ</label><input id="edit_order_{{ $step->id }}" name="sort_order" type="number" min="0" max="1000" value="{{ $step->sort_order }}" required></div>
                            <div class="field step-description-field"><label for="edit_description_{{ $step->id }}">รายละเอียด</label><input id="edit_description_{{ $step->id }}" name="description" value="{{ $step->description }}"></div>
                            <div class="field"><label for="edit_start_{{ $step->id }}">แผนเริ่ม</label><input id="edit_start_{{ $step->id }}" name="planned_start_date" type="date" value="{{ $step->planned_start_date?->format('Y-m-d') }}"></div>
                            <div class="field"><label for="edit_end_{{ $step->id }}">แผนเสร็จ</label><input id="edit_end_{{ $step->id }}" name="planned_end_date" type="date" value="{{ $step->planned_end_date?->format('Y-m-d') }}"></div>
                            <button class="button secondary" type="submit">บันทึกข้อมูล</button>
                        </form>
                        @if($step->progressLogs->isEmpty())
                            <form method="POST" action="{{ route('admin.project-steps.destroy', [$project, $step]) }}" onsubmit="return confirm('ต้องการลบขั้นตอนนี้ใช่ไหม?')">@csrf @method('DELETE')<button class="step-delete-button" type="submit">ลบขั้นตอนนี้</button></form>
                        @endif
                    </details>
                    @endif
                </article>
            @empty
                <div class="project-empty compact"><h2>ยังไม่มีขั้นตอนงาน</h2><p>{{ $isAdmin ? 'เริ่มเพิ่มขั้นตอนและกำหนดน้ำหนักให้รวมครบ 100%' : 'Admin ยังไม่ได้กำหนดขั้นตอนงานของโครงการนี้' }}</p></div>
            @endforelse
        </div>
    </section>

    <section class="card panel launch-panel" id="project-documents">
        <div class="panel-heading"><div><p class="eyebrow">DOCUMENT CENTER</p><h2>เอกสารโครงการ</h2><p>สัญญา BOQ แบบก่อสร้าง และเอกสารส่งมอบในพื้นที่เดียว</p></div><span class="client-update-count">{{ $project->documents->count() }} ไฟล์</span></div>
        @if($isAdmin)
            <form class="launch-form" method="POST" action="{{ route('admin.project-documents.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="field"><label for="document_title">ชื่อเอกสาร</label><input id="document_title" name="title" required></div>
                <div class="field"><label for="document_category">หมวด</label><select id="document_category" name="category">@foreach($documentCategoryLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label for="document_version">เวอร์ชัน</label><input id="document_version" name="version" value="1.0" required></div>
                <div class="field"><label for="document_visibility">การมองเห็น</label><select id="document_visibility" name="visibility">@foreach($documentVisibilityLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label for="document_file">ไฟล์ (ไม่เกิน 20 MB)</label><input id="document_file" name="file" type="file" required></div>
                <div class="field launch-wide"><label for="document_notes">หมายเหตุ</label><input id="document_notes" name="notes"></div>
                <button class="button" type="submit">เพิ่มเอกสาร</button>
            </form>
        @endif
        <div class="launch-list">
            @forelse($project->documents as $document)
                <article class="launch-list-row">
                    <div><strong>{{ $document->title }}</strong><small>{{ $documentCategoryLabels[$document->category] }} · v{{ $document->version }} · {{ $documentVisibilityLabels[$document->visibility] }}</small></div>
                    <div class="actions"><a class="button secondary" href="{{ route('project-documents.show', $document) }}" target="_blank">เปิดไฟล์</a>@if($isAdmin)<form method="POST" action="{{ route('admin.project-documents.destroy', [$project, $document]) }}" onsubmit="return confirm('ลบเอกสารนี้ใช่ไหม?')">@csrf @method('DELETE')<button class="launch-text-danger" type="submit">ลบ</button></form>@endif</div>
                </article>
            @empty
                <div class="project-empty compact"><h2>ยังไม่มีเอกสาร</h2><p>{{ $isAdmin ? 'เพิ่มเอกสารฉบับแรกของโครงการได้จากแบบฟอร์มด้านบน' : 'Admin ยังไม่ได้เพิ่มเอกสารสำหรับโครงการนี้' }}</p></div>
            @endforelse
        </div>
    </section>

    <section class="card panel launch-panel" id="project-issues">
        <div class="panel-heading"><div><p class="eyebrow">SITE ISSUES</p><h2>ปัญหาและงานแก้ไข</h2><p>บันทึก มอบหมาย ติดตาม และตรวจรับปัญหาหน้างาน</p></div><span class="client-update-count">{{ $project->issues->where('status', '!=', 'resolved')->count() }} รายการเปิด</span></div>
        <form class="launch-form" method="POST" action="{{ route('admin.project-issues.store', $project) }}" enctype="multipart/form-data">
            @csrf
            <div class="field"><label for="issue_title">หัวข้อปัญหา</label><input id="issue_title" name="title" required></div>
            <div class="field"><label for="issue_priority">ความสำคัญ</label><select id="issue_priority" name="priority">@foreach($issuePriorityLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="issue_step">ขั้นตอนงาน</label><select id="issue_step" name="project_step_id"><option value="">ไม่ระบุ</option>@foreach($project->steps as $step)<option value="{{ $step->id }}">{{ $step->name }}</option>@endforeach</select></div>
            <div class="field"><label for="issue_assignee">ผู้รับผิดชอบ</label><select id="issue_assignee" name="assigned_to"><option value="">ยังไม่มอบหมาย</option>@foreach($staffUsers as $staff)<option value="{{ $staff->id }}">{{ $staff->name }}</option>@endforeach</select></div>
            <div class="field"><label for="issue_due">กำหนดแก้ไข</label><input id="issue_due" name="due_date" type="date"></div>
            <div class="field"><label for="issue_media">รูปประกอบ</label><input id="issue_media" name="media[]" type="file" accept="image/*" multiple></div>
            <div class="field launch-wide"><label for="issue_description">รายละเอียด</label><textarea id="issue_description" name="description"></textarea></div>
            <label class="launch-check"><input name="customer_visible" type="checkbox" value="1" checked> แสดงรายการนี้ให้ลูกค้าเห็น</label>
            <button class="button" type="submit">เปิดรายการปัญหา</button>
        </form>
        <div class="launch-issue-grid">
            @forelse($project->issues as $issue)
                <article class="launch-issue-card priority-{{ $issue->priority }}">
                    <div class="launch-issue-head"><div><span>{{ $issuePriorityLabels[$issue->priority] }}</span><h3>{{ $issue->title }}</h3></div><strong>{{ $issueStatusLabels[$issue->status] }}</strong></div>
                    <p>{{ $issue->description ?: 'ไม่มีรายละเอียดเพิ่มเติม' }}</p>
                    @if($issue->media->isNotEmpty())<div class="launch-media">@foreach($issue->media as $media)<a href="{{ route('project-issue-media.show', $media) }}" target="_blank"><img src="{{ route('project-issue-media.show', $media) }}" alt=""></a>@endforeach</div>@endif
                    <form class="launch-form compact" method="POST" action="{{ route('admin.project-issues.update', [$project, $issue]) }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <input name="title" type="hidden" value="{{ $issue->title }}"><input name="description" type="hidden" value="{{ $issue->description }}"><input name="priority" type="hidden" value="{{ $issue->priority }}"><input name="project_step_id" type="hidden" value="{{ $issue->project_step_id }}"><input name="assigned_to" type="hidden" value="{{ $issue->assigned_to }}"><input name="due_date" type="hidden" value="{{ $issue->due_date?->format('Y-m-d') }}">@if($issue->customer_visible)<input name="customer_visible" type="hidden" value="1">@endif
                        <div class="field"><label for="issue_status_{{ $issue->id }}">สถานะ</label><select id="issue_status_{{ $issue->id }}" name="status">@foreach($issueStatusLabels as $value => $label)@if($isAdmin || $value !== 'resolved')<option value="{{ $value }}" @selected($issue->status === $value)>{{ $label }}</option>@endif @endforeach</select></div>
                        <div class="field"><label for="issue_more_media_{{ $issue->id }}">เพิ่มรูป</label><input id="issue_more_media_{{ $issue->id }}" name="media[]" type="file" accept="image/*" multiple></div>
                        <button class="button secondary" type="submit">อัปเดตสถานะ</button>
                    </form>
                    <small>ผู้รับผิดชอบ: {{ $issue->assignee?->name ?: '-' }} · กำหนด {{ $issue->due_date?->format('d/m/Y') ?: '-' }}</small>
                    @if($isAdmin)<form method="POST" action="{{ route('admin.project-issues.destroy', [$project, $issue]) }}" onsubmit="return confirm('ลบรายการปัญหานี้ใช่ไหม?')">@csrf @method('DELETE')<button class="launch-text-danger" type="submit">ลบรายการ</button></form>@endif
                </article>
            @empty
                <div class="project-empty compact"><h2>ยังไม่มีปัญหาหน้างาน</h2><p>รายการตรวจพบและงานแก้ไขจะปรากฏที่นี่</p></div>
            @endforelse
        </div>
    </section>

    @if($isAdmin)
    <section class="card panel project-customers-panel">
        <div class="panel-heading"><div><h2>ลูกค้าที่ติดตามโครงการ</h2><p>บัญชีที่มีสิทธิ์เปิดดูข้อมูลและรูปหน้างาน</p></div></div>
        <div class="project-customer-list">@foreach($project->customers as $customer)<div><span class="user-list-avatar">{{ mb_strtoupper(mb_substr($customer->name,0,1)) }}</span><span><strong>{{ $customer->name }}</strong><small>{{ $customer->email }}</small></span></div>@endforeach</div>
    </section>
    @endif

    <section id="project-updates" class="card panel project-timeline-panel">
        <div class="panel-heading"><div><p class="eyebrow">SITE UPDATES</p><h2>Timeline อัปเดตหน้างาน</h2><p>เรียงตามวันที่ทำงานจริงจากล่าสุด</p></div><a class="button" href="{{ route('admin.project-updates.create',$project) }}">เพิ่มอัปเดต</a></div>
        <div class="project-timeline">
            @forelse($project->updates as $updateItem)
                <article class="timeline-entry">
                    <div class="timeline-marker"><span></span></div>
                    <div class="timeline-content">
                        <div class="timeline-meta"><span>{{ $stageLabels[$updateItem->stage] ?? $updateItem->stage }}</span><time>{{ $updateItem->work_performed_at->format('d/m/Y H:i') }}</time><em class="update-status-{{ $updateItem->status }} {{ $updateItem->status === 'published' ? 'is-published' : '' }}">{{ $updateStatusLabels[$updateItem->status] ?? $updateItem->status }}</em></div>
                        <div class="timeline-title-row"><div><h3>{{ $updateItem->title }}</h3><p>{{ $updateItem->description }}</p>@if($updateItem->projectStep)<small class="timeline-step-label">ขั้นตอน: {{ $updateItem->projectStep->name }}</small>@endif</div><strong>{{ $updateItem->progress_percent }}%<small>{{ $updateItem->status === 'published' ? ($updateItem->projectStep ? 'ของขั้นตอน' : 'ที่อนุมัติ') : ($updateItem->projectStep ? 'ของขั้นตอนที่เสนอ' : 'รวมที่เสนอ') }}</small></strong></div>
                        @if($updateItem->inspection_result || $updateItem->progress_reason)
                            <div class="timeline-inspection-summary">
                                @if($updateItem->inspection_result)<span>{{ $inspectionLabels[$updateItem->inspection_result] ?? $updateItem->inspection_result }}</span>@endif
                                @if($updateItem->progress_reason)<p>{{ $updateItem->progress_reason }}</p>@endif
                            </div>
                        @endif
                        @if($updateItem->media->isNotEmpty())
                            <div class="timeline-gallery">@foreach($updateItem->media as $media)<a href="{{ route('project-media.show',$media) }}" target="_blank"><img src="{{ route('project-media.show',$media) }}" alt="{{ $media->original_name }}"></a>@endforeach</div>
                        @endif
                        @if($updateItem->review_note)
                            <div class="timeline-review-note"><strong>{{ $updateItem->status === 'changes_requested' ? 'เหตุผลที่ส่งกลับ' : 'หมายเหตุการอนุมัติ' }}</strong><p>{{ $updateItem->review_note }}</p><small>{{ $updateItem->reviewer?->name }} · {{ $updateItem->reviewed_at?->format('d/m/Y H:i') }}</small></div>
                        @endif
                        @if($isAdmin && $updateItem->reviewLogs->isNotEmpty())
                            <details class="timeline-review-history">
                                <summary>ประวัติการตรวจ {{ $updateItem->reviewLogs->count() }} รายการ</summary>
                                @foreach($updateItem->reviewLogs as $reviewLog)
                                    <div><strong>{{ \App\Models\ProjectUpdateReviewLog::ACTION_LABELS[$reviewLog->action] ?? $reviewLog->action }}</strong><span>{{ $reviewLog->actor?->name ?: 'ไม่ระบุผู้ดำเนินการ' }}</span><time>{{ $reviewLog->created_at->format('d/m/Y H:i') }}</time>@if($reviewLog->note)<p>{{ $reviewLog->note }}</p>@endif</div>
                                @endforeach
                            </details>
                        @endif
                        @if($isAdmin && $updateItem->status === 'pending_review')
                            <div class="timeline-review-panel">
                                <form method="POST" action="{{ route('admin.project-updates.approve', [$project, $updateItem]) }}">
                                    @csrf @method('PUT')
                                    <label for="approve_note_{{ $updateItem->id }}">หมายเหตุการอนุมัติ (ไม่บังคับ)</label>
                                    <input id="approve_note_{{ $updateItem->id }}" name="review_note" placeholder="รายละเอียดที่ต้องการบันทึกไว้">
                                    <button class="button" type="submit" onclick="return confirm('อนุมัติและเผยแพร่อัปเดตนี้ให้ลูกค้าเห็นใช่ไหม?')">อนุมัติและแจ้งลูกค้า</button>
                                </form>
                                <form method="POST" action="{{ route('admin.project-updates.request-changes', [$project, $updateItem]) }}">
                                    @csrf @method('PUT')
                                    <label for="change_note_{{ $updateItem->id }}">เหตุผลที่ต้องแก้ไข</label>
                                    <input id="change_note_{{ $updateItem->id }}" name="review_note" required placeholder="ระบุสิ่งที่ผู้ตรวจต้องแก้ไข">
                                    <button class="button secondary" type="submit">ส่งกลับแก้ไข</button>
                                </form>
                            </div>
                        @endif
                        <div class="timeline-actions"><small>บันทึกโดย {{ $updateItem->creator?->name ?: 'ไม่ระบุ' }}</small>@if($updateItem->canBeEditedBy(auth()->user()))<a href="{{ route('admin.project-updates.edit',[$project,$updateItem]) }}">แก้ไข</a>@endif @if($isAdmin)<form method="POST" action="{{ route('admin.project-updates.destroy',[$project,$updateItem]) }}" onsubmit="return confirm('ต้องการลบอัปเดตนี้ใช่ไหม?')">@csrf @method('DELETE')<button type="submit">ลบ</button></form>@endif</div>
                    </div>
                </article>
            @empty
                <div class="project-empty"><h2>ยังไม่มีอัปเดตหน้างาน</h2><p>เพิ่มรูปและรายละเอียดครั้งแรกเพื่อเริ่ม Timeline ของลูกค้า</p><a class="button" href="{{ route('admin.project-updates.create',$project) }}">เพิ่มอัปเดตแรก</a></div>
            @endforelse
        </div>
    </section>
</x-admin-layout>
