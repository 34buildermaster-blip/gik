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
use App\Services\MediaStorage;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $filters = $this->filters($request, $projects);
        $projectIds = $filters['project'] ? collect([$filters['project']]) : $projects->pluck('id');

        $events = $this->filteredEvents(ProjectEvent::query(), $projectIds, $filters)
            ->whereIn('project_id', $projectIds)
            ->where('status', '<>', 'cancelled')
            ->whereBetween('starts_at', [$gridStart, $gridEnd])
            ->with(['project:id,code,name', 'assignee:id,name', 'responder:id,name', 'attachments.file'])
            ->orderBy('starts_at')
            ->get();

        $items = $this->calendarItems($projectIds, $events, $gridStart, $gridEnd, $this->hasEventOnlyFilters($filters));
        $days = collect(range(0, $gridStart->diffInDays($gridEnd)))
            ->map(fn (int $offset) => $gridStart->addDays($offset));
        $upcomingEvents = $this->filteredEvents(ProjectEvent::query(), $projectIds, $filters)
            ->whereIn('project_id', $projectIds)
            ->where('status', 'scheduled')
            ->where('starts_at', '>=', now()->startOfDay())
            ->with(['project:id,code,name', 'assignee:id,name', 'responder:id,name', 'attachments.file'])
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
            'responseLabels' => ProjectEvent::CUSTOMER_RESPONSE_LABELS,
            'filters' => $filters,
            'filterQuery' => array_filter(array_merge(['month' => $month->format('Y-m')], $filters)),
            'selectedProjectId' => $filters['project'],
            'selectedEventId' => (int) $request->integer('event'),
        ]);
    }

    public function store(Request $request, MediaStorage $storage): RedirectResponse
    {
        $data = $this->validatedData($request);
        $project = Project::findOrFail($data['project_id']);
        abort_unless($project->canBeManagedBy($request->user()), 403);
        $this->authorizeAssignee($request->user(), $data['assigned_to'] ?? null);

        unset($data['attachments']);
        $data['created_by'] = $request->user()->id;
        $data['customer_visible'] = $request->boolean('customer_visible');
        $event = ProjectEvent::create($data);
        $this->storeAttachments($request, $event, $storage);
        $this->notifyParticipants($event, 'scheduled', $request->user());
        AuditLog::record($request->user(), 'project_event.created', $event, "เพิ่มนัดหมาย {$event->title}");

        return redirect()
            ->route('admin.calendar.index', ['month' => $event->starts_at->format('Y-m'), 'event' => $event->id])
            ->with('success', 'เพิ่มกำหนดการและแจ้งผู้เกี่ยวข้องเรียบร้อยแล้ว');
    }

    public function update(Request $request, ProjectEvent $event, MediaStorage $storage): RedirectResponse
    {
        $event->loadMissing('project');
        abort_unless($event->project->canBeManagedBy($request->user()), 403);
        $data = $this->validatedData($request, true);
        $project = Project::findOrFail($data['project_id']);
        abort_unless($project->canBeManagedBy($request->user()), 403);
        $this->authorizeAssignee($request->user(), $data['assigned_to'] ?? null);

        $scheduleChanged = $event->starts_at->format('Y-m-d H:i') !== CarbonImmutable::parse($data['starts_at'])->format('Y-m-d H:i');
        unset($data['attachments']);
        $data['customer_visible'] = $request->boolean('customer_visible');
        if ($scheduleChanged) {
            $data['reminder_sent_at'] = null;
            $data['customer_response'] = null;
            $data['customer_responded_by'] = null;
            $data['customer_responded_at'] = null;
            $data['customer_response_note'] = null;
            $data['proposed_starts_at'] = null;
        }
        $event->update($data);
        $this->storeAttachments($request, $event, $storage);
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
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'status' => [$updating ? 'required' : 'nullable', Rule::in(array_keys(ProjectEvent::STATUS_LABELS))],
            'customer_visible' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:20480', 'mimes:pdf,docx,xlsx,csv,jpg,jpeg,png,webp'],
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        [$events, $month] = $this->exportEvents($request);
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['โครงการ', 'หัวข้อ', 'ประเภท', 'เริ่ม', 'สิ้นสุด', 'สถานที่', 'ผู้รับผิดชอบ', 'สถานะลูกค้า']);
        foreach ($events as $event) {
            fputcsv($stream, [
                $this->csvCell($event->project->code.' '.$event->project->name),
                $this->csvCell($event->title),
                $this->csvCell(ProjectEvent::TYPE_LABELS[$event->type] ?? $event->type),
                $event->starts_at->format('d/m/Y H:i'),
                $event->ends_at?->format('d/m/Y H:i') ?: '',
                $this->csvCell($event->location ?: ''),
                $this->csvCell($event->assignee?->name ?: ''),
                $this->csvCell(ProjectEvent::CUSTOMER_RESPONSE_LABELS[$event->customer_response] ?? 'รอตอบรับ'),
            ]);
        }
        rewind($stream);
        $contents = stream_get_contents($stream) ?: '';
        fclose($stream);

        return response($contents, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="project-calendar-'.$month->format('Y-m').'.csv"',
        ]);
    }

    public function exportIcs(Request $request): Response
    {
        [$events, $month] = $this->exportEvents($request);
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//34 Build Master//Project Calendar//TH', 'CALSCALE:GREGORIAN'];
        foreach ($events as $event) {
            $lines = array_merge($lines, [
                'BEGIN:VEVENT',
                'UID:project-event-'.$event->id.'@34buildermaster.com',
                'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
                'DTSTART:'.$event->starts_at->utc()->format('Ymd\THis\Z'),
                'DTEND:'.($event->ends_at ?: $event->starts_at->copy()->addHour())->utc()->format('Ymd\THis\Z'),
                'SUMMARY:'.$this->escapeIcs($event->project->code.' - '.$event->title),
                'DESCRIPTION:'.$this->escapeIcs($event->description ?: ''),
                'LOCATION:'.$this->escapeIcs($event->location ?: ''),
                'END:VEVENT',
            ]);
        }
        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="project-calendar-'.$month->format('Y-m').'.ics"',
        ]);
    }

    public function print(Request $request): View
    {
        [$events, $month] = $this->exportEvents($request);

        return view('admin.calendar.print', compact('events', 'month'));
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

    private function calendarItems(Collection $projectIds, Collection $events, CarbonImmutable $from, CarbonImmutable $to, bool $eventsOnly = false): Collection
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

        if ($eventsOnly) {
            return $items->sortBy(fn (array $item) => $item['date'].' '.($item['time'] ?? '23:59'))->values();
        }

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

    private function storeAttachments(Request $request, ProjectEvent $event, MediaStorage $storage): void
    {
        foreach ($request->file('attachments', []) as $upload) {
            $file = $storage->store($upload, "project-events/{$event->project_id}/{$event->id}", 'private', $request->user());
            $event->attachments()->create([
                'stored_file_id' => $file->id,
                'uploaded_by' => $request->user()->id,
            ]);
        }
    }

    private function filters(Request $request, Collection $projects): array
    {
        $projectId = (int) $request->integer('project');

        return [
            'project' => $projectId && $projects->contains('id', $projectId) ? $projectId : null,
            'type' => array_key_exists($request->string('type')->toString(), ProjectEvent::TYPE_LABELS) ? $request->string('type')->toString() : null,
            'assignee' => $request->filled('assignee') ? (int) $request->integer('assignee') : null,
            'response' => array_key_exists($request->string('response')->toString(), ProjectEvent::CUSTOMER_RESPONSE_LABELS) ? $request->string('response')->toString() : ($request->string('response')->toString() === 'pending' ? 'pending' : null),
        ];
    }

    private function filteredEvents($query, Collection $projectIds, array $filters)
    {
        return $query
            ->whereIn('project_id', $projectIds)
            ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
            ->when($filters['assignee'], fn ($query, $assignee) => $query->where('assigned_to', $assignee))
            ->when($filters['response'] === 'pending', fn ($query) => $query->whereNull('customer_response'))
            ->when($filters['response'] && $filters['response'] !== 'pending', fn ($query) => $query->where('customer_response', $filters['response']));
    }

    private function hasEventOnlyFilters(array $filters): bool
    {
        return filled($filters['type']) || filled($filters['assignee']) || filled($filters['response']);
    }

    private function exportEvents(Request $request): array
    {
        $month = $this->calendarMonth($request->string('month')->toString());
        $projects = $this->availableProjects($request->user())->orderBy('name')->get(['id', 'code', 'name']);
        $filters = $this->filters($request, $projects);
        $projectIds = $filters['project'] ? collect([$filters['project']]) : $projects->pluck('id');
        $events = $this->filteredEvents(ProjectEvent::query(), $projectIds, $filters)
            ->where('status', '<>', 'cancelled')
            ->whereBetween('starts_at', [$month->startOfMonth(), $month->endOfMonth()])
            ->with(['project:id,code,name', 'assignee:id,name'])
            ->orderBy('starts_at')
            ->get();

        return [$events, $month];
    }

    private function escapeIcs(string $value): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', '\\n', '\\n'], $value);
    }

    private function csvCell(string $value): string
    {
        return preg_match('/^[=+\-@]/', ltrim($value)) === 1 ? "'".$value : $value;
    }
}
