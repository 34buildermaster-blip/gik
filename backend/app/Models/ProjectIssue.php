<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'project_step_id', 'created_by', 'assigned_to', 'verified_by', 'title', 'description', 'priority', 'status', 'customer_visible', 'due_date', 'resolved_at'])]
class ProjectIssue extends Model
{
    public const STATUS_LABELS = [
        'open' => 'เปิดรายการ',
        'in_progress' => 'กำลังแก้ไข',
        'pending_verification' => 'รอตรวจรับ',
        'resolved' => 'แก้ไขแล้ว',
    ];

    public const PRIORITY_LABELS = [
        'low' => 'ต่ำ',
        'normal' => 'ปกติ',
        'high' => 'สูง',
        'urgent' => 'เร่งด่วน',
    ];

    protected function casts(): array
    {
        return [
            'customer_visible' => 'boolean',
            'due_date' => 'date',
            'resolved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectStep(): BelongsTo
    {
        return $this->belongsTo(ProjectStep::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProjectIssueMedia::class)->orderBy('sort_order')->orderBy('id');
    }
}
