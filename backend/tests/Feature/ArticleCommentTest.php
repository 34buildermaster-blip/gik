<?php

namespace Tests\Feature;

use App\Models\ArticleComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_reader_can_submit_a_comment_for_moderation(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->postJson('/api/articles/home-renovation-planning/comments', [
                'article_title' => 'วางแผนรีโนเวทบ้านอย่างไร',
                'author_name' => 'สมชาย ใจดี',
                'author_email' => 'SOMCHAI@example.com',
                'body' => 'บทความนี้ช่วยให้วางแผนงบประมาณได้ชัดเจนมากครับ',
                'website' => '',
            ])
            ->assertAccepted()
            ->assertJsonPath('message', 'ส่งความคิดเห็นแล้ว ทีมงานจะตรวจสอบก่อนเผยแพร่');

        $comment = ArticleComment::query()->firstOrFail();

        $this->assertSame(ArticleComment::STATUS_PENDING, $comment->status);
        $this->assertSame('somchai@example.com', $comment->author_email);
        $this->assertNotSame('203.0.113.10', $comment->ip_hash);
        $this->assertSame(64, strlen((string) $comment->ip_hash));
    }

    public function test_public_api_returns_only_approved_comments_without_private_data(): void
    {
        ArticleComment::create($this->commentData([
            'author_name' => 'ผู้แสดงความคิดเห็นที่อนุมัติ',
            'status' => ArticleComment::STATUS_APPROVED,
            'approved_at' => now(),
            'admin_reply' => 'ขอบคุณสำหรับความคิดเห็นครับ',
            'replied_at' => now(),
        ]));

        ArticleComment::create($this->commentData([
            'author_name' => 'ข้อความที่ยังรอตรวจ',
            'status' => ArticleComment::STATUS_PENDING,
        ]));

        $this->getJson('/api/articles/home-renovation-planning/comments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.authorName', 'ผู้แสดงความคิดเห็นที่อนุมัติ')
            ->assertJsonPath('data.0.adminReply', 'ขอบคุณสำหรับความคิดเห็นครับ')
            ->assertJsonMissingPath('data.0.author_email')
            ->assertJsonMissingPath('data.0.ip_hash')
            ->assertJsonMissing(['authorName' => 'ข้อความที่ยังรอตรวจ']);
    }

    public function test_admin_can_approve_reply_to_and_delete_a_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = ArticleComment::create($this->commentData());

        $this->actingAs($admin)
            ->put(route('admin.comments.status', $comment), ['status' => ArticleComment::STATUS_APPROVED])
            ->assertSessionHasNoErrors();

        $comment->refresh();
        $this->assertSame(ArticleComment::STATUS_APPROVED, $comment->status);
        $this->assertNotNull($comment->approved_at);
        $this->assertSame($admin->id, $comment->moderated_by);

        $this->actingAs($admin)
            ->put(route('admin.comments.reply', $comment), ['admin_reply' => 'ทีมงานได้รับข้อมูลแล้วครับ'])
            ->assertSessionHasNoErrors();

        $comment->refresh();
        $this->assertSame('ทีมงานได้รับข้อมูลแล้วครับ', $comment->admin_reply);
        $this->assertSame($admin->id, $comment->replied_by);

        $this->actingAs($admin)
            ->delete(route('admin.comments.destroy', $comment))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('article_comments', ['id' => $comment->id]);
    }

    public function test_non_admin_cannot_open_comment_moderation(): void
    {
        $customer = User::factory()->create();
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($customer)->get(route('admin.comments.index'))->assertForbidden();
        $this->actingAs($inspector)->get(route('admin.comments.index'))->assertForbidden();
    }

    public function test_comment_submission_validates_content_and_honeypot(): void
    {
        $this->postJson('/api/articles/home-renovation-planning/comments', [
            'article_title' => 'บทความทดสอบ',
            'author_name' => 'ก',
            'body' => 'ดี',
            'website' => 'https://spam.example',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['author_name', 'body', 'website']);
    }

    private function commentData(array $overrides = []): array
    {
        return array_merge([
            'article_slug' => 'home-renovation-planning',
            'article_title' => 'วางแผนรีโนเวทบ้านอย่างไร',
            'author_name' => 'ลูกค้าทดสอบ',
            'author_email' => 'customer@example.com',
            'body' => 'ความคิดเห็นสำหรับใช้ทดสอบระบบ',
            'status' => ArticleComment::STATUS_PENDING,
            'ip_hash' => hash('sha256', 'test-ip'),
            'user_agent' => 'Feature test',
        ], $overrides);
    }
}
