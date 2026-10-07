<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectAcknowledgement;
use App\Models\ProjectDocument;
use App\Models\ProjectInquiry;
use App\Models\ProjectInquiryAttachment;
use App\Models\ProjectUpdate;
use App\Notifications\ProjectInquiryMessageCreated;
use App\Services\AdminNotificationRecipients;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ProjectInteractionController extends Controller
{
    public function acknowledge(Request $request, Project $project, string $type, int $target): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'user' && $project->customers()->where('users.id', $user->id)->exists(), 403);

        $item = match ($type) {
            'document' => ProjectDocument::query()
                ->where('project_id', $project->id)
                ->where('visibility', 'customer')
                ->where('requires_acknowledgement', true)
                ->findOrFail($target),
            'update' => ProjectUpdate::query()
                ->where('project_id', $project->id)
                ->where('status', 'published')
                ->where('requires_acknowledgement', true)
                ->findOrFail($target),
            default => abort(404),
        };

        ProjectAcknowledgement::firstOrCreate(
            ['user_id' => $user->id, 'target_type' => $type, 'target_id' => $item->id],
            ['project_id' => $project->id, 'acknowledged_at' => now()],
        );
        AuditLog::record($user, 'project_item.acknowledged', $item, "ยืนยันรับทราบ {$type}", ['project_id' => $project->id]);

        return back()->with('success', 'ยืนยันรับทราบเรียบร้อยแล้ว');
    }

    public function storeInquiry(
        Request $request,
        Project $project,
        MediaStorage $storage,
        AdminNotificationRecipients $adminRecipients,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user->role === 'user' && $project->customers()->where('users.id', $user->id)->exists(), 403);
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:5000'],
            'project_update_id' => ['nullable', Rule::exists('project_updates', 'id')->where('project_id', $project->id)],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => ['image', 'max:10240'],
        ]);

        $inquiry = DB::transaction(function () use ($data, $project, $request, $storage, $user): ProjectInquiry {
            $inquiry = $project->inquiries()->create([
                'project_update_id' => $data['project_update_id'] ?? null,
                'customer_id' => $user->id,
                'subject' => $data['subject'],
                'status' => 'open',
                'last_message_at' => now(),
            ]);
            $message = $inquiry->messages()->create(['sender_id' => $user->id, 'body' => $data['body']]);
            $this->storeAttachments($request, $message->id, $project, $storage);

            return $inquiry;
        });

        $message = $inquiry->messages()->with('sender')->latest('id')->firstOrFail();
        $recipients = $adminRecipients->forProject($project);
        if ($project->manager && ! $project->manager->isDisabled()) {
            $recipients->push($project->manager);
        }
        Notification::send($recipients->unique('id')->values(), new ProjectInquiryMessageCreated($inquiry, $message));
        AuditLog::record($user, 'project_inquiry.created', $inquiry, "ส่งคำถาม {$inquiry->subject}");

        return back()->with('success', 'ส่งคำถามให้ทีมงานแล้ว ระบบจะแจ้งเตือนเมื่อมีคำตอบ');
    }

    public function reply(
        Request $request,
        Project $project,
        ProjectInquiry $inquiry,
        MediaStorage $storage,
        AdminNotificationRecipients $adminRecipients,
    ): RedirectResponse {
        $this->authorizeInquiry($request, $project, $inquiry);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => ['image', 'max:10240'],
        ]);
        $user = $request->user();

        $message = DB::transaction(function () use ($data, $inquiry, $request, $project, $storage, $user) {
            $message = $inquiry->messages()->create(['sender_id' => $user->id, 'body' => $data['body']]);
            $this->storeAttachments($request, $message->id, $project, $storage);
            $inquiry->update([
                'status' => $user->isStaff() ? 'answered' : 'open',
                'last_message_at' => now(),
            ]);

            return $message;
        });

        $message->load('sender');
        if ($user->isStaff()) {
            $recipients = collect([$inquiry->customer])->filter();
        } else {
            $recipients = $adminRecipients->forProject($project);
            if ($project->manager && ! $project->manager->isDisabled()) {
                $recipients->push($project->manager);
            }
            $recipients = $recipients->unique('id')->values();
        }
        Notification::send($recipients, new ProjectInquiryMessageCreated($inquiry, $message));
        AuditLog::record($user, 'project_inquiry.replied', $inquiry, "ตอบคำถาม {$inquiry->subject}");

        return back()->with('success', 'ส่งข้อความเรียบร้อยแล้ว');
    }

    public function updateStatus(Request $request, Project $project, ProjectInquiry $inquiry): RedirectResponse
    {
        $this->authorizeInquiry($request, $project, $inquiry);
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'answered', 'closed'])]]);
        $inquiry->update(['status' => $data['status']]);
        AuditLog::record($request->user(), 'project_inquiry.status_updated', $inquiry, "เปลี่ยนสถานะคำถามเป็น {$data['status']}");

        return back()->with('success', 'อัปเดตสถานะคำถามเรียบร้อยแล้ว');
    }

    private function authorizeInquiry(Request $request, Project $project, ProjectInquiry $inquiry): void
    {
        abort_unless($inquiry->project_id === $project->id, 404);
        $user = $request->user();
        $allowed = $user->isStaff()
            ? $project->canBeManagedBy($user)
            : $inquiry->customer_id === $user->id && $project->customers()->where('users.id', $user->id)->exists();
        abort_unless($allowed, 403);
    }

    private function storeAttachments(Request $request, int $messageId, Project $project, MediaStorage $storage): void
    {
        foreach ($request->file('attachments', []) as $index => $upload) {
            $file = $storage->store($upload, "project-inquiries/{$project->id}", 'private', $request->user());
            ProjectInquiryAttachment::create([
                'project_inquiry_message_id' => $messageId,
                'stored_file_id' => $file->id,
                'sort_order' => $index,
            ]);
        }
    }
}
