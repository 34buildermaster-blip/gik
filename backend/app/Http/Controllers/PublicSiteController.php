<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\HomeSlide;
use App\Models\HouseDesign;
use App\Services\ArticleHtmlSanitizer;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function __construct(private readonly ArticleHtmlSanitizer $htmlSanitizer) {}

    public function home(): View
    {
        $slides = HomeSlide::query()
            ->with('storedFile')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('section');

        return view('site.home', [
            'heroSlides' => $this->slides($slides->get(HomeSlide::SECTION_HERO), 'hero_fallback'),
            'approachSlides' => $this->slides($slides->get(HomeSlide::SECTION_APPROACH), 'approach_fallback'),
            'services' => config('site_content.services'),
            'process' => config('site_content.process'),
            'brands' => config('site_content.brands'),
            'testimonials' => config('site_content.testimonials'),
        ]);
    }

    public function about(): View
    {
        return view('site.about', [
            'process' => config('site_content.process'),
            'testimonials' => config('site_content.testimonials'),
        ]);
    }

    public function services(): View
    {
        return view('site.services', ['services' => config('site_content.services')]);
    }

    public function houseDesigns(): View
    {
        $designs = HouseDesign::query()
            ->with('coverFile')
            ->published()
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get()
            ->map(fn (HouseDesign $design) => $this->housePayload($design));

        return view('site.house-designs.index', [
            'designs' => $designs,
            'styles' => HouseDesign::STYLE_LABELS,
            'budgets' => HouseDesign::BUDGET_LABELS,
        ]);
    }

    public function houseDesign(string $slug): View
    {
        $design = HouseDesign::query()
            ->with(['coverFile', 'images.storedFile'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.house-designs.show', ['design' => $this->housePayload($design, true)]);
    }

    public function updates(): View
    {
        return view('site.updates', ['updates' => config('site_content.updates')]);
    }

    public function blog(): View
    {
        $articles = Article::query()
            ->with('coverFile')
            ->where('status', 'published')
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->map(fn (Article $article) => $this->articlePayload($article));

        return view('site.blog.index', ['articles' => $articles]);
    }

    public function article(string $slug): View
    {
        $article = Article::query()
            ->with(['coverFile', 'user'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $comments = ArticleComment::query()
            ->approved()
            ->where('article_slug', $slug)
            ->oldest('approved_at')
            ->oldest('id')
            ->get();

        $related = Article::query()
            ->with('coverFile')
            ->where('status', 'published')
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Article $item) => $this->articlePayload($item));

        return view('site.blog.show', [
            'article' => $this->articlePayload($article, true),
            'comments' => $comments,
            'related' => $related,
        ]);
    }

    public function faq(): View
    {
        return view('site.faq', ['faqGroups' => config('site_content.faqs')]);
    }

    public function contact(): View
    {
        return view('site.contact');
    }

    public function sitemap(): Response
    {
        $urls = collect([
            ['location' => route('site.home'), 'modified' => null, 'priority' => '1.0'],
            ['location' => route('site.about'), 'modified' => null, 'priority' => '0.8'],
            ['location' => route('site.services'), 'modified' => null, 'priority' => '0.8'],
            ['location' => route('site.house-designs.index'), 'modified' => null, 'priority' => '0.9'],
            ['location' => route('site.updates'), 'modified' => null, 'priority' => '0.7'],
            ['location' => route('site.blog.index'), 'modified' => null, 'priority' => '0.8'],
            ['location' => route('site.faq'), 'modified' => null, 'priority' => '0.6'],
            ['location' => route('site.contact'), 'modified' => null, 'priority' => '0.7'],
        ]);

        HouseDesign::query()->published()->get(['slug', 'updated_at'])->each(
            fn (HouseDesign $design) => $urls->push([
                'location' => route('site.house-designs.show', $design->slug),
                'modified' => $design->updated_at?->toAtomString(),
                'priority' => '0.7',
            ]),
        );
        Article::query()->where('status', 'published')->get(['slug', 'updated_at'])->each(
            fn (Article $article) => $urls->push([
                'location' => route('site.blog.show', $article->slug),
                'modified' => $article->updated_at?->toAtomString(),
                'priority' => '0.6',
            ]),
        );

        $xml = view('site.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function slides(?Collection $slides, string $fallbackKey): Collection
    {
        if (! $slides || $slides->isEmpty()) {
            return collect(config("site_content.{$fallbackKey}"))->map(fn (array $slide, int $index) => [
                'id' => "fallback-{$fallbackKey}-{$index}",
                ...$slide,
                'image' => $this->mediaUrl($slide['image']),
            ]);
        }

        return $slides->map(fn (HomeSlide $slide) => [
            'id' => $slide->id,
            'image' => $this->mediaUrl($slide->imageUrl()),
            'alt' => $slide->alt_text,
            'eyebrow' => $slide->eyebrow,
            'title' => $slide->title,
            'title_line_2' => $slide->title_line_2,
            'description' => $slide->description,
            'label' => $slide->label,
        ])->values();
    }

    private function housePayload(HouseDesign $design, bool $detail = false): array
    {
        $payload = [
            'slug' => $design->slug,
            'name' => $design->name,
            'title' => $design->title,
            'style' => $design->style,
            'style_label' => HouseDesign::STYLE_LABELS[$design->style] ?? $design->style,
            'budget_category' => $design->budget_category,
            'budget_label' => $design->budget_label,
            'area' => $design->area,
            'bedrooms' => $design->bedrooms,
            'bathrooms' => $design->bathrooms,
            'floors' => $design->floors,
            'parking_spaces' => $design->parking_spaces,
            'description' => $design->description,
            'cover_image' => $this->mediaUrl($design->coverUrl(), '/approach-homes/modern.jpg'),
            'cover_alt' => $design->cover_alt ?: $design->title,
            'seo_title' => $design->seo_title ?: $design->title,
            'seo_description' => $design->seo_description ?: $design->description,
        ];

        if ($detail) {
            $payload['concept'] = $design->concept;
            $payload['features'] = $design->features ?: [];
            $payload['gallery'] = $design->images->map(fn ($image) => [
                'id' => $image->id,
                'image' => $this->mediaUrl($image->imageUrl()),
                'alt' => $image->alt_text ?: $design->title,
                'caption' => $image->caption,
            ])->values();
        }

        return $payload;
    }

    private function articlePayload(Article $article, bool $detail = false): array
    {
        $content = $this->htmlSanitizer->sanitize((string) $article->content);
        $plainText = trim(strip_tags($content));
        $wordCount = max(1, count(preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) ?: []));
        $payload = [
            'slug' => $article->slug,
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'date' => $this->thaiDate($article->published_at ?? $article->updated_at),
            'read_time' => max(1, (int) ceil($wordCount / 220)).' นาที',
            'image' => $this->mediaUrl($article->coverUrl(), '/bg-material-board.webp'),
            'cover_alt' => $article->title,
            'seo_title' => $article->seo_title ?: $article->title,
            'seo_description' => $article->seo_description ?: $article->excerpt,
        ];

        if ($detail) {
            $payload['content_html'] = $content !== strip_tags($content)
                ? $content
                : collect(preg_split('/\R{2,}/u', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [])
                    ->map(fn (string $paragraph) => '<p>'.e(trim($paragraph)).'</p>')
                    ->implode('');
            $payload['author'] = $article->user?->name ?: 'ทีม 34 Build Master';
        }

        return $payload;
    }

    private function mediaUrl(?string $value, ?string $fallback = null): ?string
    {
        $value = $value ?: $fallback;
        if (! $value) {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://']) ? $value : url('/'.ltrim($value, '/'));
    }

    private function thaiDate($value): string
    {
        if (! $value) {
            return '';
        }

        $months = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        return $value->format('j').' '.$months[(int) $value->format('n')].' '.((int) $value->format('Y') + 543);
    }
}
