<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid',
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size',
    'sha256',
    'scan_status',
    'scanned_at',
    'visibility',
    'category',
    'uploaded_by',
    'metadata',
])]
class StoredFile extends Model
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size' => 'integer',
            'scanned_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function publicUrl(): string
    {
        return route('stored-files.show', $this);
    }
}
