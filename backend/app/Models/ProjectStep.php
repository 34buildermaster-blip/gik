<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'name', 'description', 'weight_percent', 'progress_percent', 'status', 'sort_order', 'planned_start_date', 'planned_end_date', 'actual_completed_at'])]
class ProjectStep extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'รอเริ่มงาน',
        'in_progress' => 'กำลังดำเนินงาน',
        'needs_attention' => 'ต้องแก้ไข',
        'completed' => 'ผ่านและเสร็จสิ้น',
    ];

    public const INSPECTION_LABELS = [
        'not_checked' => 'ยังไม่ได้ตรวจ',
        'passed' => 'ผ่านการตรวจ',
        'failed' => 'ไม่ผ่านการตรวจ',
        'rework' => 'กำลังแก้ไขงาน',
    ];

    protected function casts(): array
    {
        return [
            'weight_percent' => 'integer',
            'progress_percent' => 'integer',
            'sort_order' => 'integer',
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'actual_completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(ProjectStepProgressLog::class)->latest('id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ProjectIssue::class);
    }

    public function scheduleStatus(): string
    {
        if ($this->progress_percent >= 100 || $this->actual_completed_at) {
            return 'completed';
        }

        if ($this->planned_end_date?->isPast()) {
            return 'overdue';
        }

        if ($this->progress_percent > 0 || $this->planned_start_date?->isPast()) {
            return 'in_progress';
        }

        return 'upcoming';
    }

    public function contributionPercent(): float
    {
        return round(($this->weight_percent * $this->progress_percent) / 100, 2);
    }
}
