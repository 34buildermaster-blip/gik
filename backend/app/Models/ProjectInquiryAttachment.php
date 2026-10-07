<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_inquiry_message_id', 'stored_file_id', 'sort_order'])]
class ProjectInquiryAttachment extends Model
{
    public function message(): BelongsTo
    {
        return $this->belongsTo(ProjectInquiryMessage::class, 'project_inquiry_message_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'stored_file_id');
    }
}
