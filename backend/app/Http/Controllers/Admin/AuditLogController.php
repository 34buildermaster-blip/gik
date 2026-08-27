<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.audit-logs.index', [
            'logs' => AuditLog::query()
                ->with('user:id,name')
                ->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested
                    ->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'search' => $search,
        ]);
    }
}
