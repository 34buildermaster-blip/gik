<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_event_id', 'stored_file_id', 'uploaded_by'])]
class ProjectEventAttachment extends Model
{
    public function event(): BelongsTo
    {
        return $this->belongsTo(ProjectEvent::class, 'project_event_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'stored_file_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
