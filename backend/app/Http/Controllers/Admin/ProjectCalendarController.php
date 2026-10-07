<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\ProjectIssue;
use App\Models\ProjectStep;
use App\Models\User;
use App\Notifications\ProjectEventNotification;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->calendarMonth($request->string('month')->toString());
        $gridStart = $month->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
        $gridEnd = $month->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();
        $projectQuery = $this->availableProjects($request->user());
        $projects = (clone $projectQuery)->orderBy('name')->get(['id', 'code', 'name', 'manager_id', 'estimated_end_date']);
        $projectIds = $projects->pluck('id');

        $events = ProjectEvent::query()
            ->whereIn('project_id', $projectIds)
            ->whereBetween('starts_at', [$gridStart, $gridEnd])
            ->with(['project:id,code,name', 'assignee:id,name'])
            ->orderBy('starts_at')
            ->get();

        $items = $this->calendarItems($projectIds, $events, $gridStart, $gridEnd);
        $days = collect(range(0, $gridStart->diffInDays($gridEnd)))
            ->map(fn (int $offset) => $gridStart->addDays($offset));
        $upcomingEvents = ProjectEvent::query()
            ->whereIn('project_id', $projectIds)
            ->where('status', 'scheduled')
            ->where('starts_at', '>=', now()->startOfDay())
            ->with(['project:id,code,name', 'assignee:id,name'])
            ->orderBy('starts_at')
            ->limit(12)
            ->get();

        return view('admin.calendar.index', [
            'month' => $month,
            'days' => $days,
            'itemsByDate' => $items->groupBy('date'),
            'projects' => $projects,
            'staffUsers' => $request->user()->isAdmin()
                ? User::whereIn('role', ['admin', 'inspector'])->orderBy('name')->get(['id', 'name', 'role'])
                : collect([$request->user()]),
            'upcomingEvents' => $upcomingEvents,
            'typeLabels' => ProjectEvent::TYPE_LABELS,
            'statusLabels' => ProjectEvent::STATUS_LABELS,
            'selectedProjectId' => (int) $request->integer('project'),
            'selectedEventId' => (int) $request->integer('event'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $project = Project::findOrFail($data['project_id']);
        abort_unless($project->canBeManagedBy($request->user()), 403);
        $this->authorizeAssignee($request->user(), $data['assigned_to'] ?? null);

        $data['created_by'] = $request->user()->id;
        $data['customer_visible'] = $request->boolean('customer_visible');
        $event = ProjectEvent::create($data);
        $this->notifyParticipants($event, 'scheduled', $request->user());
        AuditLog::record($request->user(), 'project_event.created', $event, "เพิ่มนัดหมาย {$event->title}");

        return redirect()
            ->route('admin.calendar.index', ['month' => $event->starts_at->format('Y-m'), 'event' => $event->id])
            ->with('success', 'เพิ่มกำหนดการและแจ้งผู้เกี่ยวข้องเรียบร้อยแล้ว');
    }

    public function update(Request $request, ProjectEvent $event): RedirectResponse
    {
        $event->loadMissing('project');
        abort_unless($event->project->canBeManagedBy($request->user()), 403);
        $data = $this->validatedData($request, true);
        $project = Project::findOrFail($data['project_id']);
        abort_unless($project->canBeManagedBy($request->user()), 403);
        $this->authorizeAssignee($request->user(), $data['assigned_to'] ?? null);

        $scheduleChanged = $event->starts_at->format('Y-m-d H:i') !== CarbonImmutable::parse($data['starts_at'])->format('Y-m-d H:i');
        $data['customer_visible'] = $request->boolean('customer_visible');
        if ($scheduleChanged) {
            $data['reminder_sent_at'] = null;
        }
        $event->update($data);
        $this->notifyParticipants($event->fresh(), 'updated', $request->user());
        AuditLog::record($request->user(), 'project_event.updated', $event, "แก้ไขนัดหมาย {$event->title}");

        return redirect()
            ->route('admin.calendar.index', ['month' => $event->starts_at->format('Y-m'), 'event' => $event->id])
            ->with('success', 'อัปเดตกำหนดการเรียบร้อยแล้ว');
    }

    private function validatedData(Request $request, bool $updating = false): array
    {
        return $request->validateWithBag('calendar', [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'type' => ['required', Rule::in(array_keys(ProjectEvent::TYPE_LABELS))],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => [$updating ? 'required' : 'nullable', Rule::in(array_keys(ProjectEvent::STATUS_LABELS))],
            'customer_visible' => ['nullable', 'boolean'],
        ]);
    }

    private function availableProjects(User $user)
    {
        return Project::query()
            ->when($user->isInspector(), fn ($query) => $query->where('manager_id', $user->id));
    }

    private function authorizeAssignee(User $user, ?int $assigneeId): void
    {
        if (! $assigneeId) {
            return;
        }

        abort_unless(
            User::whereKey($assigneeId)->whereIn('role', ['admin', 'inspector'])->exists(),
            422,
            'ผู้รับผิดชอบต้องเป็น Admin หรือผู้ตรวจหน้างาน',
        );
        abort_if($user->isInspector() && $assigneeId !== $user->id, 403);
    }

    private function calendarMonth(string $value): CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                return CarbonImmutable::createFromFormat('Y-m-d', $value.'-01')->startOfMonth();
            } catch (\Throwable) {
                // Fall through to the current month.
            }
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    private function calendarItems(Collection $projectIds, Collection $events, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $items = $events->map(fn (ProjectEvent $event) => [
            'date' => $event->starts_at->toDateString(),
            'time' => $event->starts_at->format('H:i'),
            'title' => $event->title,
            'project' => $event->project->code,
            'kind' => 'event',
            'type' => $event->type,
            'event' => $event,
        ]);

        ProjectStep::query()
            ->whereIn('project_id', $projectIds)
            ->whereNotNull('planned_end_date')
            ->whereBetween('planned_end_date', [$from->toDateString(), $to->toDateString()])
            ->where('progress_percent', '<', 100)
            ->with('project:id,code,name')
            ->get()
            ->each(fn (ProjectStep $step) => $items->push([
                'date' => $step->planned_end_date->toDateString(),
                'time' => null,
                'title' => 'กำหนดจบ: '.$step->name,
                'project' => $step->project->code,
                'kind' => $step->planned_end_date->isPast() ? 'overdue' : 'step',
                'type' => 'step',
                'event' => null,
            ]));

        ProjectIssue::query()
            ->whereIn('project_id', $projectIds)
            ->whereNotIn('status', ['resolved'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
            ->with('project:id,code,name')
            ->get()
            ->each(fn (ProjectIssue $issue) => $items->push([
                'date' => $issue->due_date->toDateString(),
                'time' => null,
                'title' => 'กำหนดแก้ไข: '.$issue->title,
                'project' => $issue->project->code,
                'kind' => $issue->due_date->isPast() ? 'overdue' : 'issue',
                'type' => 'issue',
                'event' => null,
            ]));

        return $items->sortBy(fn (array $item) => $item['date'].' '.($item['time'] ?? '23:59'))->values();
    }

    private function notifyParticipants(ProjectEvent $event, string $mode, User $actor): void
    {
        if ($event->status === 'cancelled' && $mode === 'scheduled') {
            return;
        }

        $event->loadMissing(['project.customers', 'project.manager', 'project.reviewer', 'assignee']);
        $recipients = collect([
            $event->project->manager,
            $event->project->reviewer,
            $event->assignee,
            ...($event->customer_visible ? $event->project->customers->all() : []),
        ])->filter()->reject(fn ($recipient) => $recipient->is($actor))->unique('id')->values();

        Notification::send($recipients, new ProjectEventNotification($event, $mode));
    }
}
