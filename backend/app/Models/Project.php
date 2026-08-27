<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['manager_id', 'code', 'name', 'type', 'address', 'start_date', 'estimated_end_date', 'status', 'progress_percent', 'summary'])]
class Project extends Model
{
    use SoftDeletes;

    public const STATUS_LABELS = [
        'preparing' => 'เตรียมงาน',
        'in_progress' => 'กำลังดำเนินงาน',
        'on_hold' => 'พักงาน',
        'completed' => 'เสร็จสิ้น',
    ];

    public const TYPE_LABELS = [
        'house_build' => 'สร้างบ้าน',
        'renovation' => 'รีโนเวท',
        'interior' => 'บิวท์อิน',
        'design' => 'ออกแบบ',
        'other' => 'งานอื่น ๆ',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'estimated_end_date' => 'date',
            'progress_percent' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function canBeManagedBy(User $user): bool
    {
        return $user->isAdmin() || ($user->isInspector() && $this->manager_id === $user->id);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class)->latest('work_performed_at');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProjectStep::class)->orderBy('sort_order')->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->latest();
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ProjectIssue::class)->latest();
    }

    public function stepWeightTotal(): int
    {
        return (int) $this->steps()->sum('weight_percent');
    }

    public function hasConfiguredSteps(): bool
    {
        return $this->steps()->exists() && $this->stepWeightTotal() === 100;
    }

    public function recalculateProgress(): int
    {
        if (! $this->hasConfiguredSteps()) {
            return $this->progress_percent;
        }

        $progress = (int) round($this->steps()->get()->sum(
            fn (ProjectStep $step): float => ($step->weight_percent * $step->progress_percent) / 100
        ));

        $status = $this->status;
        if ($status !== 'on_hold') {
            $status = match (true) {
                $progress >= 100 => 'completed',
                $progress > 0 => 'in_progress',
                default => 'preparing',
            };
        }

        $this->update([
            'progress_percent' => $progress,
            'status' => $status,
        ]);

        return $progress;
    }
}
