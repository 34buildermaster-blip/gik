<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    'latitude',
    'longitude',
    'status',
    'customer_visible',
    'customer_response',
    'customer_responded_by',
    'customer_responded_at',
    'customer_response_note',
    'proposed_starts_at',
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

    public const CUSTOMER_RESPONSE_LABELS = [
        'confirmed' => 'ยืนยันเข้าร่วม',
        'reschedule_requested' => 'ขอเปลี่ยนวันนัด',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'customer_visible' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'customer_responded_at' => 'datetime',
            'proposed_starts_at' => 'datetime',
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

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_responded_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectEventAttachment::class);
    }

    public function googleMapsUrl(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return 'https://www.google.com/maps?q='.$this->latitude.','.$this->longitude;
    }
}
