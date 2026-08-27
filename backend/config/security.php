<?php

return [
    'staff_2fa_required' => env('SECURITY_STAFF_2FA_REQUIRED', true),

    'upload_scan' => [
        'enabled' => env('SECURITY_UPLOAD_SCAN_ENABLED', false),
        'required' => env('SECURITY_UPLOAD_SCAN_REQUIRED', env('APP_ENV') === 'production'),
        'fail_closed' => env('SECURITY_UPLOAD_SCAN_FAIL_CLOSED', true),
        'require_clean_for_serving' => env('SECURITY_REQUIRE_CLEAN_FILES', env('APP_ENV') === 'production'),
        'binary' => env('SECURITY_CLAMAV_BINARY', 'clamscan'),
        'timeout' => (int) env('SECURITY_CLAMAV_TIMEOUT', 120),
        'quarantine_disk' => env('SECURITY_QUARANTINE_DISK', 'local'),
        'quarantine_path' => env('SECURITY_QUARANTINE_PATH', 'quarantine'),
    ],

    'headers' => [
        'hsts' => env('SECURITY_HSTS_ENABLED', false),
    ],
];
