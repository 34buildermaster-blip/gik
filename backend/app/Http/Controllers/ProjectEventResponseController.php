<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Notifications\CustomerProjectEventResponseNotification;
use App\Services\AdminNotificationRecipients;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ProjectEventResponseController extends Controller
{
    public function update(
        Request $request,
        Project $project,
        ProjectEvent $event,
        AdminNotificationRecipients $adminRecipients,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($event->project_id === $project->id, 404);
        abort_unless($project->customers()->where('users.id', $user->id)->exists(), 403);
        abort_unless($event->customer_visible && $event->status === 'scheduled', 403);
        abort_if($event->starts_at->isPast(), 422, 'ไม่สามารถตอบรับนัดหมายที่ผ่านไปแล้วได้');

        $data = $request->validate([
            'response' => ['required', Rule::in(array_keys(ProjectEvent::CUSTOMER_RESPONSE_LABELS))],
            'proposed_starts_at' => ['nullable', 'required_if:response,reschedule_requested', 'date', 'after:now'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $event->update([
            'customer_response' => $data['response'],
            'customer_responded_by' => $user->id,
            'customer_responded_at' => now(),
            'customer_response_note' => $data['note'] ?? null,
            'proposed_starts_at' => $data['response'] === 'reschedule_requested' ? $data['proposed_starts_at'] : null,
        ]);

        $event->loadMissing(['project.manager', 'project.reviewer', 'assignee']);
        $recipients = $adminRecipients->forProject($project)
            ->merge(array_values(array_filter([$project->manager, $project->reviewer, $event->assignee])))
            ->filter()
            ->unique('id')
            ->values();
        Notification::send($recipients, new CustomerProjectEventResponseNotification($event->fresh(), $user));
        AuditLog::record($user, 'project_event.customer_response', $event, ProjectEvent::CUSTOMER_RESPONSE_LABELS[$data['response']], ['project_id' => $project->id]);

        return back()->with('success', $data['response'] === 'confirmed'
            ? 'ยืนยันนัดหมายเรียบร้อยแล้ว'
            : 'ส่งคำขอเปลี่ยนวันนัดให้ทีมงานเรียบร้อยแล้ว');
    }
}
