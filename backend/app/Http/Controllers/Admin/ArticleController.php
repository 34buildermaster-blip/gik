<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('admin.articles.index', [
            'articles' => Article::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.create', [
            'article' => new Article(['status' => 'draft']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['content'] = $this->cleanContent($data['content']);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['cover_image'] = $this->uploadCoverImage($request);
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        Article::create($data);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'เพิ่มบทความเรียบร้อยแล้ว');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.edit', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['content'] = $this->cleanContent($data['content']);
        $data['slug'] = $this->uniqueSlug($data['title'], $article);

        if ($request->hasFile('cover_image')) {
            $this->deleteCoverImage($article);
            $data['cover_image'] = $this->uploadCoverImage($request);
        }

        if ($data['status'] === 'published' && $article->published_at === null) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        $article->update($data);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'แก้ไขบทความเรียบร้อยแล้ว');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->deleteCoverImage($article);
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'ลบบทความเรียบร้อยแล้ว');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', 'in:draft,published'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function uniqueSlug(string $title, ?Article $article = null): string
    {
        $baseSlug = Str::slug($title) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Article::where('slug', $slug)
                ->when($article, fn ($query) => $query->whereKeyNot($article->id))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function cleanContent(string $content): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><s><span><h2><h3><h4><ul><ol><li><blockquote><a><figure><figcaption><img><table><thead><tbody><tr><th><td><hr>';

        $content = strip_tags($content, $allowedTags);
        $content = preg_replace('/\s+on\w+="[^"]*"/i', '', $content) ?? $content;
        $content = preg_replace("/\s+on\w+='[^']*'/i", '', $content) ?? $content;
        $content = preg_replace('/javascript:/i', '', $content) ?? $content;

        return trim($content);
    }

    private function uploadCoverImage(Request $request): ?string
    {
        if (! $request->hasFile('cover_image')) {
            return null;
        }

        $directory = public_path('uploads/articles');
        File::ensureDirectoryExists($directory);

        $file = $request->file('cover_image');
        $filename = now()->format('YmdHis').'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return "uploads/articles/{$filename}";
    }

    private function deleteCoverImage(Article $article): void
    {
        if ($article->cover_image) {
            File::delete(public_path($article->cover_image));
        }
    }
}
