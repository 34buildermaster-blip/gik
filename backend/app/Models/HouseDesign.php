<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'cover_file_id',
    'cover_image',
    'name',
    'title',
    'slug',
    'style',
    'budget_category',
    'budget_label',
    'area',
    'bedrooms',
    'bathrooms',
    'floors',
    'parking_spaces',
    'description',
    'concept',
    'features',
    'cover_alt',
    'status',
    'sort_order',
    'seo_title',
    'seo_description',
    'published_at',
])]
class HouseDesign extends Model
{
    public const STYLE_LABELS = [
        'modern' => 'โมเดิร์น',
        'minimal' => 'มินิมอล',
        'contemporary' => 'ร่วมสมัย',
        'classic' => 'คลาสสิก',
    ];

    public const BUDGET_LABELS = [
        'under-5' => 'ต่ำกว่า 5 ล้านบาท',
        '5-10' => '5 - 10 ล้านบาท',
        'over-10' => 'มากกว่า 10 ล้านบาท',
    ];

    public const STATUS_LABELS = [
        'draft' => 'ฉบับร่าง',
        'published' => 'เผยแพร่แล้ว',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'area' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'floors' => 'integer',
            'parking_spaces' => 'integer',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'cover_file_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(HouseDesignImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverUrl(): ?string
    {
        return $this->coverFile?->publicUrl() ?: $this->cover_image;
    }

    public function previewCoverUrl(): ?string
    {
        if ($this->coverFile) {
            return $this->coverFile->publicUrl();
        }

        return $this->cover_image
            ? rtrim((string) config('app.frontend_url'), '/').'/'.ltrim($this->cover_image, '/')
            : null;
    }
}
