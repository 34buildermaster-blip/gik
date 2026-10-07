<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_inquiry_id', 'sender_id', 'body'])]
class ProjectInquiryMessage extends Model
{
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(ProjectInquiry::class, 'project_inquiry_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectInquiryAttachment::class)->orderBy('sort_order');
    }
}
