<?php

namespace App\Http\Controllers;

use App\Models\StoredFile;
use App\Services\MediaStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoredFileController extends Controller
{
    public function show(StoredFile $file, MediaStorage $storage): StreamedResponse
    {
        abort_unless($file->isPublic(), 404);

        return $storage->response($file, 'public, max-age=86400');
    }
}
