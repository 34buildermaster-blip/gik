<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'created_by',
    'assigned_to',
    'title',
    'description',
    'type',
    'starts_at',
    'ends_at',
    'location',
    'status',
    'customer_visible',
    'reminder_sent_at',
])]
class ProjectEvent extends Model
{
    public const TYPE_LABELS = [
        'site_inspection' => 'ตรวจหน้างาน',
        'meeting' => 'ประชุม',
        'material_delivery' => 'ส่งวัสดุ',
        'payment' => 'นัดชำระเงิน',
        'handover' => 'ส่งมอบงาน',
        'other' => 'นัดหมายอื่น ๆ',
    ];

    public const STATUS_LABELS = [
        'scheduled' => 'ตามกำหนด',
        'completed' => 'เสร็จแล้ว',
        'cancelled' => 'ยกเลิก',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'customer_visible' => 'boolean',
            'reminder_sent_at' => 'datetime',
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

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
