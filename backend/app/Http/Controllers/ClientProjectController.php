<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectIssue;
use App\Models\ProjectStep;
use App\Models\ProjectUpdate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $projects = $user->projects()
            ->with(['manager:id,name', 'updates' => fn ($query) => $query->where('status', 'published')->with('media')->limit(1)])
            ->withCount(['updates as published_updates_count' => fn ($query) => $query->where('status', 'published')])
            ->withCount(['updates as unread_updates_count' => fn ($query) => $query
                ->where('status', 'published')
                ->whereDoesntHave('readers', fn ($query) => $query->where('users.id', $user->id))])
            ->latest('projects.updated_at')
            ->get();

        return view('client.projects.index', [
            'projects' => $projects,
            'statusLabels' => Project::STATUS_LABELS,
            'typeLabels' => Project::TYPE_LABELS,
        ]);
    }

    public function show(Request $request, Project $project): View
    {
        $user = $request->user();
        abort_unless($project->customers()->where('users.id', $user->id)->exists(), 403);

        $project->load([
            'manager:id,name',
            'steps',
            'updates' => fn ($query) => $query->where('status', 'published')->with(['media', 'creator:id,name', 'projectStep:id,name']),
            'documents' => fn ($query) => $query->where('visibility', 'customer')->with(['file', 'uploader:id,name']),
            'issues' => fn ($query) => $query->where('customer_visible', true)->with(['media.file', 'projectStep:id,name', 'assignee:id,name']),
        ]);

        $unreadIds = $project->updates()
            ->where('status', 'published')
            ->whereDoesntHave('readers', fn ($query) => $query->where('users.id', $user->id))
            ->pluck('id');

        foreach ($unreadIds as $updateId) {
            $user->projectUpdatesRead()->syncWithoutDetaching([$updateId => ['read_at' => now()]]);
        }

        return view('client.projects.show', [
            'project' => $project,
            'statusLabels' => Project::STATUS_LABELS,
            'typeLabels' => Project::TYPE_LABELS,
            'stageLabels' => ProjectUpdate::STAGE_LABELS,
            'stepStatusLabels' => ProjectStep::STATUS_LABELS,
            'inspectionLabels' => ProjectStep::INSPECTION_LABELS,
            'documentCategoryLabels' => ProjectDocument::CATEGORY_LABELS,
            'issueStatusLabels' => ProjectIssue::STATUS_LABELS,
            'issuePriorityLabels' => ProjectIssue::PRIORITY_LABELS,
        ]);
    }
}
