<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->toString();
        $baseQuery = User::query()->where('role', 'user');

        return view('admin.customers.index', [
            'customers' => (clone $baseQuery)
                ->with(['projects:id,name,code,status,progress_percent,updated_at'])
                ->withCount('allProjects as projects_count')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('billing_name', 'like', "%{$search}%");
                    });
                })
                ->when(array_key_exists($status, User::CUSTOMER_STATUS_LABELS), fn ($query) => $query->where('customer_status', $status))
                ->latest('updated_at')
                ->paginate(12)
                ->withQueryString(),
            'search' => $search,
            'status' => $status,
            'statusLabels' => User::CUSTOMER_STATUS_LABELS,
            'totalCustomers' => (clone $baseQuery)->count(),
            'activeCustomers' => (clone $baseQuery)->where('customer_status', 'active')->count(),
            'prospectCustomers' => (clone $baseQuery)->where('customer_status', 'prospect')->count(),
            'inactiveCustomers' => (clone $baseQuery)->where('customer_status', 'inactive')->count(),
        ]);
    }

    public function show(User $customer): View
    {
        $this->ensureCustomer($customer);
        $customer->load([
            'allProjects' => fn ($query) => $query
                ->withTrashed()
                ->with('manager:id,name')
                ->withCount(['updates', 'documents', 'issues'])
                ->latest('updated_at'),
        ]);

        $projectIds = $customer->allProjects->pluck('id');
        $latestUpdates = ProjectUpdate::query()
            ->whereIn('project_id', $projectIds)
            ->where('status', 'published')
            ->with('creator:id,name')
            ->latest('work_performed_at')
            ->get()
            ->unique('project_id')
            ->keyBy('project_id');

        return view('admin.customers.show', [
            'customer' => $customer,
            'latestUpdates' => $latestUpdates,
            'statusLabels' => User::CUSTOMER_STATUS_LABELS,
            'contactChannelLabels' => User::CONTACT_CHANNEL_LABELS,
            'projectStatusLabels' => Project::STATUS_LABELS,
            'projectTypeLabels' => Project::TYPE_LABELS,
            'documentCount' => ProjectDocument::query()->whereIn('project_id', $projectIds)->count(),
        ]);
    }

    public function edit(User $customer): View
    {
        $this->ensureCustomer($customer);

        return view('admin.customers.edit', [
            'customer' => $customer,
            'statusLabels' => User::CUSTOMER_STATUS_LABELS,
            'contactChannelLabels' => User::CONTACT_CHANNEL_LABELS,
        ]);
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        $this->ensureCustomer($customer);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'line_recipient_id' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'billing_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'preferred_contact_channel' => ['required', Rule::in(array_keys(User::CONTACT_CHANNEL_LABELS))],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'customer_status' => ['required', Rule::in(array_keys(User::CUSTOMER_STATUS_LABELS))],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $before = $customer->only(array_keys($data));
        $customer->update($data);
        $changedFields = collect($data)
            ->keys()
            ->filter(fn (string $field): bool => ($before[$field] ?? null) !== $customer->{$field})
            ->values()
            ->all();

        AuditLog::record(
            $request->user(),
            'customer.profile_updated',
            $customer,
            "อัปเดตข้อมูลลูกค้า {$customer->name}",
            ['changed_fields' => $changedFields],
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'บันทึกข้อมูลลูกค้าเรียบร้อยแล้ว');
    }

    private function ensureCustomer(User $customer): void
    {
        abort_unless($customer->role === 'user', 404);
    }
}
