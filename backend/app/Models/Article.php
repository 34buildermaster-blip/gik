<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'cover_image',
    'cover_file_id',
    'status',
    'published_at',
    'seo_title',
    'seo_description',
    'seo_keywords',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'cover_file_id');
    }

    public function coverUrl(): ?string
    {
        if ($this->coverFile) {
            return $this->coverFile->publicUrl();
        }

        return $this->cover_image ? url($this->cover_image) : null;
    }
}
