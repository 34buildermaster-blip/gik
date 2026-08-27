<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleFormattingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_inline_font_formatting_in_article_content(): void
    {
        $admin = User::factory()->admin()->create();
        $content = '<p><span style="font-family: Georgia, serif">Formatted article text</span></p>';

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Article font formatting',
                'excerpt' => 'Formatting test',
                'content' => $content,
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article = Article::query()->sole();

        $this->assertSame($content, $article->content);
    }

    public function test_article_content_uses_an_allowlist_and_blocks_xss_payloads(): void
    {
        $admin = User::factory()->admin()->create();
        $content = <<<'HTML'
<p onclick=alert(1) style="font-size: 20px; background-image: url(javascript:alert(1)); color: #053920">Safe text</p>
<a href="java&#x09;script:alert(1)" target="_blank">unsafe link</a>
<img src="x" onerror=alert(1)><script>alert(1)</script>
HTML;

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Sanitized article',
                'excerpt' => 'Security test',
                'content' => $content,
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.articles.index'));

        $stored = Article::query()->sole()->content;

        $this->assertStringContainsString('Safe text', $stored);
        $this->assertStringContainsString('font-size: 20px', $stored);
        $this->assertStringContainsString('color: #053920', $stored);
        $this->assertStringNotContainsString('onclick', $stored);
        $this->assertStringNotContainsString('onerror', $stored);
        $this->assertStringNotContainsString('javascript', strtolower($stored));
        $this->assertStringNotContainsString('<script', strtolower($stored));
        $this->assertStringNotContainsString('background-image', strtolower($stored));
    }

    public function test_api_sanitizes_legacy_article_html_before_returning_it(): void
    {
        Article::create([
            'user_id' => User::factory()->admin()->create()->id,
            'title' => 'Legacy content',
            'slug' => 'legacy-content',
            'content' => '<p>Visible</p><img src="x" onerror="alert(1)"><script>alert(1)</script>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $content = $this->getJson('/api/articles/legacy-content')
            ->assertOk()
            ->json('data.contentHtml');

        $this->assertStringContainsString('Visible', $content);
        $this->assertStringNotContainsString('onerror', $content);
        $this->assertStringNotContainsString('<script', $content);
    }
}
