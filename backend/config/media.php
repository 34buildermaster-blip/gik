<?php

return [
    'driver' => env('MEDIA_STORAGE_DRIVER', 'local'),

    'local' => [
        'disk' => env('MEDIA_LOCAL_DISK', 'local'),
    ],

    'images' => [
        'optimize' => env('MEDIA_IMAGE_OPTIMIZE', true),
        'quality' => (int) env('MEDIA_IMAGE_QUALITY', 82),
        'max_width' => (int) env('MEDIA_IMAGE_MAX_WIDTH', 2560),
        'max_height' => (int) env('MEDIA_IMAGE_MAX_HEIGHT', 2560),
    ],

    'google' => [
        'auth' => env('GOOGLE_DRIVE_AUTH', 'service_account'),
        'credentials_path' => env(
            'GOOGLE_DRIVE_CREDENTIALS_PATH',
            storage_path('app/google/credentials.json'),
        ),
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'scope' => env('GOOGLE_DRIVE_SCOPE', 'https://www.googleapis.com/auth/drive.file'),
        'chunk_size' => (int) env('GOOGLE_DRIVE_CHUNK_SIZE', 8 * 1024 * 1024),
    ],
];
