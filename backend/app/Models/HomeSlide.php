<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'section',
    'stored_file_id',
    'image_path',
    'eyebrow',
    'title',
    'title_line_2',
    'description',
    'label',
    'alt_text',
    'sort_order',
    'is_active',
])]
class HomeSlide extends Model
{
    public const SECTION_HERO = 'hero';

    public const SECTION_APPROACH = 'approach';

    public const SECTION_LABELS = [
        self::SECTION_HERO => 'Hero Slider',
        self::SECTION_APPROACH => 'About Our Approach',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function storedFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class);
    }

    public function imageUrl(): ?string
    {
        return $this->storedFile?->publicUrl() ?: $this->image_path;
    }

    public function previewUrl(): ?string
    {
        if ($this->storedFile) {
            return $this->storedFile->publicUrl();
        }

        return $this->image_path
            ? rtrim((string) config('app.frontend_url'), '/').'/'.ltrim($this->image_path, '/')
            : null;
    }
}
