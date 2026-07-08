<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(): JsonResponse
    {
        $articles = Article::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->latest()
            ->get()
            ->map(fn (Article $article): array => $this->toPayload($article));

        return $this->corsResponse(['data' => $articles]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->corsResponse(['data' => $this->toPayload($article)]);
    }

    private function toPayload(Article $article): array
    {
        $content = trim((string) $article->content);
        $wordCount = str_word_count(strip_tags($content));

        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'category' => 'Article',
            'date' => $this->thaiDate($article->published_at ?? $article->updated_at),
            'readTime' => max(1, (int) ceil($wordCount / 220)).' นาที',
            'image' => $article->cover_image ? url($article->cover_image) : null,
            'coverAlt' => $article->title,
            'highlights' => $this->makeHighlights($article),
            'content' => $this->makeContentSections($article),
            'contentHtml' => $this->prepareContentHtml($content),
            'seo' => [
                'title' => $article->seo_title ?: $article->title,
                'description' => $article->seo_description ?: $article->excerpt,
                'keywords' => $article->seo_keywords,
            ],
        ];
    }

    private function makeHighlights(Article $article): array
    {
        $source = $article->excerpt ?: $article->title;
        $parts = preg_split('/[,.!?ๆ]+/u', $source, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $highlights = array_values(array_filter(array_map(
            fn (string $part): string => trim(Str::limit($part, 58, '')),
            $parts,
        )));

        return array_slice($highlights ?: ['อ่านแนวทางสำคัญก่อนเริ่มงาน', 'เตรียมข้อมูลให้พร้อมก่อนคุยรายละเอียด', 'ช่วยให้วางแผนงบและงานได้ชัดขึ้น'], 0, 3);
    }

    private function makeContentSections(Article $article): array
    {
        $plainContent = trim(strip_tags((string) $article->content));
        $blocks = preg_split('/\R{2,}/u', $plainContent, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($blocks === []) {
            return [
                [
                    'heading' => $article->title,
                    'body' => $article->excerpt ?: 'รายละเอียดบทความจะถูกอัปเดตเพิ่มเติมเร็ว ๆ นี้',
                ],
            ];
        }

        return array_map(
            fn (string $body, int $index): array => [
                'heading' => $index === 0 ? 'ภาพรวม' : 'รายละเอียดเพิ่มเติม',
                'body' => trim($body),
            ],
            $blocks,
            array_keys($blocks),
        );
    }

    private function prepareContentHtml(string $content): string
    {
        if ($content === '') {
            return '';
        }

        if ($content !== strip_tags($content)) {
            return $content;
        }

        $paragraphs = preg_split('/\R{2,}/u', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($paragraphs)
            ->map(fn (string $paragraph): string => '<p>'.e(trim($paragraph)).'</p>')
            ->implode('');
    }

    private function thaiDate($date): string
    {
        if (! $date) {
            return '';
        }

        $months = [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม',
        ];

        return $date->format('j').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }

    private function corsResponse(array $payload): JsonResponse
    {
        return response()
            ->json($payload)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }
}
