<?php

namespace App\Services;

use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class GoogleDriveStorage
{
    private ?Client $client = null;

    private ?Drive $drive = null;

    public function upload(string $localPath, string $name, string $mimeType, string $category): string
    {
        $folderId = $this->categoryFolder($category);
        $metadata = new DriveFile([
            'name' => Str::uuid().'-'.$this->safeName($name),
            'parents' => [$folderId],
            'appProperties' => ['category' => $category],
        ]);

        $client = $this->client();
        $client->setDefer(true);

        try {
            $request = $this->drive()->files->create($metadata, [
                'fields' => 'id',
                'supportsAllDrives' => true,
            ]);
            $upload = new MediaFileUpload(
                $client,
                $request,
                $mimeType ?: 'application/octet-stream',
                null,
                true,
                max(256 * 1024, (int) config('media.google.chunk_size')),
            );
            $upload->setFileSize(filesize($localPath) ?: 0);

            $handle = fopen($localPath, 'rb');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the uploaded file.');
            }

            try {
                $result = false;
                while (! $result && ! feof($handle)) {
                    $chunk = fread($handle, max(256 * 1024, (int) config('media.google.chunk_size')));
                    if ($chunk === false) {
                        throw new RuntimeException('Unable to read the uploaded file.');
                    }
                    $result = $upload->nextChunk($chunk);
                }
            } finally {
                fclose($handle);
            }
        } finally {
            $client->setDefer(false);
        }

        if (! $result instanceof DriveFile || ! $result->getId()) {
            throw new RuntimeException('Google Drive did not return a file ID.');
        }

        return $result->getId();
    }

    public function download(string $fileId, ?string $range = null): ResponseInterface
    {
        $headers = [];
        if ($range) {
            $headers['Range'] = $range;
        }

        return $this->client()->authorize()->request(
            'GET',
            'https://www.googleapis.com/drive/v3/files/'.rawurlencode($fileId),
            [
                'query' => ['alt' => 'media', 'supportsAllDrives' => 'true'],
                'headers' => $headers,
                'stream' => true,
                'http_errors' => false,
            ],
        );
    }

    public function exists(string $fileId): bool
    {
        try {
            $file = $this->drive()->files->get($fileId, [
                'fields' => 'id,trashed',
                'supportsAllDrives' => true,
            ]);

            return ! $file->getTrashed();
        } catch (Exception $exception) {
            if ($exception->getCode() === 404) {
                return false;
            }

            throw $exception;
        }
    }

    public function delete(string $fileId): void
    {
        if ($this->exists($fileId)) {
            $this->drive()->files->delete($fileId, ['supportsAllDrives' => true]);
        }
    }

    private function categoryFolder(string $category): string
    {
        $root = trim((string) config('media.google.folder_id'));
        if ($root === '') {
            throw new RuntimeException('GOOGLE_DRIVE_FOLDER_ID is not configured.');
        }

        $folderName = Str::of($category)->replaceMatches('/[^A-Za-z0-9_-]+/', '-')->trim('-')->toString()
            ?: 'general';
        $cacheKey = 'google-drive-folder:'.sha1($root.':'.$folderName);

        return Cache::rememberForever($cacheKey, function () use ($root, $folderName): string {
            $escapedName = str_replace(['\\', "'"], ['\\\\', "\\'"], $folderName);
            $escapedRoot = str_replace(['\\', "'"], ['\\\\', "\\'"], $root);
            $files = $this->drive()->files->listFiles([
                'q' => "name = '{$escapedName}' and '{$escapedRoot}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                'fields' => 'files(id,name)',
                'pageSize' => 1,
                'includeItemsFromAllDrives' => true,
                'supportsAllDrives' => true,
            ])->getFiles();

            if ($files !== []) {
                return $files[0]->getId();
            }

            $folder = $this->drive()->files->create(new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$root],
            ]), [
                'fields' => 'id',
                'supportsAllDrives' => true,
            ]);

            return (string) $folder->getId();
        });
    }

    private function client(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $client = new Client;
        $client->setApplicationName((string) config('app.name'));
        $client->setScopes([(string) config('media.google.scope', Drive::DRIVE_FILE)]);

        if (config('media.google.auth') === 'oauth') {
            $client->setClientId((string) config('media.google.client_id'));
            $client->setClientSecret((string) config('media.google.client_secret'));
            $token = $client->fetchAccessTokenWithRefreshToken((string) config('media.google.refresh_token'));

            if (isset($token['error'])) {
                throw new RuntimeException('Google OAuth failed: '.($token['error_description'] ?? $token['error']));
            }
        } else {
            $credentialsPath = $this->credentialsPath();
            if (! is_file($credentialsPath)) {
                throw new RuntimeException("Google service account credentials were not found at {$credentialsPath}.");
            }
            $client->setAuthConfig($credentialsPath);
        }

        return $this->client = $client;
    }

    private function drive(): Drive
    {
        return $this->drive ??= new Drive($this->client());
    }

    private function credentialsPath(): string
    {
        $path = (string) config('media.google.credentials_path');
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function safeName(string $name): string
    {
        $name = preg_replace('/[^\pL\pN._ -]+/u', '-', $name) ?: 'file';

        return Str::limit($name, 180, '');
    }
}
