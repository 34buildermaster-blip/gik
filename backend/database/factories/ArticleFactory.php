<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(6, true),
            'cover_image' => null,
            'status' => 'draft',
            'published_at' => null,
            'seo_title' => $title,
            'seo_description' => fake()->sentence(18),
            'seo_keywords' => 'ออกแบบบ้าน, รีโนเวทบ้าน, บิวท์อิน',
        ];
    }
}
