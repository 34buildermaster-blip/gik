<?php

namespace App\Http\Controllers;

use App\Models\ProjectUpdateMedia;
use App\Services\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectMediaController extends Controller
{
    public function show(Request $request, ProjectUpdateMedia $media, MediaStorage $storage): StreamedResponse
    {
        $media->load('projectUpdate.project.customers:id');
        $update = $media->projectUpdate;
        $isAllowedCustomer = $update->status === 'published'
            && $update->project->customers->contains('id', $request->user()->id);
        $isAllowedStaff = $update->project->canBeManagedBy($request->user());

        abort_unless($isAllowedStaff || $isAllowedCustomer, 403);
        if ($media->storedFile) {
            return $storage->response($media->storedFile);
        }

        abort_unless(Storage::disk('local')->exists($media->path), 404);

        return Storage::disk('local')->response($media->path, $media->original_name, [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
