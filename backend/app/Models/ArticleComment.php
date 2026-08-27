<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'article_slug',
    'article_title',
    'author_name',
    'author_email',
    'body',
    'status',
    'admin_reply',
    'approved_at',
    'replied_at',
    'moderated_by',
    'replied_by',
    'ip_hash',
    'user_agent',
])]
class ArticleComment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_HIDDEN = 'hidden';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'รอตรวจสอบ',
        self::STATUS_APPROVED => 'เผยแพร่แล้ว',
        self::STATUS_HIDDEN => 'ซ่อน',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
