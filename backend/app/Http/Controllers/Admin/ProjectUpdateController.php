<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectStep;
use App\Models\ProjectUpdate;
use App\Models\User;
use App\Notifications\ProjectUpdateChangesRequested;
use App\Notifications\ProjectUpdatePublished;
use App\Notifications\ProjectUpdateSubmitted;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectUpdateController extends Controller
{
    public function __construct(private readonly MediaStorage $mediaStorage) {}

    public function create(Request $request, Project $project): View
    {
        $this->ensureCanManageProject($request, $project);

        return view('admin.project-updates.create', [
            'project' => $project,
            'update' => new ProjectUpdate(['progress_percent' => $project->progress_percent, 'status' => 'draft', 'work_performed_at' => now()]),
            'stageLabels' => ProjectUpdate::STAGE_LABELS,
            'statusLabels' => ProjectUpdate::STATUS_LABELS,
            'inspectionLabels' => ProjectStep::INSPECTION_LABELS,
            'projectSteps' => $project->steps()->get(),
            'usesWeightedSteps' => $project->steps()->exists(),
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->ensureCanManageProject($request, $project);
        $data = $this->validatedData($request);
        $this->validateProgressProposal($project, $data);
        $update = DB::transaction(function () use ($request, $project, $data): ProjectUpdate {
            $update = $project->updates()->create($this->prepareData($data, $request));
            $this->storeImages($request, $update);
            if ($update->status === 'pending_review') {
                $this->recordReviewAction($update, $request->user(), 'submitted', 'draft', 'pending_review');
            }

            return $update;
        });

        if ($update->status === 'pending_review') {
            $this->notifyAdmins($update);
        }
        AuditLog::record($request->user(), 'project_update.created', $update, "เพิ่มอัปเดตหน้างาน {$update->title}", ['status' => $update->status]);

        return redirect()->route('admin.projects.show', $project)->with(
            'success',
            $update->status === 'pending_review'
                ? 'ส่งอัปเดตให้ Admin ตรวจสอบแล้ว'
                : 'บันทึกอัปเดตเป็นฉบับร่างแล้ว',
        );
    }

    public function edit(Request $request, Project $project, ProjectUpdate $update): View
    {
        $this->ensureBelongsToProject($project, $update);
        $this->ensureCanManageProject($request, $project);
        $this->ensureCanEditUpdate($request, $update);
        $update->load('media');

        return view('admin.project-updates.edit', compact('project', 'update') + [
            'stageLabels' => ProjectUpdate::STAGE_LABELS,
            'statusLabels' => ProjectUpdate::STATUS_LABELS,
            'inspectionLabels' => ProjectStep::INSPECTION_LABELS,
            'projectSteps' => $project->steps()->get(),
            'usesWeightedSteps' => $project->steps()->exists(),
        ]);
    }

    public function update(Request $request, Project $project, ProjectUpdate $update): RedirectResponse
    {
        $this->ensureBelongsToProject($project, $update);
        $this->ensureCanManageProject($request, $project);
        $this->ensureCanEditUpdate($request, $update);
        $data = $this->validatedData($request);
        $this->validateProgressProposal($project, $data);
        DB::transaction(function () use ($request, $update, $data): void {
            $fromStatus = $update->status;
            $update->update($this->prepareData($data, $request, $update));
            $this->storeImages($request, $update);
            if ($update->status === 'pending_review') {
                $this->recordReviewAction($update, $request->user(), 'submitted', $fromStatus, 'pending_review');
            }
        });

        if ($update->fresh()->status === 'pending_review') {
            $this->notifyAdmins($update->fresh());
        }
        AuditLog::record($request->user(), 'project_update.updated', $update, "แก้ไขอัปเดตหน้างาน {$update->title}", ['status' => $update->fresh()->status]);

        return redirect()->route('admin.projects.show', $project)->with(
            'success',
            $update->fresh()->status === 'pending_review'
                ? 'แก้ไขและส่งอัปเดตให้ Admin ตรวจสอบแล้ว'
                : 'บันทึกการแก้ไขฉบับร่างแล้ว',
        );
    }

    public function approve(Request $request, Project $project, ProjectUpdate $update): RedirectResponse
    {
        $this->ensureBelongsToProject($project, $update);
        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $project, $update, $data): void {
            $lockedUpdate = ProjectUpdate::query()->lockForUpdate()->findOrFail($update->id);
            abort_unless($lockedUpdate->status === 'pending_review', 422, 'รายการนี้ไม่ได้อยู่ในสถานะรอตรวจ');

            $this->applyApprovedProgress($project, $lockedUpdate, $request->user());
            $this->recordReviewAction(
                $lockedUpdate,
                $request->user(),
                'approved',
                'pending_review',
                'published',
                $data['review_note'] ?? null,
            );
            $lockedUpdate->update([
                'status' => 'published',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
                'published_at' => now(),
            ]);
        });

        $this->notifyCustomers($update->fresh());
        AuditLog::record($request->user(), 'project_update.approved', $update, "อนุมัติอัปเดตหน้างาน {$update->title}");

        return back()->with('success', 'อนุมัติอัปเดต คำนวณความคืบหน้า และแจ้งลูกค้าแล้ว');
    }

    public function requestChanges(Request $request, Project $project, ProjectUpdate $update): RedirectResponse
    {
        $this->ensureBelongsToProject($project, $update);
        $data = $request->validate([
            'review_note' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $update, $data): void {
            $lockedUpdate = ProjectUpdate::query()->lockForUpdate()->findOrFail($update->id);
            abort_unless($lockedUpdate->status === 'pending_review', 422, 'รายการนี้ไม่ได้อยู่ในสถานะรอตรวจ');
            $this->recordReviewAction(
                $lockedUpdate,
                $request->user(),
                'changes_requested',
                'pending_review',
                'changes_requested',
                $data['review_note'],
            );
            $lockedUpdate->update([
                'status' => 'changes_requested',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'],
                'published_at' => null,
            ]);
        });

        $creator = $update->fresh()->creator;
        if ($creator && ! $creator->isAdmin()) {
            $creator->notify(new ProjectUpdateChangesRequested($update->fresh()));
        }
        AuditLog::record($request->user(), 'project_update.changes_requested', $update, "ส่งอัปเดต {$update->title} กลับแก้ไข", ['review_note' => $data['review_note']]);

        return back()->with('success', 'ส่งรายการกลับให้ผู้ตรวจแก้ไขแล้ว');
    }

    public function destroy(Request $request, Project $project, ProjectUpdate $update): RedirectResponse
    {
        $this->ensureBelongsToProject($project, $update);
        $update->load('media');
        foreach ($update->media as $media) {
            if ($media->storedFile) {
                $this->mediaStorage->delete($media->storedFile);
            } else {
                Storage::disk('local')->delete($media->path);
            }
        }
        AuditLog::record($request->user(), 'project_update.deleted', $update, "ลบอัปเดตหน้างาน {$update->title}");
        $update->delete();

        return back()->with('success', 'ลบอัปเดตหน้างานเรียบร้อยแล้ว');
    }

    public function destroyMedia(Request $request, Project $project, ProjectUpdate $update, int $media): RedirectResponse
    {
        $this->ensureBelongsToProject($project, $update);
        $item = $update->media()->findOrFail($media);
        if ($item->storedFile) {
            $this->mediaStorage->delete($item->storedFile);
        } else {
            Storage::disk('local')->delete($item->path);
        }
        $item->delete();
        AuditLog::record($request->user(), 'project_update.media_deleted', $update, "ลบไฟล์จากอัปเดต {$update->title}");

        return back()->with('success', 'ลบรูปหน้างานเรียบร้อยแล้ว');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'stage' => ['required', Rule::in(array_keys(ProjectUpdate::STAGE_LABELS))],
            'progress_percent' => ['required', 'integer', 'between:0,100'],
            'project_step_id' => [
                'nullable',
                'integer',
                Rule::exists('project_steps', 'id')->where('project_id', $request->route('project')->id),
            ],
            'inspection_result' => ['nullable', Rule::in(array_keys(ProjectStep::INSPECTION_LABELS))],
            'progress_reason' => ['nullable', 'string', 'max:2000'],
            'work_performed_at' => ['required', 'date'],
            'workflow_action' => ['required', Rule::in(['save_draft', 'submit_review'])],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
        ]);
    }

    private function prepareData(array $data, Request $request, ?ProjectUpdate $update = null): array
    {
        unset($data['images']);
        $workflowAction = $data['workflow_action'];
        unset($data['workflow_action']);
        $data['created_by'] = $update?->created_by ?? $request->user()->id;
        $data['status'] = $workflowAction === 'submit_review' ? 'pending_review' : 'draft';
        $data['submitted_at'] = $workflowAction === 'submit_review' ? now() : $update?->submitted_at;
        $data['published_at'] = null;
        $data['notified_at'] = null;

        return $data;
    }

    private function storeImages(Request $request, ProjectUpdate $update): void
    {
        $startOrder = ((int) $update->media()->max('sort_order')) + 1;
        foreach ($request->file('images', []) as $index => $image) {
            $storedFile = $this->mediaStorage->store(
                $image,
                "project-updates-{$update->project_id}",
                'private',
                $request->user(),
            );
            $update->media()->create([
                'stored_file_id' => $storedFile->id,
                'path' => $storedFile->path,
                'original_name' => $storedFile->original_name,
                'mime_type' => $storedFile->mime_type,
                'sort_order' => $startOrder + $index,
            ]);
        }
    }

    private function applyApprovedProgress(Project $project, ProjectUpdate $update, User $reviewer): void
    {
        if ($update->project_step_id) {
            $step = ProjectStep::query()->lockForUpdate()->findOrFail($update->project_step_id);
            abort_unless($step->project_id === $project->id, 422);
            $previousProgress = $step->progress_percent;
            $step->update([
                'progress_percent' => $update->progress_percent,
                'status' => $this->stepStatusFor($update->progress_percent, $update->inspection_result),
            ]);
            $step->progressLogs()->create([
                'changed_by' => $reviewer->id,
                'previous_progress' => $previousProgress,
                'new_progress' => $update->progress_percent,
                'inspection_result' => $update->inspection_result ?? 'not_checked',
                'reason' => $update->progress_reason,
            ]);
            $project->recalculateProgress();

            return;
        }

        $project->update([
            'progress_percent' => $update->progress_percent,
            'status' => $update->progress_percent >= 100 ? 'completed' : ($project->status === 'preparing' ? 'in_progress' : $project->status),
        ]);
    }

    private function ensureBelongsToProject(Project $project, ProjectUpdate $update): void
    {
        abort_unless($update->project_id === $project->id, 404);
    }

    private function ensureCanManageProject(Request $request, Project $project): void
    {
        abort_unless($project->canBeManagedBy($request->user()), 403);
    }

    private function ensureCanEditUpdate(Request $request, ProjectUpdate $update): void
    {
        abort_unless($update->canBeEditedBy($request->user()), 403);
    }

    private function notifyCustomers(ProjectUpdate $update): void
    {
        if ($update->notified_at) {
            return;
        }

        $update->loadMissing('project.customers');
        Notification::send($update->project->customers, new ProjectUpdatePublished($update));
        $update->update(['notified_at' => now()]);
    }

    private function notifyAdmins(ProjectUpdate $update): void
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->when($update->creator?->isAdmin(), fn ($query) => $query->whereKeyNot($update->created_by))
            ->get();
        Notification::send($admins, new ProjectUpdateSubmitted($update));
    }

    private function recordReviewAction(
        ProjectUpdate $update,
        User $actor,
        string $action,
        ?string $fromStatus,
        string $toStatus,
        ?string $note = null,
    ): void {
        $update->reviewLogs()->create([
            'acted_by' => $actor->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }

    private function validateProgressProposal(Project $project, array $data): void
    {
        if ($data['workflow_action'] !== 'submit_review') {
            return;
        }

        if ($project->steps()->exists()) {
            if (! $project->hasConfiguredSteps()) {
                throw ValidationException::withMessages([
                    'project_step_id' => 'ต้องกำหนดน้ำหนักขั้นตอนงานให้รวมครบ 100% ก่อนส่งตรวจ',
                ]);
            }

            if (blank($data['project_step_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'project_step_id' => 'กรุณาเลือกขั้นตอนงานที่ต้องการอัปเดต',
                ]);
            }

            if (blank($data['inspection_result'] ?? null)) {
                throw ValidationException::withMessages([
                    'inspection_result' => 'กรุณาระบุผลการตรวจของขั้นตอนงาน',
                ]);
            }

            $step = $project->steps()->findOrFail($data['project_step_id']);
            $needsReason = $data['progress_percent'] < $step->progress_percent
                || in_array($data['inspection_result'], ['failed', 'rework'], true);
        } else {
            $needsReason = $data['progress_percent'] < $project->progress_percent;
        }

        if ($needsReason && blank($data['progress_reason'] ?? null)) {
            throw ValidationException::withMessages([
                'progress_reason' => 'กรุณาระบุเหตุผลเมื่อเปอร์เซ็นต์ลดลง หรืองานไม่ผ่านการตรวจ',
            ]);
        }
    }

    private function stepStatusFor(int $progress, ?string $inspectionResult): string
    {
        if (in_array($inspectionResult, ['failed', 'rework'], true)) {
            return 'needs_attention';
        }

        if ($progress >= 100 && $inspectionResult === 'passed') {
            return 'completed';
        }

        return $progress > 0 ? 'in_progress' : 'pending';
    }
}
