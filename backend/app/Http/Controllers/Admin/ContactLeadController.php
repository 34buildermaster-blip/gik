<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContactLead;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactLeadController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $statuses = array_keys(ContactLead::STATUS_LABELS);

        $leads = ContactLead::query()
            ->with(['assignee:id,name', 'convertedProject:id,code,name'])
            ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                    if (ctype_digit($search)) {
                        $nested->orWhere('id', (int) $search);
                    }
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = collect($statuses)
            ->mapWithKeys(fn (string $key): array => [$key => ContactLead::query()->where('status', $key)->count()])
            ->all();

        return view('admin.contact-leads.index', [
            'leads' => $leads,
            'counts' => $counts,
            'search' => $search,
            'status' => $status,
            'statusLabels' => ContactLead::STATUS_LABELS,
            'customers' => User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']),
            'typeLabels' => Project::TYPE_LABELS,
        ]);
    }

    public function update(Request $request, ContactLead $contactLead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(ContactLead::STATUS_LABELS))],
            'admin_note' => ['nullable', 'string', 'max:5000'],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);

        $status = $validated['status'];
        $contactLead->update([
            'status' => $status,
            'admin_note' => trim((string) ($validated['admin_note'] ?? '')) ?: null,
            'assigned_to' => $request->user()->id,
            'contacted_at' => $status === ContactLead::STATUS_NEW
                ? null
                : ($contactLead->contacted_at ?? now()),
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
        ]);

        AuditLog::record($request->user(), 'contact_lead.updated', $contactLead, "อัปเดตผู้ติดต่อ {$contactLead->name}", ['status' => $status]);

        return back()->with('success', 'บันทึกสถานะผู้ติดต่อเรียบร้อยแล้ว');
    }

    public function convert(Request $request, ContactLead $contactLead): RedirectResponse
    {
        abort_if($contactLead->converted_project_id, 422, 'ผู้ติดต่อนี้ถูกเปลี่ยนเป็นโครงการแล้ว');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('projects', 'code')],
            'project_name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Project::TYPE_LABELS))],
            'customer_id' => ['required', Rule::exists('users', 'id')->where('role', 'user')],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $project = DB::transaction(function () use ($request, $contactLead, $data): Project {
            $project = Project::create([
                'manager_id' => $request->user()->id,
                'code' => $data['code'],
                'name' => $data['project_name'],
                'type' => $data['type'],
                'address' => $data['address'] ?? null,
                'status' => 'preparing',
                'progress_percent' => 0,
                'summary' => $contactLead->message,
            ]);
            $project->customers()->attach($data['customer_id']);
            $contactLead->update([
                'status' => ContactLead::STATUS_CONVERTED,
                'converted_project_id' => $project->id,
                'converted_at' => now(),
                'assigned_to' => $request->user()->id,
                'next_follow_up_at' => null,
            ]);

            return $project;
        });

        AuditLog::record($request->user(), 'contact_lead.converted', $contactLead, "เปลี่ยนผู้ติดต่อ {$contactLead->name} เป็นโครงการ {$project->code}", ['project_id' => $project->id]);

        return redirect()->route('admin.projects.show', $project)->with('success', 'สร้างโครงการจากผู้ติดต่อเรียบร้อยแล้ว');
    }

    public function destroy(ContactLead $contactLead): RedirectResponse
    {
        AuditLog::record(auth()->user(), 'contact_lead.deleted', $contactLead, "ลบผู้ติดต่อ {$contactLead->name}");
        $contactLead->delete();

        return back()->with('success', 'ลบข้อมูลผู้ติดต่อเรียบร้อยแล้ว');
    }
}
