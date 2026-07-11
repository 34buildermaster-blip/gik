<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->toString();

        return view('admin.articles.index', [
            'articles' => Article::query()
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%");
                    });
                })
                ->when(in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
                ->latest('updated_at')
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
            'status' => $status,
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

    public function preview(Article $article): View
    {
        $plainContent = Str::of(strip_tags($article->content))->squish()->toString();
        $characterCount = mb_strlen($plainContent);

        return view('admin.articles.preview', [
            'article' => $article,
            'characterCount' => $characterCount,
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

    public function uploadMedia(Request $request): JsonResponse
    {
        $request->validate([
            'media' => [
                'required',
                'file',
                'max:102400',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime',
            ],
        ]);

        $file = $request->file('media');
        $directory = public_path('uploads/article-media');
        File::ensureDirectoryExists($directory);

        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $filename = now()->format('YmdHis').'-'.Str::slug($name).'-'.Str::random(8).'.'.$extension;
        $mimeType = $file->getMimeType() ?: '';
        $file->move($directory, $filename);

        $path = "uploads/article-media/{$filename}";

        return response()->json([
            'url' => url($path),
            'path' => $path,
            'type' => Str::startsWith($mimeType, 'video/') ? 'video' : 'image',
            'mimeType' => $mimeType,
            'name' => $name ?: 'media',
        ]);
    }

    public function importMarkdown(Request $request): JsonResponse
    {
        $request->validate([
            'markdown' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('markdown');
        $extension = Str::lower($file->getClientOriginalExtension());

        if (! in_array($extension, ['md', 'markdown', 'txt'], true)) {
            return response()->json([
                'message' => 'รองรับเฉพาะไฟล์ .md, .markdown หรือ .txt',
            ], 422);
        }

        $markdown = File::get($file->getRealPath());
        $title = $this->extractMarkdownTitle($markdown)
            ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $html = $this->cleanContent($this->normalizeMarkdownHtml(Str::markdown($markdown, [
            'allow_unsafe_links' => false,
            'html_input' => 'strip',
        ])));
        $excerpt = Str::of(strip_tags($html))
            ->squish()
            ->limit(240)
            ->toString();

        return response()->json([
            'title' => $title,
            'excerpt' => $excerpt,
            'html' => $html,
        ]);
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
        $allowedTags = '<p><br><strong><b><em><i><u><s><span><h2><h3><h4><ul><ol><li><blockquote><a><figure><figcaption><img><video><source><table><thead><tbody><tr><th><td><hr>';

        $content = strip_tags($content, $allowedTags);
        $content = preg_replace('/\s+on\w+="[^"]*"/i', '', $content) ?? $content;
        $content = preg_replace("/\s+on\w+='[^']*'/i", '', $content) ?? $content;
        $content = preg_replace('/javascript:/i', '', $content) ?? $content;

        return trim($content);
    }

    private function extractMarkdownTitle(string $markdown): ?string
    {
        if (preg_match('/^\s*#\s+(.+)$/m', $markdown, $matches) !== 1) {
            return null;
        }

        return Str::of($matches[1])
            ->replaceMatches('/[*_`~\[\]#]/', '')
            ->squish()
            ->limit(255, '')
            ->toString();
    }

    private function normalizeMarkdownHtml(string $html): string
    {
        $html = preg_replace('/<h1([^>]*)>/i', '<h2$1>', $html) ?? $html;
        $html = preg_replace('/<\/h1>/i', '</h2>', $html) ?? $html;

        return $html;
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
