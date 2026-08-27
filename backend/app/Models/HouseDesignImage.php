<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'house_design_id',
    'stored_file_id',
    'image_path',
    'alt_text',
    'caption',
    'sort_order',
])]
class HouseDesignImage extends Model
{
    public function houseDesign(): BelongsTo
    {
        return $this->belongsTo(HouseDesign::class);
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
