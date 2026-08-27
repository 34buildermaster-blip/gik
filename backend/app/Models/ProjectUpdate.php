<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_id',
    'project_step_id',
    'created_by',
    'title',
    'description',
    'stage',
    'progress_percent',
    'inspection_result',
    'progress_reason',
    'work_performed_at',
    'status',
    'submitted_at',
    'reviewed_by',
    'reviewed_at',
    'review_note',
    'published_at',
    'notified_at',
])]
class ProjectUpdate extends Model
{
    public const STATUS_LABELS = [
        'draft' => 'ฉบับร่าง',
        'pending_review' => 'รอ Admin ตรวจ',
        'changes_requested' => 'ส่งกลับแก้ไข',
        'published' => 'อนุมัติและเผยแพร่แล้ว',
    ];

    public const STAGE_LABELS = [
        'survey' => 'สำรวจและเตรียมพื้นที่',
        'design' => 'ออกแบบและอนุมัติแบบ',
        'procurement' => 'จัดเตรียมวัสดุ',
        'structure' => 'งานโครงสร้าง',
        'architecture' => 'งานสถาปัตยกรรม',
        'interior' => 'งานตกแต่งและบิวท์อิน',
        'inspection' => 'ตรวจสอบและเก็บงาน',
        'handover' => 'ส่งมอบโครงการ',
    ];

    protected function casts(): array
    {
        return [
            'progress_percent' => 'integer',
            'work_performed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projectStep(): BelongsTo
    {
        return $this->belongsTo(ProjectStep::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProjectUpdateMedia::class)->orderBy('sort_order');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_update_reads')->withPivot('read_at');
    }

    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ProjectUpdateReviewLog::class)->latest('id');
    }

    public function canBeEditedBy(User $user): bool
    {
        return in_array($this->status, ['draft', 'changes_requested'], true)
            && ($user->isAdmin() || $this->created_by === $user->id);
    }
}
