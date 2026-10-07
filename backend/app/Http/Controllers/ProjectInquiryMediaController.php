<?php

namespace App\Http\Controllers;

use App\Models\ProjectInquiryAttachment;
use App\Services\MediaStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectInquiryMediaController extends Controller
{
    public function show(Request $request, ProjectInquiryAttachment $attachment, MediaStorage $storage): StreamedResponse
    {
        $attachment->loadMissing('file', 'message.inquiry.project');
        $inquiry = $attachment->message->inquiry;
        $project = $inquiry->project;
        $user = $request->user();
        $allowed = $user->isStaff()
            ? $project->canBeManagedBy($user)
            : $inquiry->customer_id === $user->id && $project->customers()->where('users.id', $user->id)->exists();
        abort_unless($allowed && $attachment->file, 403);

        return $storage->response($attachment->file, 'private, max-age=600');
    }
}
