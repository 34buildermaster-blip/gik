<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $role = $request->string('role')->toString();

        return view('admin.users.index', [
            'users' => User::query()
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->when(array_key_exists($role, User::ROLE_LABELS), fn ($query) => $query->where('role', $role))
                ->orderByRaw("CASE role WHEN 'admin' THEN 0 WHEN 'inspector' THEN 1 ELSE 2 END")
                ->latest('created_at')
                ->paginate(12)
                ->withQueryString(),
            'search' => $search,
            'role' => $role,
            'totalUsers' => User::count(),
            'adminCount' => User::where('role', 'admin')->count(),
            'inspectorCount' => User::where('role', 'inspector')->count(),
            'memberCount' => User::where('role', 'user')->count(),
            'roleLabels' => User::ROLE_LABELS,
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLE_LABELS))],
        ]);

        if ($request->user()->is($user) && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'ไม่สามารถลดสิทธิ์บัญชีที่กำลังใช้งานอยู่ได้']);
        }

        if ($user->isAdmin() && $validated['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['role' => 'ระบบต้องมีผู้ดูแลอย่างน้อย 1 บัญชี']);
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "อัปเดตสิทธิ์ของ {$user->name} เรียบร้อยแล้ว");
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roleLabels' => User::ROLE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(User::ROLE_LABELS))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $validated['password_must_change'] = true;
        $validated['password_changed_at'] = now();
        $user = User::create($validated);
        AuditLog::record($request->user(), 'user.created', $user, "สร้างบัญชี {$user->name}", ['role' => $user->role]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "สร้างบัญชี {$user->name} เรียบร้อยแล้ว");
    }
}
