<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleCommentController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $comments = ArticleComment::query()
            ->approved()
            ->where('article_slug', $slug)
            ->oldest('approved_at')
            ->oldest('id')
            ->get()
            ->map(fn (ArticleComment $comment): array => $this->publicPayload($comment));

        return response()->json([
            'data' => $comments,
            'meta' => ['count' => $comments->count()],
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        abort_if(Str::length($slug) > 255 || str_contains($slug, '/'), 404);

        $validated = $request->validate([
            'article_title' => ['required', 'string', 'max:255'],
            'author_name' => ['required', 'string', 'min:2', 'max:100'],
            'author_email' => ['nullable', 'email:rfc', 'max:255'],
            'body' => ['required', 'string', 'min:3', 'max:2000'],
            'website' => ['nullable', 'string', 'max:0'],
        ], [
            'author_name.required' => 'กรุณาระบุชื่อ',
            'author_name.min' => 'ชื่อต้องมีอย่างน้อย 2 ตัวอักษร',
            'author_email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'body.required' => 'กรุณาเขียนความคิดเห็น',
            'body.min' => 'ความคิดเห็นต้องมีอย่างน้อย 3 ตัวอักษร',
            'body.max' => 'ความคิดเห็นต้องไม่เกิน 2,000 ตัวอักษร',
        ]);

        ArticleComment::create([
            'article_slug' => $slug,
            'article_title' => $validated['article_title'],
            'author_name' => trim($validated['author_name']),
            'author_email' => isset($validated['author_email']) ? mb_strtolower(trim($validated['author_email'])) : null,
            'body' => trim($validated['body']),
            'status' => ArticleComment::STATUS_PENDING,
            'ip_hash' => $request->ip()
                ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                : null,
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return response()->json([
            'message' => 'ส่งความคิดเห็นแล้ว ทีมงานจะตรวจสอบก่อนเผยแพร่',
        ], 202);
    }

    private function publicPayload(ArticleComment $comment): array
    {
        return [
            'id' => $comment->id,
            'authorName' => $comment->author_name,
            'body' => $comment->body,
            'createdAt' => $comment->created_at?->toIso8601String(),
            'adminReply' => $comment->admin_reply,
            'repliedAt' => $comment->replied_at?->toIso8601String(),
        ];
    }
}
