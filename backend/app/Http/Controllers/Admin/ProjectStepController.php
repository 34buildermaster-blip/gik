<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectStepController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validatedStepData($request);

        DB::transaction(function () use ($project, $data): void {
            $currentWeight = (int) $project->steps()->lockForUpdate()->sum('weight_percent');
            $this->ensureWeightDoesNotExceedOneHundred($currentWeight + $data['weight_percent']);

            $data['sort_order'] = ((int) $project->steps()->max('sort_order')) + 1;
            $project->steps()->create($data);
            $project->recalculateProgress();
        });

        AuditLog::record($request->user(), 'project_step.created', $project, "เพิ่มขั้นตอน {$data['name']}");

        return back()->with('success', 'เพิ่มขั้นตอนงานเรียบร้อยแล้ว');
    }

    public function update(Request $request, Project $project, ProjectStep $step): RedirectResponse
    {
        $this->ensureBelongsToProject($project, $step);
        $data = $this->validatedStepData($request, true);

        DB::transaction(function () use ($project, $step, $data): void {
            $otherWeight = (int) $project->steps()->where('id', '!=', $step->id)->lockForUpdate()->sum('weight_percent');
            $this->ensureWeightDoesNotExceedOneHundred($otherWeight + $data['weight_percent']);

            $step->update($data);
            $project->recalculateProgress();
        });

        AuditLog::record($request->user(), 'project_step.updated', $step, "แก้ไขขั้นตอน {$step->name}");

        return back()->with('success', 'แก้ไขขั้นตอนงานเรียบร้อยแล้ว');
    }

    public function updateProgress(Request $request, Project $project, ProjectStep $step): RedirectResponse
    {
        abort_unless($project->canBeManagedBy($request->user()), 403);
        $this->ensureBelongsToProject($project, $step);
        $data = $request->validate([
            'progress_percent' => ['required', 'integer', 'between:0,100'],
            'inspection_result' => ['required', Rule::in(array_keys(ProjectStep::INSPECTION_LABELS))],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $project->hasConfiguredSteps()) {
            throw ValidationException::withMessages([
                'steps' => 'ต้องกำหนดน้ำหนักของทุกขั้นตอนให้รวมครบ 100% ก่อนอัปเดตความคืบหน้า',
            ]);
        }

        DB::transaction(function () use ($request, $project, $step, $data): void {
            $lockedStep = ProjectStep::query()->lockForUpdate()->findOrFail($step->id);
            $previousProgress = $lockedStep->progress_percent;
            $isDecrease = $data['progress_percent'] < $previousProgress;
            $needsReason = $isDecrease || in_array($data['inspection_result'], ['failed', 'rework'], true);

            if ($needsReason && blank($data['reason'])) {
                throw ValidationException::withMessages([
                    'reason' => 'กรุณาระบุเหตุผลเมื่อปรับเปอร์เซ็นต์ลดลง หรืองานไม่ผ่านการตรวจ',
                ]);
            }

            $lockedStep->update([
                'progress_percent' => $data['progress_percent'],
                'status' => $this->statusFor($data['progress_percent'], $data['inspection_result']),
                'actual_completed_at' => $data['progress_percent'] >= 100 && $data['inspection_result'] === 'passed'
                    ? ($lockedStep->actual_completed_at ?? now())
                    : null,
            ]);

            $lockedStep->progressLogs()->create([
                'changed_by' => $request->user()->id,
                'previous_progress' => $previousProgress,
                'new_progress' => $data['progress_percent'],
                'inspection_result' => $data['inspection_result'],
                'reason' => $data['reason'] ?? null,
            ]);

            $project->recalculateProgress();
        });

        AuditLog::record($request->user(), 'project_step.progress_updated', $step, "อัปเดตความคืบหน้า {$step->name} เป็น {$data['progress_percent']}%", ['inspection_result' => $data['inspection_result']]);

        return back()->with('success', 'อัปเดตความคืบหน้าและคำนวณเปอร์เซ็นต์รวมใหม่แล้ว');
    }

    public function destroy(Project $project, ProjectStep $step): RedirectResponse
    {
        $this->ensureBelongsToProject($project, $step);

        if ($step->progressLogs()->exists()) {
            return back()->withErrors(['steps' => 'ขั้นตอนที่มีประวัติแล้วไม่สามารถลบได้ กรุณาแก้ไขชื่อหรือน้ำหนักแทน']);
        }

        $step->delete();

        AuditLog::record(auth()->user(), 'project_step.deleted', $project, "ลบขั้นตอน {$step->name}");

        return back()->with('success', 'ลบขั้นตอนงานเรียบร้อยแล้ว');
    }

    private function validatedStepData(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'weight_percent' => ['required', 'integer', 'between:1,100'],
            'sort_order' => [$updating ? 'required' : 'nullable', 'integer', 'between:0,1000'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
        ]);
    }

    private function ensureWeightDoesNotExceedOneHundred(int $total): void
    {
        if ($total > 100) {
            throw ValidationException::withMessages([
                'weight_percent' => "น้ำหนักรวมต้องไม่เกิน 100% (ค่าที่กรอกทำให้รวมเป็น {$total}%)",
            ]);
        }
    }

    private function statusFor(int $progress, string $inspectionResult): string
    {
        if (in_array($inspectionResult, ['failed', 'rework'], true)) {
            return 'needs_attention';
        }

        if ($progress >= 100 && $inspectionResult === 'passed') {
            return 'completed';
        }

        return $progress > 0 ? 'in_progress' : 'pending';
    }

    private function ensureBelongsToProject(Project $project, ProjectStep $step): void
    {
        abort_unless($step->project_id === $project->id, 404);
    }
}
