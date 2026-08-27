<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_step_id', 'changed_by', 'previous_progress', 'new_progress', 'inspection_result', 'reason'])]
class ProjectStepProgressLog extends Model
{
    protected function casts(): array
    {
        return [
            'previous_progress' => 'integer',
            'new_progress' => 'integer',
        ];
    }

    public function projectStep(): BelongsTo
    {
        return $this->belongsTo(ProjectStep::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
