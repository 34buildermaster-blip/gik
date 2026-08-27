<form class="card panel site-update-form" method="POST" enctype="multipart/form-data" action="{{ $update->exists ? route('admin.project-updates.update', [$project, $update]) : route('admin.project-updates.store', $project) }}">
    @csrf
    @if($update->exists) @method('PUT') @endif

    <div class="site-update-grid">
        <div class="site-update-fields">
            <div class="field"><label for="title">หัวข้ออัปเดต</label><input id="title" name="title" value="{{ old('title', $update->title) }}" placeholder="เช่น ติดตั้งโครงหลังคาเรียบร้อยแล้ว" required></div>
            <div class="field"><label for="description">รายละเอียดงาน</label><textarea id="description" name="description" rows="7" required>{{ old('description', $update->description) }}</textarea></div>
            <div class="form-grid">
                <div class="field"><label for="stage">ขั้นตอนงาน</label><select id="stage" name="stage" required>@foreach($stageLabels as $value=>$label)<option value="{{ $value }}" @selected(old('stage',$update->stage)===$value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label for="work_performed_at">วันที่และเวลาที่ทำงานจริง</label><input id="work_performed_at" name="work_performed_at" type="datetime-local" value="{{ old('work_performed_at', $update->work_performed_at?->format('Y-m-d\TH:i')) }}" required></div>
                @if($usesWeightedSteps)
                    <div class="field">
                        <label for="project_step_id">ขั้นตอนตามแผนโครงการ</label>
                        <select id="project_step_id" name="project_step_id">
                            <option value="">เลือกขั้นตอนที่ตรวจ</option>
                            @foreach($projectSteps as $step)
                                <option value="{{ $step->id }}" data-progress="{{ $step->progress_percent }}" @selected((int) old('project_step_id', $update->project_step_id) === $step->id)>{{ $step->name }} · ปัจจุบัน {{ $step->progress_percent }}%</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label for="progress_percent">เสนอความสำเร็จของขั้นตอน (%)</label><input id="progress_percent" name="progress_percent" type="number" min="0" max="100" value="{{ old('progress_percent',$update->progress_percent) }}" required><small>เปอร์เซ็นต์จริงจะเปลี่ยนหลัง Admin อนุมัติ</small></div>
                    <div class="field">
                        <label for="inspection_result">ผลการตรวจหน้างาน</label>
                        <select id="inspection_result" name="inspection_result">
                            @foreach($inspectionLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('inspection_result', $update->inspection_result ?? 'not_checked') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="field"><label for="progress_percent">เสนอความคืบหน้ารวม (%)</label><input id="progress_percent" name="progress_percent" type="number" min="0" max="100" value="{{ old('progress_percent',$update->progress_percent) }}" required><small>เปอร์เซ็นต์จริงจะเปลี่ยนหลัง Admin อนุมัติ</small></div>
                @endif
                <div class="field form-grid-full"><label for="progress_reason">เหตุผล / หมายเหตุการตรวจ</label><textarea id="progress_reason" name="progress_reason" rows="3" placeholder="จำเป็นเมื่อเปอร์เซ็นต์ลดลง งานไม่ผ่าน หรืออยู่ระหว่างแก้ไข">{{ old('progress_reason', $update->progress_reason) }}</textarea></div>
            </div>

            @if($update->review_note)
                <div class="review-feedback"><strong>หมายเหตุจาก Admin</strong><p>{{ $update->review_note }}</p><small>{{ $update->reviewed_at?->format('d/m/Y H:i') }}</small></div>
            @endif
        </div>

        <aside class="site-update-media-panel">
            <div class="upload-dropzone">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19h16V5H4v14z"></path><path d="m7 15 3-3 2 2 2-3 3 4"></path><path d="M12 3v7"></path><path d="m9 6 3-3 3 3"></path></svg>
                <strong>เพิ่มรูปหน้างาน</strong><p>เลือกได้สูงสุด 10 รูปต่อครั้ง<br>JPG, PNG, WebP ไม่เกิน 8MB/รูป</p>
                <label class="button secondary" for="images">เลือกรูปภาพ</label>
                <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-project-images>
            </div>
            <div class="upload-preview-grid" data-project-image-preview></div>

            @if($update->exists && $update->media->isNotEmpty())
                <div class="existing-media-grid">
                    @foreach($update->media as $media)
                        <div><img src="{{ route('project-media.show',$media) }}" alt="{{ $media->original_name }}">@if(auth()->user()->isAdmin())<button form="delete-media-{{ $media->id }}" type="submit" title="ลบรูป" aria-label="ลบรูป {{ $media->original_name }}">×</button>@endif</div>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>

    <div class="project-form-actions">
        <button class="button secondary" type="submit" name="workflow_action" value="save_draft">เก็บเป็นฉบับร่าง</button>
        <button class="button" type="submit" name="workflow_action" value="submit_review">ส่งให้ Admin ตรวจ</button>
        <a class="button secondary" href="{{ route('admin.projects.show',$project) }}">ยกเลิก</a>
    </div>
</form>

@if($update->exists && auth()->user()->isAdmin())
    @foreach($update->media as $media)
        <form id="delete-media-{{ $media->id }}" method="POST" action="{{ route('admin.project-updates.media.destroy',[$project,$update,$media->id]) }}">@csrf @method('DELETE')</form>
    @endforeach
@endif

<script>
    (() => {
        const input = document.querySelector('[data-project-images]');
        const preview = document.querySelector('[data-project-image-preview]');
        input?.addEventListener('change', () => {
            preview.replaceChildren();
            [...input.files].slice(0, 10).forEach((file) => {
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = file.name;
                image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
                preview.appendChild(image);
            });
        });

        const stepSelect = document.getElementById('project_step_id');
        const progressInput = document.getElementById('progress_percent');
        stepSelect?.addEventListener('change', () => {
            const selected = stepSelect.options[stepSelect.selectedIndex];
            if (selected?.dataset.progress !== undefined && progressInput) {
                progressInput.value = selected.dataset.progress;
            }
        });
    })();
</script>
