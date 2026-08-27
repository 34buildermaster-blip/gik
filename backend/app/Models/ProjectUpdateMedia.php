<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_update_id', 'stored_file_id', 'path', 'original_name', 'mime_type', 'caption', 'sort_order'])]
class ProjectUpdateMedia extends Model
{
    protected $table = 'project_update_media';

    public function projectUpdate(): BelongsTo
    {
        return $this->belongsTo(ProjectUpdate::class, 'project_update_id');
    }

    public function storedFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class);
    }
}
