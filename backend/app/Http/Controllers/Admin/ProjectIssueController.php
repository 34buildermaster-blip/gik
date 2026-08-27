<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectIssue;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectIssueController extends Controller
{
    public function store(Request $request, Project $project, MediaStorage $storage): RedirectResponse
    {
        abort_unless($project->canBeManagedBy($request->user()), 403);
        $data = $this->validatedData($request, $project);

        $issue = DB::transaction(function () use ($request, $project, $storage, $data): ProjectIssue {
            $media = $data['media'] ?? [];
            unset($data['media']);
            $data['created_by'] = $request->user()->id;
            $data['customer_visible'] = $request->boolean('customer_visible');
            $issue = $project->issues()->create($data);

            foreach ($media as $index => $upload) {
                $file = $storage->store($upload, "project-issues/{$project->id}", 'private', $request->user());
                $issue->media()->create(['stored_file_id' => $file->id, 'sort_order' => $index]);
            }

            return $issue;
        });

        AuditLog::record($request->user(), 'project_issue.created', $issue, "เปิดรายการปัญหา {$issue->title}", ['project_id' => $project->id]);

        return back()->with('success', 'เพิ่มรายการปัญหาหน้างานเรียบร้อยแล้ว');
    }

    public function update(Request $request, Project $project, ProjectIssue $issue, MediaStorage $storage): RedirectResponse
    {
        $this->authorizeIssue($request, $project, $issue);
        $data = $this->validatedData($request, $project, true);

        if (! $request->user()->isAdmin() && $data['status'] === 'resolved') {
            abort(403);
        }

        $media = $data['media'] ?? [];
        unset($data['media']);
        $data['customer_visible'] = $request->boolean('customer_visible');
        $data['resolved_at'] = $data['status'] === 'resolved' ? ($issue->resolved_at ?? now()) : null;
        $data['verified_by'] = $data['status'] === 'resolved' ? $request->user()->id : null;
        $issue->update($data);

        foreach ($media as $upload) {
            $file = $storage->store($upload, "project-issues/{$project->id}", 'private', $request->user());
            $issue->media()->create(['stored_file_id' => $file->id, 'sort_order' => $issue->media()->count()]);
        }

        AuditLog::record($request->user(), 'project_issue.updated', $issue, "อัปเดตรายการปัญหา {$issue->title}", ['status' => $issue->status]);

        return back()->with('success', 'อัปเดตรายการปัญหาหน้างานเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, Project $project, ProjectIssue $issue, MediaStorage $storage): RedirectResponse
    {
        abort_unless($issue->project_id === $project->id, 404);
        $issue->loadMissing('media.file');
        AuditLog::record($request->user(), 'project_issue.deleted', $issue, "ลบรายการปัญหา {$issue->title}");
        $files = $issue->media->pluck('file')->filter();
        $issue->delete();
        $files->each(fn ($file) => $storage->delete($file));

        return back()->with('success', 'ลบรายการปัญหาเรียบร้อยแล้ว');
    }

    public function destroyMedia(Request $request, Project $project, ProjectIssue $issue, int $media, MediaStorage $storage): RedirectResponse
    {
        $this->authorizeIssue($request, $project, $issue);
        $item = $issue->media()->with('file')->findOrFail($media);
        $file = $item->file;
        $item->delete();
        $storage->delete($file);
        AuditLog::record($request->user(), 'project_issue.media_deleted', $issue, "ลบรูปจากรายการปัญหา {$issue->title}");

        return back()->with('success', 'ลบรูปปัญหาหน้างานเรียบร้อยแล้ว');
    }

    private function validatedData(Request $request, Project $project, bool $updating = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'priority' => ['required', Rule::in(array_keys(ProjectIssue::PRIORITY_LABELS))],
            'status' => [$updating ? 'required' : 'nullable', Rule::in(array_keys(ProjectIssue::STATUS_LABELS))],
            'project_step_id' => ['nullable', Rule::exists('project_steps', 'id')->where('project_id', $project->id)],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['admin', 'inspector']))],
            'customer_visible' => ['nullable', 'boolean'],
            'due_date' => ['nullable', 'date'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['image', 'max:20480'],
        ]);
    }

    private function authorizeIssue(Request $request, Project $project, ProjectIssue $issue): void
    {
        abort_unless($issue->project_id === $project->id, 404);
        abort_unless($project->canBeManagedBy($request->user()), 403);
    }
}
