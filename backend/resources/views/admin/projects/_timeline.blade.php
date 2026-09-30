<section id="project-updates" class="card panel project-timeline-panel">
    <div class="panel-heading"><div><p class="eyebrow">SITE UPDATES</p><h2>Timeline อัปเดตหน้างาน</h2><p>เรียงตามวันที่ทำงานจริงจากล่าสุด</p></div><a class="button" href="{{ route('admin.project-updates.create',$project) }}">เพิ่มอัปเดต</a></div>
    <div class="project-timeline">
        @forelse($project->updates as $updateItem)
            <article class="timeline-entry">
                <div class="timeline-marker"><span></span></div>
                <div class="timeline-content client-update-card admin-update-card">
                    <button type="button" class="client-update-card-trigger" data-update-dialog="admin-update-{{ $updateItem->id }}" aria-label="ดูรายละเอียดอัปเดต {{ $updateItem->title }}"></button>
                    <div class="timeline-meta"><span>{{ $stageLabels[$updateItem->stage] ?? $updateItem->stage }}</span><time>{{ $updateItem->work_performed_at->format('d/m/Y H:i') }}</time><em class="update-status-{{ $updateItem->status }} {{ $updateItem->status === 'published' ? 'is-published' : '' }}">{{ $updateStatusLabels[$updateItem->status] ?? $updateItem->status }}</em></div>
                    <div class="timeline-title-row"><div><h3>{{ $updateItem->title }}</h3><p>{{ \Illuminate\Support\Str::limit($updateItem->description, 115) }}</p>@if($updateItem->projectStep)<small class="timeline-step-label">{{ $updateItem->projectStep->name }}</small>@endif</div><strong>{{ $updateItem->progress_percent }}%<small>{{ $updateItem->projectStep ? 'ของขั้นตอน' : 'ของโครงการ' }}</small></strong></div>
                    <div class="client-update-card-footer"><small>บันทึกโดย {{ $updateItem->creator?->name ?: 'ไม่ระบุ' }} · {{ $updateItem->media->count() }} รูป</small><span>ดูรายละเอียด <b aria-hidden="true">&rarr;</b></span></div>
                </div>
                <dialog class="client-update-dialog admin-update-dialog" id="admin-update-{{ $updateItem->id }}" aria-labelledby="admin-update-title-{{ $updateItem->id }}">
                    <div class="client-update-dialog-shell">
                        <header class="client-update-dialog-header">
                            <div><span>{{ $stageLabels[$updateItem->stage] ?? $updateItem->stage }}</span><time>{{ $updateItem->work_performed_at->format('d/m/Y H:i') }}</time><em class="admin-update-dialog-status update-status-{{ $updateItem->status }}">{{ $updateStatusLabels[$updateItem->status] ?? $updateItem->status }}</em></div>
                            <button type="button" data-close-update-dialog aria-label="ปิดรายละเอียด">&times;</button>
                        </header>
                        <div class="client-update-dialog-body">
                            <div class="client-update-dialog-title"><div><p class="eyebrow">SITE UPDATE</p><h2 id="admin-update-title-{{ $updateItem->id }}">{{ $updateItem->title }}</h2></div><strong>{{ $updateItem->progress_percent }}%</strong></div>
                            <p class="client-update-description">{{ $updateItem->description }}</p>
                            <div class="client-update-facts">
                                <div><span>สถานะ</span><strong>{{ $updateStatusLabels[$updateItem->status] ?? $updateItem->status }}</strong></div>
                                <div><span>ขั้นตอนงาน</span><strong>{{ $updateItem->projectStep?->name ?: 'ความคืบหน้ารวม' }}</strong></div>
                                <div><span>อัปเดตโดย</span><strong>{{ $updateItem->creator?->name ?: 'ไม่ระบุ' }}</strong></div>
                            </div>
                            @if($updateItem->inspection_result || $updateItem->progress_reason)
                                <div class="timeline-inspection-summary">
                                    @if($updateItem->inspection_result)<span>{{ $inspectionLabels[$updateItem->inspection_result] ?? $updateItem->inspection_result }}</span>@endif
                                    @if($updateItem->progress_reason)<p>{{ $updateItem->progress_reason }}</p>@endif
                                </div>
                            @endif
                            @if($updateItem->review_note)
                                <div class="timeline-review-note"><strong>{{ $updateItem->status === 'changes_requested' ? 'เหตุผลที่ส่งกลับ' : 'หมายเหตุการอนุมัติ' }}</strong><p>{{ $updateItem->review_note }}</p><small>{{ $updateItem->reviewer?->name }} · {{ $updateItem->reviewed_at?->format('d/m/Y H:i') }}</small></div>
                            @endif
                            <section class="client-update-media-section">
                                <div class="client-update-media-heading"><h3>รูปภาพหน้างาน</h3><span>{{ $updateItem->media->count() }} รูป</span></div>
                                @if($updateItem->media->isNotEmpty())
                                    <div class="client-update-dialog-gallery">@foreach($updateItem->media as $media)<a href="{{ route('project-media.show',$media) }}" target="_blank" rel="noopener"><img src="{{ route('project-media.show',$media) }}" alt="{{ $media->original_name }}" loading="lazy"></a>@endforeach</div>
                                @else
                                    <div class="client-update-no-media"><strong>ยังไม่มีรูปภาพในอัปเดตนี้</strong></div>
                                @endif
                            </section>
                            @if($isAdmin && $updateItem->reviewLogs->isNotEmpty())
                                <details class="timeline-review-history"><summary>ประวัติการตรวจ {{ $updateItem->reviewLogs->count() }} รายการ</summary>
                                    @foreach($updateItem->reviewLogs as $reviewLog)
                                        <div><strong>{{ \App\Models\ProjectUpdateReviewLog::ACTION_LABELS[$reviewLog->action] ?? $reviewLog->action }}</strong><span>{{ $reviewLog->actor?->name ?: 'ไม่ระบุผู้ดำเนินการ' }}</span><time>{{ $reviewLog->created_at->format('d/m/Y H:i') }}</time>@if($reviewLog->note)<p>{{ $reviewLog->note }}</p>@endif</div>
                                    @endforeach
                                </details>
                            @endif
                            @if($isAdmin && $updateItem->status === 'pending_review')
                                <div class="timeline-review-panel">
                                    <form method="POST" action="{{ route('admin.project-updates.approve', [$project, $updateItem]) }}">@csrf @method('PUT')<label for="approve_note_{{ $updateItem->id }}">หมายเหตุการอนุมัติ (ไม่บังคับ)</label><input id="approve_note_{{ $updateItem->id }}" name="review_note" placeholder="รายละเอียดที่ต้องการบันทึกไว้"><button class="button" type="submit" onclick="return confirm('อนุมัติและเผยแพร่อัปเดตนี้ให้ลูกค้าเห็นใช่ไหม?')">อนุมัติและแจ้งลูกค้า</button></form>
                                    <form method="POST" action="{{ route('admin.project-updates.request-changes', [$project, $updateItem]) }}">@csrf @method('PUT')<label for="change_note_{{ $updateItem->id }}">เหตุผลที่ต้องแก้ไข</label><input id="change_note_{{ $updateItem->id }}" name="review_note" required placeholder="ระบุสิ่งที่ผู้ตรวจต้องแก้ไข"><button class="button secondary" type="submit">ส่งกลับแก้ไข</button></form>
                                </div>
                            @endif
                        </div>
                        <footer class="client-update-dialog-footer"><div class="timeline-actions">@if($updateItem->canBeEditedBy(auth()->user()))<a href="{{ route('admin.project-updates.edit',[$project,$updateItem]) }}">แก้ไข</a>@endif @if($isAdmin)<form method="POST" action="{{ route('admin.project-updates.destroy',[$project,$updateItem]) }}" onsubmit="return confirm('ต้องการลบอัปเดตนี้ใช่ไหม?')">@csrf @method('DELETE')<button type="submit">ลบ</button></form>@endif</div><button type="button" class="button secondary" data-close-update-dialog>ปิดหน้าต่าง</button></footer>
                    </div>
                </dialog>
            </article>
        @empty
            <div class="project-empty"><h2>ยังไม่มีอัปเดตหน้างาน</h2><p>เพิ่มรูปและรายละเอียดครั้งแรกเพื่อเริ่ม Timeline ของลูกค้า</p><a class="button" href="{{ route('admin.project-updates.create',$project) }}">เพิ่มอัปเดตแรก</a></div>
        @endforelse
    </div>
</section>
