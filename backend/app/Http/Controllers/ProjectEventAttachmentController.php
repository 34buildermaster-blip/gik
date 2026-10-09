<?php

namespace App\Http\Controllers;

use App\Models\ProjectEventAttachment;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectEventAttachmentController extends Controller
{
    public function show(Request $request, ProjectEventAttachment $attachment, MediaStorage $storage): StreamedResponse
    {
        $attachment->loadMissing(['file', 'event.project']);
        $project = $attachment->event->project;
        $user = $request->user();
        $canView = $user->isStaff()
            ? $project->canBeManagedBy($user)
            : $attachment->event->customer_visible && $project->customers()->where('users.id', $user->id)->exists();
        abort_unless($canView && $attachment->file, 403);

        return $storage->response($attachment->file, 'private, max-age=600');
    }

    public function destroy(Request $request, ProjectEventAttachment $attachment, MediaStorage $storage): RedirectResponse
    {
        $attachment->loadMissing(['file', 'event.project']);
        abort_unless($attachment->event->project->canBeManagedBy($request->user()), 403);
        $file = $attachment->file;
        $attachment->delete();
        $storage->delete($file);

        return back()->with('success', 'ลบไฟล์แนบนัดหมายเรียบร้อยแล้ว');
    }
}
