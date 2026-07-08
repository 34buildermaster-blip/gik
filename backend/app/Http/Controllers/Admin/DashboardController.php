<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'articleCount' => Article::count(),
            'publishedCount' => Article::where('status', 'published')->count(),
            'draftCount' => Article::where('status', 'draft')->count(),
            'latestArticles' => Article::latest()->limit(5)->get(),
        ]);
    }
}
