<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString();

        return view('notifications.index', [
            'notifications' => $request->user()->notifications()
                ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filter' => $filter,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();
        $contactLeadId = $item->data['contact_lead_id'] ?? null;

        if ($contactLeadId && $request->user()->isAdmin()) {
            return redirect()->route('admin.contact-leads.index', ['q' => $contactLeadId]);
        }

        $projectId = $item->data['project_id'] ?? null;

        abort_unless($projectId, 404);

        if (isset($item->data['inquiry_id'])) {
            $route = $request->user()->isStaff() ? 'admin.projects.show' : 'client.projects.show';

            return redirect()->to(route($route, ['project' => $projectId, 'inquiry' => $item->data['inquiry_id']]).'#project-inquiries');
        }

        if (isset($item->data['project_event_id'])) {
            if ($request->user()->isStaff()) {
                return redirect()->route('admin.calendar.index', ['event' => $item->data['project_event_id']]);
            }

            return redirect()->to(route('client.projects.show', $projectId).'#project-calendar');
        }

        return redirect()->route(
            $request->user()->isStaff() ? 'admin.projects.show' : 'client.projects.show',
            $projectId,
        );
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'อ่านการแจ้งเตือนทั้งหมดแล้ว');
    }
}
