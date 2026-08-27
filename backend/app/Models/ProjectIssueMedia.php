<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_issue_id', 'stored_file_id', 'sort_order'])]
class ProjectIssueMedia extends Model
{
    protected $table = 'project_issue_media';

    public function issue(): BelongsTo
    {
        return $this->belongsTo(ProjectIssue::class, 'project_issue_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'stored_file_id');
    }
}
