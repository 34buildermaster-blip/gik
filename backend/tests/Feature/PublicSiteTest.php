<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\HouseDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_public_pages_render_with_shared_navigation(): void
    {
        foreach (['/', '/about', '/services', '/house-designs', '/updates', '/blog', '/faq', '/contact'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('BUILD MASTER')
                ->assertSee('/css/site.min.css', false)
                ->assertSee('/js/site.js', false);
        }

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);
    }

    public function test_only_published_house_designs_are_public(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        HouseDesign::query()->create([
            'user_id' => $user->id,
            'name' => 'Modern One',
            'title' => 'บ้านโมเดิร์นวัน',
            'slug' => 'modern-one',
            'style' => 'modern',
            'budget_category' => '5-10',
            'budget_label' => '5 - 10 ล้านบาท',
            'area' => 220,
            'description' => 'บ้านสำหรับการทดสอบ',
            'cover_alt' => 'บ้านโมเดิร์นวัน',
            'status' => 'published',
            'published_at' => now(),
        ]);
        HouseDesign::query()->create([
            'user_id' => $user->id,
            'name' => 'Secret Draft',
            'title' => 'แบบที่ยังไม่เผยแพร่',
            'slug' => 'secret-draft',
            'style' => 'modern',
            'budget_category' => 'under-5',
            'budget_label' => 'ต่ำกว่า 5 ล้านบาท',
            'area' => 180,
            'description' => 'ฉบับร่าง',
            'cover_alt' => 'แบบที่ยังไม่เผยแพร่',
            'status' => 'draft',
        ]);

        $this->get('/house-designs')->assertOk()->assertSee('บ้านโมเดิร์นวัน')->assertDontSee('แบบที่ยังไม่เผยแพร่');
        $this->get('/house-designs/modern-one')->assertOk()->assertSee('บ้านโมเดิร์นวัน');
        $this->get('/house-designs/secret-draft')->assertNotFound();
    }

    public function test_only_published_articles_are_public(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Article::query()->create([
            'user_id' => $user->id,
            'title' => 'บทความที่เผยแพร่',
            'slug' => 'public-article',
            'excerpt' => 'ข้อมูลสำหรับทดสอบ',
            'content' => '<p>เนื้อหาที่ปลอดภัย</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Article::query()->create([
            'user_id' => $user->id,
            'title' => 'บทความฉบับร่าง',
            'slug' => 'draft-article',
            'content' => 'ยังไม่เผยแพร่',
            'status' => 'draft',
        ]);

        $this->get('/blog')->assertOk()->assertSee('บทความที่เผยแพร่')->assertDontSee('บทความฉบับร่าง');
        $this->get('/blog/public-article')->assertOk()->assertSee('เนื้อหาที่ปลอดภัย');
        $this->get('/blog/draft-article')->assertNotFound();
    }
}
