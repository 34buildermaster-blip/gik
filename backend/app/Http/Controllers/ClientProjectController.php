<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAcknowledgement;
use App\Models\ProjectDocument;
use App\Models\ProjectInquiry;
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
        $projectIds = $user->projects()->pluck('projects.id');
        $projects = $user->projects()
            ->with(['manager:id,name', 'updates' => fn ($query) => $query->where('status', 'published')->with('media')->limit(1)])
            ->withCount(['updates as published_updates_count' => fn ($query) => $query->where('status', 'published')])
            ->withCount(['updates as unread_updates_count' => fn ($query) => $query
                ->where('status', 'published')
                ->whereDoesntHave('readers', fn ($query) => $query->where('users.id', $user->id))])
            ->latest('projects.updated_at')
            ->get();

        $acknowledgedDocuments = ProjectAcknowledgement::query()
            ->where('user_id', $user->id)->where('target_type', 'document')->pluck('target_id');
        $acknowledgedUpdates = ProjectAcknowledgement::query()
            ->where('user_id', $user->id)->where('target_type', 'update')->pluck('target_id');
        $pendingDocumentsQuery = ProjectDocument::query()
            ->whereIn('project_id', $projectIds)->where('visibility', 'customer')
            ->where('requires_acknowledgement', true)->whereNotIn('id', $acknowledgedDocuments);
        $pendingUpdatesQuery = ProjectUpdate::query()
            ->whereIn('project_id', $projectIds)->where('status', 'published')
            ->where('requires_acknowledgement', true)->whereNotIn('id', $acknowledgedUpdates);
        $pendingAcknowledgements = (clone $pendingDocumentsQuery)->count() + (clone $pendingUpdatesQuery)->count();
        $answeredInquiry = ProjectInquiry::query()
            ->where('customer_id', $user->id)
            ->where('status', 'answered')
            ->latest('last_message_at')
            ->first(['id', 'project_id']);

        return view('client.projects.index', [
            'projects' => $projects,
            'statusLabels' => Project::STATUS_LABELS,
            'typeLabels' => Project::TYPE_LABELS,
            'taskCenter' => [
                'profile_incomplete' => blank($user->phone) || blank($user->address),
                'line_incomplete' => blank($user->line_recipient_id),
                'unread_updates' => (int) $projects->sum('unread_updates_count'),
                'unread_project_id' => $projects->firstWhere('unread_updates_count', '>', 0)?->id,
                'pending_acknowledgements' => $pendingAcknowledgements,
                'acknowledgement_project_id' => (clone $pendingDocumentsQuery)->value('project_id')
                    ?? (clone $pendingUpdatesQuery)->value('project_id'),
                'acknowledgement_anchor' => (clone $pendingDocumentsQuery)->exists()
                    ? 'client-project-documents'
                    : 'client-project-updates',
                'answered_inquiries' => ProjectInquiry::query()
                    ->where('customer_id', $user->id)->where('status', 'answered')->count(),
                'inquiry_project_id' => $answeredInquiry?->project_id,
                'inquiry_id' => $answeredInquiry?->id,
            ],
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
            'inquiries' => fn ($query) => $query
                ->where('customer_id', $user->id)
                ->with(['customer:id,name', 'projectUpdate:id,title', 'messages.sender:id,name,role', 'messages.attachments.file'])
                ->latest('last_message_at'),
        ]);

        $unreadIds = $project->updates()
            ->where('status', 'published')
            ->whereDoesntHave('readers', fn ($query) => $query->where('users.id', $user->id))
            ->pluck('id');

        foreach ($unreadIds as $updateId) {
            $user->projectUpdatesRead()->syncWithoutDetaching([$updateId => ['read_at' => now()]]);
        }

        $acknowledgements = ProjectAcknowledgement::query()
            ->where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->get()
            ->groupBy('target_type')
            ->map(fn ($items) => $items->pluck('target_id')->all());

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
            'inquiryStatusLabels' => ProjectInquiry::STATUS_LABELS,
            'acknowledgedDocumentIds' => $acknowledgements->get('document', []),
            'acknowledgedUpdateIds' => $acknowledgements->get('update', []),
            'nextStep' => $project->steps->first(fn (ProjectStep $step) => $step->progress_percent < 100),
        ]);
    }
}
