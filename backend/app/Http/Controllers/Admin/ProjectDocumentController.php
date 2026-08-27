<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentController extends Controller
{
    public function store(Request $request, Project $project, MediaStorage $storage): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(ProjectDocument::CATEGORY_LABELS))],
            'version' => ['required', 'string', 'max:40'],
            'visibility' => ['required', Rule::in(array_keys(ProjectDocument::VISIBILITY_LABELS))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp'],
        ]);

        $storedFile = $storage->store($data['file'], "project-documents/{$project->id}", 'private', $request->user());
        $document = $project->documents()->create([
            'stored_file_id' => $storedFile->id,
            'uploaded_by' => $request->user()->id,
            'title' => $data['title'],
            'category' => $data['category'],
            'version' => $data['version'],
            'visibility' => $data['visibility'],
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLog::record($request->user(), 'project_document.created', $document, "เพิ่มเอกสาร {$document->title}", ['project_id' => $project->id]);

        return back()->with('success', 'เพิ่มเอกสารโครงการเรียบร้อยแล้ว');
    }

    public function show(Request $request, ProjectDocument $document, MediaStorage $storage): StreamedResponse
    {
        $document->loadMissing(['project', 'file']);
        $user = $request->user();
        $canView = $user->isStaff()
            ? $document->project->canBeManagedBy($user)
            : $document->visibility === 'customer' && $document->project->customers()->where('users.id', $user->id)->exists();

        abort_unless($canView && $document->file, 403);

        AuditLog::record($user, 'project_document.viewed', $document, "เปิดเอกสาร {$document->title}");

        return $storage->response($document->file, 'private, no-store', 'attachment');
    }

    public function destroy(Request $request, Project $project, ProjectDocument $document, MediaStorage $storage): RedirectResponse
    {
        abort_unless($document->project_id === $project->id, 404);
        $document->loadMissing('file');
        AuditLog::record($request->user(), 'project_document.deleted', $document, "ลบเอกสาร {$document->title}", ['project_id' => $project->id]);
        $file = $document->file;
        $document->delete();
        $storage->delete($file);

        return back()->with('success', 'ลบเอกสารโครงการเรียบร้อยแล้ว');
    }
}
