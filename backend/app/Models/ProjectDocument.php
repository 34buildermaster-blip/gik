<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'stored_file_id', 'uploaded_by', 'title', 'category', 'version', 'visibility', 'notes'])]
class ProjectDocument extends Model
{
    public const CATEGORY_LABELS = [
        'contract' => 'สัญญา',
        'quotation' => 'ใบเสนอราคา',
        'boq' => 'BOQ / รายการวัสดุ',
        'drawing' => 'แบบก่อสร้าง',
        'handover' => 'เอกสารส่งมอบ',
        'other' => 'เอกสารอื่น ๆ',
    ];

    public const VISIBILITY_LABELS = [
        'staff' => 'เฉพาะทีมงาน',
        'customer' => 'ลูกค้าดูได้',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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
