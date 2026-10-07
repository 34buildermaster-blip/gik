<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\ProjectIssue;
use App\Models\ProjectStep;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($request->user()->isInspector()) {
            $projects = Project::query()->where('manager_id', $request->user()->id);
            $projectIds = (clone $projects)->pluck('id');

            return view('admin.inspector-dashboard', [
                'totalProjects' => (clone $projects)->count(),
                'activeProjects' => (clone $projects)->where('status', 'in_progress')->count(),
                'attentionProjects' => (clone $projects)->whereHas('steps', fn ($query) => $query->where('status', 'needs_attention'))->count(),
                'pendingReviewCount' => ProjectUpdate::query()
                    ->where('created_by', $request->user()->id)
                    ->where('status', 'pending_review')
                    ->count(),
                'overdueStepCount' => ProjectStep::whereIn('project_id', $projectIds)
                    ->where('progress_percent', '<', 100)
                    ->whereDate('planned_end_date', '<', today())
                    ->count(),
                'openIssueCount' => ProjectIssue::whereIn('project_id', $projectIds)
                    ->where('status', '<>', 'resolved')
                    ->count(),
                'upcomingEvents' => ProjectEvent::whereIn('project_id', $projectIds)
                    ->where('status', 'scheduled')
                    ->where('starts_at', '>=', now())
                    ->with('project:id,code,name')
                    ->orderBy('starts_at')
                    ->limit(5)
                    ->get(),
                'assignedProjects' => (clone $projects)
                    ->with(['customers:id,name'])
                    ->withCount('updates')
                    ->latest('updated_at')
                    ->limit(6)
                    ->get(),
                'statusLabels' => Project::STATUS_LABELS,
            ]);
        }

        $articleCount = Article::count();
        $publishedCount = Article::where('status', 'published')->count();
        $draftCount = Article::where('status', 'draft')->count();
        $seoReadyCount = Article::query()
            ->whereNotNull('seo_title')
            ->where('seo_title', '<>', '')
            ->whereNotNull('seo_description')
            ->where('seo_description', '<>', '')
            ->count();
        $projectCount = Project::count();
        $activeProjectCount = Project::where('status', 'in_progress')->count();
        $completedProjectCount = Project::where('status', 'completed')->count();
        $averageProgress = $projectCount > 0 ? (int) round((float) Project::avg('progress_percent')) : 0;
        $updatesThisWeek = ProjectUpdate::where('work_performed_at', '>=', now()->subDays(7))->count();
        $userCount = User::count();
        $inspectorCount = User::where('role', 'inspector')->count();
        $customerCount = User::where('role', 'user')->count();
        $overdueStepCount = ProjectStep::whereHas('project')
            ->where('progress_percent', '<', 100)
            ->whereDate('planned_end_date', '<', today())
            ->count();
        $overdueProjectCount = Project::where('status', '<>', 'completed')
            ->whereDate('estimated_end_date', '<', today())
            ->count();
        $openIssueCount = ProjectIssue::where('status', '<>', 'resolved')->count();
        $urgentIssueCount = ProjectIssue::where('status', '<>', 'resolved')->where('priority', 'urgent')->count();
        $upcomingEvents = ProjectEvent::query()
            ->where('status', 'scheduled')
            ->where('starts_at', '>=', now())
            ->with(['project:id,code,name', 'assignee:id,name'])
            ->orderBy('starts_at')
            ->limit(6)
            ->get();
        $statusCounts = collect(array_keys(Project::STATUS_LABELS))
            ->mapWithKeys(fn (string $status) => [$status => Project::where('status', $status)->count()]);
        $managerWorkload = User::query()
            ->whereIn('role', ['admin', 'inspector'])
            ->withCount(['managedProjects as active_projects_count' => fn ($query) => $query->whereIn('status', ['preparing', 'in_progress', 'on_hold'])])
            ->get(['id', 'name', 'role'])
            ->filter(fn (User $user) => $user->active_projects_count > 0)
            ->sortByDesc('active_projects_count')
            ->take(6)
            ->values();

        return view('admin.dashboard', [
            'projectCount' => $projectCount,
            'activeProjectCount' => $activeProjectCount,
            'completedProjectCount' => $completedProjectCount,
            'averageProgress' => $averageProgress,
            'updatesThisWeek' => $updatesThisWeek,
            'userCount' => $userCount,
            'inspectorCount' => $inspectorCount,
            'customerCount' => $customerCount,
            'overdueStepCount' => $overdueStepCount,
            'overdueProjectCount' => $overdueProjectCount,
            'openIssueCount' => $openIssueCount,
            'urgentIssueCount' => $urgentIssueCount,
            'upcomingEvents' => $upcomingEvents,
            'statusCounts' => $statusCounts,
            'managerWorkload' => $managerWorkload,
            'attentionProjectCount' => Project::whereHas('steps', fn ($query) => $query->where('status', 'needs_attention'))->count(),
            'unassignedProjectCount' => Project::whereNull('manager_id')->where('status', '!=', 'completed')->count(),
            'draftUpdateCount' => ProjectUpdate::where('status', 'draft')->count(),
            'pendingReviewCount' => ProjectUpdate::where('status', 'pending_review')->count(),
            'latestProjects' => Project::query()
                ->with(['manager:id,name', 'customers:id,name'])
                ->withCount('updates')
                ->whereIn('status', ['preparing', 'in_progress', 'on_hold'])
                ->latest('updated_at')
                ->limit(5)
                ->get(),
            'latestProjectUpdates' => ProjectUpdate::query()
                ->whereHas('project')
                ->with(['project:id,code,name', 'creator:id,name'])
                ->latest('work_performed_at')
                ->limit(5)
                ->get(),
            'projectStatusLabels' => Project::STATUS_LABELS,
            'projectStageLabels' => ProjectUpdate::STAGE_LABELS,
            'articleCount' => $articleCount,
            'publishedCount' => $publishedCount,
            'draftCount' => $draftCount,
            'seoReadyCount' => $seoReadyCount,
            'publishPercent' => $articleCount > 0 ? (int) round(($publishedCount / $articleCount) * 100) : 0,
            'recentlyUpdatedCount' => Article::where('updated_at', '>=', now()->subDays(7))->count(),
            'latestArticles' => Article::with('user')->latest('updated_at')->limit(4)->get(),
        ]);
    }
}
