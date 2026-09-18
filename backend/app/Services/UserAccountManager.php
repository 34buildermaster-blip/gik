<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserAccountManager
{
    public function suspend(User $actor, User $user, string $reason): void
    {
        DB::transaction(function () use ($actor, $user, $reason): void {
            $user->forceFill([
                'disabled_at' => now(),
                'disabled_by' => $actor->id,
                'disabled_reason' => $reason,
                'line_recipient_id' => null,
                'remember_token' => Str::random(60),
            ])->save();
            $user->lineAccountLinks()->delete();
            $user->socialIdentities()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        });

        AuditLog::record($actor, 'user.suspended', $user, "ระงับบัญชี {$user->name}", [
            'reason' => $reason,
            'role' => $user->role,
        ]);
    }

    public function restore(User $actor, User $user): void
    {
        $user->forceFill([
            'disabled_at' => null,
            'disabled_by' => null,
            'disabled_reason' => null,
            'failed_login_attempts' => 0,
            'login_locked_until' => null,
        ])->save();

        AuditLog::record($actor, 'user.restored', $user, "เปิดใช้งานบัญชี {$user->name}", [
            'role' => $user->role,
        ]);
    }

    /** @return array<int, string> */
    public function deletionBlockers(User $user): array
    {
        $id = $user->id;
        $checks = [
            'โครงการที่เป็นลูกค้า' => fn (): bool => DB::table('project_user')->where('user_id', $id)->exists(),
            'โครงการที่รับผิดชอบหรือตรวจอนุมัติ' => fn (): bool => DB::table('projects')->where('manager_id', $id)->orWhere('reviewer_id', $id)->exists(),
            'อัปเดตหน้างานที่สร้างหรือตรวจสอบ' => fn (): bool => DB::table('project_updates')->where('created_by', $id)->orWhere('reviewed_by', $id)->exists(),
            'ประวัติการตรวจอัปเดต' => fn (): bool => DB::table('project_update_review_logs')->where('acted_by', $id)->exists(),
            'ประวัติความคืบหน้าขั้นตอนงาน' => fn (): bool => DB::table('project_step_progress_logs')->where('changed_by', $id)->exists(),
            'ปัญหาหน้างานที่เกี่ยวข้อง' => fn (): bool => DB::table('project_issues')->where('created_by', $id)->orWhere('assigned_to', $id)->orWhere('verified_by', $id)->exists(),
            'เอกสารหรือไฟล์ที่อัปโหลด' => fn (): bool => DB::table('project_documents')->where('uploaded_by', $id)->exists()
                || DB::table('stored_files')->where('uploaded_by', $id)->exists(),
            'บทความหรือแบบบ้านที่สร้าง' => fn (): bool => DB::table('articles')->where('user_id', $id)->exists()
                || DB::table('house_designs')->where('user_id', $id)->exists(),
            'ประวัติดูแลความคิดเห็น' => fn (): bool => DB::table('article_comments')->where('moderated_by', $id)->orWhere('replied_by', $id)->exists(),
            'ผู้ติดต่อที่ได้รับมอบหมาย' => fn (): bool => DB::table('contact_leads')->where('assigned_to', $id)->exists(),
        ];

        return collect($checks)
            ->filter(fn (callable $check): bool => $check())
            ->keys()
            ->values()
            ->all();
    }

    public function permanentlyDelete(User $actor, User $user): void
    {
        $snapshot = [
            'deleted_user_id' => $user->id,
            'role' => $user->role,
            'email_hash' => hash('sha256', mb_strtolower($user->email)),
        ];
        $name = $user->name;

        DB::transaction(function () use ($user): void {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            DB::table('notifications')
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->id)
                ->delete();
            $user->lineAccountLinks()->delete();
            $user->socialIdentities()->delete();
            $user->delete();
        });

        AuditLog::record($actor, 'user.deleted', null, "ลบบัญชี {$name} ถาวร", $snapshot);
    }
}
