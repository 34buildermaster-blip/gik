<?php

$appUrl = rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/');

return [
    'google' => [
        'label' => 'Google',
        'enabled' => env('GOOGLE_LOGIN_ENABLED', false),
        'client_id' => env('GOOGLE_LOGIN_CLIENT_ID'),
        'client_secret' => env('GOOGLE_LOGIN_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_LOGIN_REDIRECT_URI', $appUrl.'/auth/google/callback'),
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'scopes' => 'openid email profile',
    ],

    'line' => [
        'label' => 'LINE',
        'enabled' => env('LINE_LOGIN_ENABLED', false),
        'client_id' => env('LINE_LOGIN_CHANNEL_ID'),
        'client_secret' => env('LINE_LOGIN_CHANNEL_SECRET'),
        'redirect_uri' => env('LINE_LOGIN_REDIRECT_URI', $appUrl.'/auth/line/callback'),
        'authorize_url' => 'https://access.line.me/oauth2/v2.1/authorize',
        'token_url' => 'https://api.line.me/oauth2/v2.1/token',
        'verify_url' => 'https://api.line.me/oauth2/v2.1/verify',
        'scopes' => env('LINE_LOGIN_REQUEST_EMAIL', false) ? 'openid profile email' : 'openid profile',
    ],
];
