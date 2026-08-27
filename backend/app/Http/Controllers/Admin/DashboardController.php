<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
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

            return view('admin.inspector-dashboard', [
                'totalProjects' => (clone $projects)->count(),
                'activeProjects' => (clone $projects)->where('status', 'in_progress')->count(),
                'attentionProjects' => (clone $projects)->whereHas('steps', fn ($query) => $query->where('status', 'needs_attention'))->count(),
                'pendingReviewCount' => ProjectUpdate::query()
                    ->where('created_by', $request->user()->id)
                    ->where('status', 'pending_review')
                    ->count(),
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

        return view('admin.dashboard', [
            'projectCount' => $projectCount,
            'activeProjectCount' => $activeProjectCount,
            'completedProjectCount' => $completedProjectCount,
            'averageProgress' => $averageProgress,
            'updatesThisWeek' => $updatesThisWeek,
            'userCount' => $userCount,
            'inspectorCount' => $inspectorCount,
            'customerCount' => $customerCount,
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
