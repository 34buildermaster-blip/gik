<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'project_update_id', 'customer_id', 'subject', 'status', 'last_message_at'])]
class ProjectInquiry extends Model
{
    public const STATUS_LABELS = [
        'open' => 'รอทีมงานตอบ',
        'answered' => 'ทีมงานตอบแล้ว',
        'closed' => 'ปิดเรื่องแล้ว',
    ];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectUpdate(): BelongsTo
    {
        return $this->belongsTo(ProjectUpdate::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProjectInquiryMessage::class)->oldest();
    }
}
