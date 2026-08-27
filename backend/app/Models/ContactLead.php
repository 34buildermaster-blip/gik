<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'phone',
    'email',
    'service_type',
    'message',
    'source_url',
    'status',
    'admin_note',
    'contacted_at',
    'assigned_to',
    'next_follow_up_at',
    'converted_project_id',
    'converted_at',
    'ip_hash',
    'user_agent',
])]
class ContactLead extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_SPAM = 'spam';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_LABELS = [
        self::STATUS_NEW => 'รายการใหม่',
        self::STATUS_CONTACTED => 'กำลังติดตาม',
        self::STATUS_CLOSED => 'ปิดรายการแล้ว',
        self::STATUS_SPAM => 'สแปม',
        self::STATUS_CONVERTED => 'เปลี่ยนเป็นโครงการแล้ว',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_NEW, self::STATUS_CONTACTED]);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'converted_project_id');
    }
}
