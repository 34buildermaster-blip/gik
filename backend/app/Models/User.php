<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'email_verified_at', 'phone', 'address', 'billing_name', 'tax_id', 'preferred_contact_channel', 'emergency_contact_name', 'emergency_contact_phone', 'customer_status', 'internal_notes', 'line_recipient_id', 'notification_preferences', 'password', 'avatar_path', 'avatar_file_id', 'role', 'failed_login_attempts', 'login_locked_until', 'password_must_change', 'password_changed_at', 'terms_accepted_at', 'privacy_accepted_at', 'marketing_consent_at', 'policy_version', 'consent_ip_hash'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'tax_id', 'internal_notes'])]
class User extends Authenticatable
{
    public const ROLE_LABELS = [
        'admin' => 'ผู้ดูแลระบบ',
        'inspector' => 'ผู้ตรวจหน้างาน',
        'user' => 'ลูกค้า',
    ];

    public const CUSTOMER_STATUS_LABELS = [
        'active' => 'ใช้งานอยู่',
        'prospect' => 'ผู้สนใจ / รอเริ่มโครงการ',
        'inactive' => 'พักการใช้งาน',
    ];

    public const CONTACT_CHANNEL_LABELS = [
        'phone' => 'โทรศัพท์',
        'line' => 'LINE',
        'email' => 'อีเมล',
    ];

    public const NOTIFICATION_CHANNEL_LABELS = [
        'database' => 'ในเว็บไซต์',
        'line' => 'LINE',
        'email' => 'อีเมล',
    ];

    public const NOTIFICATION_EVENT_LABELS = [
        'project_update_submitted' => 'งานใหม่รอ Admin ตรวจสอบ',
        'project_update_changes_requested' => 'งานถูกส่งกลับให้แก้ไข',
        'project_update_approved' => 'อัปเดตโครงการผ่านการอนุมัติ',
        'contact_lead_submitted' => 'ผู้ติดต่อใหม่จากเว็บไซต์',
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInspector(): bool
    {
        return $this->role === 'inspector';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'inspector'], true);
    }

    public function hasTwoFactorAuthenticationEnabled(): bool
    {
        return filled($this->two_factor_secret)
            && $this->two_factor_confirmed_at !== null;
    }

    public function isLoginLocked(): bool
    {
        return $this->login_locked_until?->isFuture() ?? false;
    }

    public function routeNotificationForLine(): ?string
    {
        return $this->line_recipient_id;
    }

    public function lineAccountLinks(): HasMany
    {
        return $this->hasMany(LineAccountLink::class);
    }

    public function socialIdentities(): HasMany
    {
        return $this->hasMany(SocialIdentity::class);
    }

    public function notificationSettings(): array
    {
        $stored = is_array($this->notification_preferences) ? $this->notification_preferences : [];

        return [
            'channels' => array_values(array_intersect(
                $stored['channels'] ?? array_keys(self::NOTIFICATION_CHANNEL_LABELS),
                array_keys(self::NOTIFICATION_CHANNEL_LABELS),
            )),
            'events' => array_values(array_intersect(
                $stored['events'] ?? $this->availableNotificationEvents(),
                $this->availableNotificationEvents(),
            )),
            'all_projects' => $this->isAdmin() && (bool) ($stored['all_projects'] ?? false),
        ];
    }

    public function availableNotificationEvents(): array
    {
        return match ($this->role) {
            'admin' => ['project_update_submitted', 'contact_lead_submitted'],
            'inspector' => ['project_update_changes_requested'],
            default => ['project_update_approved'],
        };
    }

    public function wantsNotificationEvent(string $event): bool
    {
        return in_array($event, $this->notificationSettings()['events'], true);
    }

    public function wantsNotificationChannel(string $channel): bool
    {
        return in_array($channel, $this->notificationSettings()['channels'], true);
    }

    public function monitorsAllProjects(): bool
    {
        return $this->notificationSettings()['all_projects'];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function allProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTrashed()->withTimestamps();
    }

    public function maskedTaxId(): ?string
    {
        if (blank($this->tax_id)) {
            return null;
        }

        $visible = mb_substr($this->tax_id, -4);

        return str_repeat('•', max(4, mb_strlen($this->tax_id) - 4)).$visible;
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function avatarFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'avatar_file_id');
    }

    public function projectUpdatesRead(): BelongsToMany
    {
        return $this->belongsToMany(ProjectUpdate::class, 'project_update_reads')->withPivot('read_at');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'failed_login_attempts' => 'integer',
            'login_locked_until' => 'datetime',
            'password_must_change' => 'boolean',
            'password_changed_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'marketing_consent_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'tax_id' => 'encrypted',
            'notification_preferences' => 'array',
        ];
    }
}
