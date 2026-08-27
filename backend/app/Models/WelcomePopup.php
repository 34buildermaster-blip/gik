<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'desktop_stored_file_id',
    'mobile_stored_file_id',
    'name',
    'alt_text',
    'link_url',
    'starts_at',
    'ends_at',
    'sort_order',
    'is_active',
])]
class WelcomePopup extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function desktopImage(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'desktop_stored_file_id');
    }

    public function mobileImage(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'mobile_stored_file_id');
    }

    public function isCurrentlyVisible(): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->ends_at || $this->ends_at->isFuture());
    }
}
