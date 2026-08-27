<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_update_id', 'acted_by', 'action', 'from_status', 'to_status', 'note'])]
class ProjectUpdateReviewLog extends Model
{
    public const ACTION_LABELS = [
        'submitted' => 'ส่งให้ตรวจ',
        'approved' => 'อนุมัติ',
        'changes_requested' => 'ส่งกลับแก้ไข',
    ];

    public function projectUpdate(): BelongsTo
    {
        return $this->belongsTo(ProjectUpdate::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
