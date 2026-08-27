<?php

namespace App\Http\Controllers;

use App\Models\ProjectIssueMedia;
use App\Services\MediaStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectIssueMediaController extends Controller
{
    public function show(Request $request, ProjectIssueMedia $media, MediaStorage $storage): StreamedResponse
    {
        $media->loadMissing(['file', 'issue.project']);
        $issue = $media->issue;
        $user = $request->user();
        $canView = $user->isStaff()
            ? $issue->project->canBeManagedBy($user)
            : $issue->customer_visible && $issue->project->customers()->where('users.id', $user->id)->exists();

        abort_unless($canView && $media->file, 403);

        return $storage->response($media->file);
    }
}
