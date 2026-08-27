<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleCommentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $allowedStatuses = array_keys(ArticleComment::STATUS_LABELS);

        $comments = ArticleComment::query()
            ->with(['moderator:id,name', 'replier:id,name'])
            ->when(
                in_array($status, $allowedStatuses, true),
                fn ($query) => $query->where('status', $status),
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('article_title', 'like', "%{$search}%")
                        ->orWhere('article_slug', 'like', "%{$search}%")
                        ->orWhere('author_name', 'like', "%{$search}%")
                        ->orWhere('author_email', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = collect($allowedStatuses)
            ->mapWithKeys(fn (string $key): array => [$key => ArticleComment::query()->where('status', $key)->count()])
            ->all();

        return view('admin.comments.index', [
            'comments' => $comments,
            'counts' => $counts,
            'search' => $search,
            'status' => $status,
            'statusLabels' => ArticleComment::STATUS_LABELS,
        ]);
    }

    public function updateStatus(Request $request, ArticleComment $comment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(ArticleComment::STATUS_LABELS))],
        ]);

        $status = $validated['status'];
        $comment->update([
            'status' => $status,
            'approved_at' => $status === ArticleComment::STATUS_APPROVED
                ? ($comment->approved_at ?? now())
                : null,
            'moderated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'อัปเดตสถานะความคิดเห็นแล้ว');
    }

    public function reply(Request $request, ArticleComment $comment): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => ['nullable', 'string', 'max:2000'],
        ], [
            'admin_reply.max' => 'คำตอบต้องไม่เกิน 2,000 ตัวอักษร',
        ]);

        $reply = trim((string) ($validated['admin_reply'] ?? ''));
        $comment->update([
            'admin_reply' => $reply !== '' ? $reply : null,
            'replied_at' => $reply !== '' ? now() : null,
            'replied_by' => $reply !== '' ? $request->user()->id : null,
        ]);

        return back()->with('success', $reply !== '' ? 'บันทึกคำตอบแล้ว' : 'ลบคำตอบแล้ว');
    }

    public function destroy(ArticleComment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'ลบความคิดเห็นแล้ว');
    }
}
