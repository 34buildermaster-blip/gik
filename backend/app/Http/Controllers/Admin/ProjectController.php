<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectIssue;
use App\Models\ProjectStep;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->toString();
        $view = $request->user()->isAdmin() && $request->string('view')->toString() === 'archived'
            ? 'archived'
            : 'active';
        $baseProjectsQuery = Project::query()
            ->when($request->user()->isInspector(), fn ($query) => $query->where('manager_id', $request->user()->id));
        $projectsQuery = (clone $baseProjectsQuery)
            ->when($view === 'archived', fn ($query) => $query->onlyTrashed());

        return view('admin.projects.index', [
            'projects' => (clone $projectsQuery)
                ->with(['customers:id,name,email', 'manager:id,name'])
                ->withCount('updates')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
                })
                ->when(array_key_exists($status, Project::STATUS_LABELS), fn ($query) => $query->where('status', $status))
                ->latest('updated_at')
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'statusLabels' => Project::STATUS_LABELS,
            'totalProjects' => (clone $baseProjectsQuery)->count(),
            'activeProjects' => (clone $baseProjectsQuery)->where('status', 'in_progress')->count(),
            'completedProjects' => (clone $baseProjectsQuery)->where('status', 'completed')->count(),
            'archivedProjects' => $request->user()->isAdmin() ? Project::onlyTrashed()->count() : 0,
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.create', $this->formData(new Project([
            'status' => 'preparing',
            'progress_percent' => 0,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $customerIds = $data['customer_ids'];
        unset($data['customer_ids']);

        $project = Project::create($data);
        $project->customers()->sync($customerIds);
        AuditLog::record($request->user(), 'project.created', $project, "สร้างโครงการ {$project->code} · {$project->name}");

        return redirect()->route('admin.projects.show', $project)->with('success', 'สร้างโครงการและมอบหมายลูกค้าเรียบร้อยแล้ว');
    }

    public function show(Request $request, Project $project): View
    {
        abort_unless($project->canBeManagedBy($request->user()), 403);

        $project->load([
            'customers:id,name,email,username',
            'manager:id,name',
            'updates.media',
            'updates.creator:id,name',
            'updates.projectStep:id,name',
            'updates.reviewer:id,name',
            'updates.reviewLogs.actor:id,name',
            'steps.progressLogs.changedBy:id,name',
            'documents.file',
            'documents.uploader:id,name',
            'issues.media.file',
            'issues.projectStep:id,name',
            'issues.creator:id,name',
            'issues.assignee:id,name',
        ]);

        $stepWeightTotal = (int) $project->steps->sum('weight_percent');

        return view('admin.projects.show', [
            'project' => $project,
            'statusLabels' => Project::STATUS_LABELS,
            'typeLabels' => Project::TYPE_LABELS,
            'stageLabels' => ProjectUpdate::STAGE_LABELS,
            'updateStatusLabels' => ProjectUpdate::STATUS_LABELS,
            'stepStatusLabels' => ProjectStep::STATUS_LABELS,
            'inspectionLabels' => ProjectStep::INSPECTION_LABELS,
            'stepWeightTotal' => $stepWeightTotal,
            'isStepPlanReady' => $project->steps->isNotEmpty() && $stepWeightTotal === 100,
            'documentCategoryLabels' => ProjectDocument::CATEGORY_LABELS,
            'documentVisibilityLabels' => ProjectDocument::VISIBILITY_LABELS,
            'issueStatusLabels' => ProjectIssue::STATUS_LABELS,
            'issuePriorityLabels' => ProjectIssue::PRIORITY_LABELS,
            'staffUsers' => User::whereIn('role', ['admin', 'inspector'])->orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.edit', $this->formData($project));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validatedData($request, $project);
        $customerIds = $data['customer_ids'];
        unset($data['customer_ids']);

        $project->update($data);
        $project->customers()->sync($customerIds);
        AuditLog::record($request->user(), 'project.updated', $project, "แก้ไขโครงการ {$project->code} · {$project->name}");

        return redirect()->route('admin.projects.show', $project)->with('success', 'อัปเดตข้อมูลโครงการเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        AuditLog::record($request->user(), 'project.archived', $project, "เก็บโครงการ {$project->code} เข้าคลัง");
        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'เก็บโครงการเข้าคลังแล้ว ข้อมูลและไฟล์หน้างานยังอยู่ครบ');
    }

    public function restore(Request $request, int $project): RedirectResponse
    {
        $archivedProject = Project::onlyTrashed()->findOrFail($project);
        $archivedProject->restore();
        AuditLog::record($request->user(), 'project.restored', $archivedProject, "กู้คืนโครงการ {$archivedProject->code}");

        return redirect()
            ->route('admin.projects.show', $archivedProject)
            ->with('success', 'กู้คืนโครงการเรียบร้อยแล้ว');
    }

    private function formData(Project $project): array
    {
        $selectedCustomers = [];

        if ($project->exists) {
            $project->loadMissing('customers:id');
            $project->loadCount('steps');
            $selectedCustomers = $project->customers->pluck('id')->all();
        }

        return [
            'project' => $project,
            'customers' => User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']),
            'managers' => User::whereIn('role', ['admin', 'inspector'])->orderBy('name')->get(['id', 'name', 'role']),
            'selectedCustomers' => $selectedCustomers,
            'statusLabels' => Project::STATUS_LABELS,
            'typeLabels' => Project::TYPE_LABELS,
        ];
    }

    private function validatedData(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('projects')->ignore($project?->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Project::TYPE_LABELS))],
            'address' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['nullable', 'date'],
            'estimated_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(array_keys(Project::STATUS_LABELS))],
            'progress_percent' => ['required', 'integer', 'between:0,100'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'manager_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['admin', 'inspector']))],
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('role', 'user')],
        ]);

        if ($project?->steps()->exists()) {
            $data['progress_percent'] = $project->progress_percent;
        }

        return $data;
    }
}
