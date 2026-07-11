<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $articleCount = Article::count();
        $publishedCount = Article::where('status', 'published')->count();
        $draftCount = Article::where('status', 'draft')->count();
        $seoReadyCount = Article::query()
            ->whereNotNull('seo_title')
            ->where('seo_title', '<>', '')
            ->whereNotNull('seo_description')
            ->where('seo_description', '<>', '')
            ->count();
        $dayLabels = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
        $weeklyActivity = collect(range(6, 0))
            ->map(function (int $daysAgo) use ($dayLabels): array {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => $dayLabels[$date->dayOfWeek],
                    'count' => Article::whereDate('updated_at', $date)->count(),
                ];
            });
        $maxActivity = max(1, (int) $weeklyActivity->max('count'));
        $weeklyActivity = $weeklyActivity->map(fn (array $day): array => [
            ...$day,
            'height' => $day['count'] === 0
                ? 14
                : 28 + (int) round(($day['count'] / $maxActivity) * 72),
        ]);

        return view('admin.dashboard', [
            'articleCount' => $articleCount,
            'publishedCount' => $publishedCount,
            'draftCount' => $draftCount,
            'seoReadyCount' => $seoReadyCount,
            'publishPercent' => $articleCount > 0 ? (int) round(($publishedCount / $articleCount) * 100) : 0,
            'recentlyUpdatedCount' => Article::where('updated_at', '>=', now()->subDays(7))->count(),
            'weeklyActivity' => $weeklyActivity,
            'latestArticles' => Article::with('user')->latest('updated_at')->limit(5)->get(),
        ]);
    }
}
