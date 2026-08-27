<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\StoredFile;
use App\Services\ArticleHtmlSanitizer;
use App\Services\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        private readonly MediaStorage $mediaStorage,
        private readonly ArticleHtmlSanitizer $htmlSanitizer,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->toString();

        return view('admin.articles.index', [
            'articles' => Article::query()
                ->with('coverFile')
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
        $article = new Article(['status' => 'draft']);

        return view('admin.articles.create', [
            'article' => $article,
            'editorContent' => $this->cleanContent((string) old('content', '')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['content'] = $this->cleanContent($data['content']);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $cover = $this->uploadCoverImage($request);
        $data['cover_file_id'] = $cover?->id;
        $data['cover_image'] = null;
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
            'editorContent' => $this->cleanContent((string) old('content', $article->content)),
        ]);
    }

    public function preview(Article $article): View
    {
        $plainContent = Str::of(strip_tags($article->content))->squish()->toString();
        $characterCount = mb_strlen($plainContent);

        return view('admin.articles.preview', [
            'article' => $article,
            'characterCount' => $characterCount,
            'sanitizedContent' => $this->cleanContent((string) $article->content),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['content'] = $this->cleanContent($data['content']);
        $data['slug'] = $this->uniqueSlug($data['title'], $article);
        $oldCover = null;
        $oldCoverPath = null;

        if ($request->hasFile('cover_image')) {
            $oldCover = $article->coverFile;
            $oldCoverPath = $article->cover_image;
            $newCover = $this->uploadCoverImage($request);
            $data['cover_file_id'] = $newCover?->id;
            $data['cover_image'] = null;
        }

        if ($data['status'] === 'published' && $article->published_at === null) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        $article->update($data);

        if ($oldCover) {
            $this->mediaStorage->delete($oldCover);
        } elseif ($oldCoverPath) {
            File::delete(public_path($oldCoverPath));
        }

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
                'max:512000',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime',
            ],
        ]);

        $uploadedFile = $request->file('media');
        $file = $this->mediaStorage->store(
            $uploadedFile,
            'article-media',
            'public',
            $request->user(),
        );

        return response()->json([
            'url' => $file->publicUrl(),
            'path' => 'media:'.$file->uuid,
            'type' => Str::startsWith($file->mime_type, 'video/') ? 'video' : 'image',
            'mimeType' => $file->mime_type,
            'name' => pathinfo($file->original_name, PATHINFO_FILENAME) ?: 'media',
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
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
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
        return $this->htmlSanitizer->sanitize($content);
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

    private function uploadCoverImage(Request $request): ?StoredFile
    {
        if (! $request->hasFile('cover_image')) {
            return null;
        }

        return $this->mediaStorage->store(
            $request->file('cover_image'),
            'article-covers',
            'public',
            $request->user(),
        );
    }

    private function deleteCoverImage(Article $article): void
    {
        if ($article->coverFile) {
            $this->mediaStorage->delete($article->coverFile);
        } elseif ($article->cover_image) {
            File::delete(public_path($article->cover_image));
        }
    }
}
