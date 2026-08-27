<?php

return [
    'database' => true,
    'email' => env('PROJECT_EMAIL_NOTIFICATIONS', false),
    'line' => env('PROJECT_LINE_NOTIFICATIONS', false),
    'line_channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN'),
    'line_push_url' => env('LINE_PUSH_URL', 'https://api.line.me/v2/bot/message/push'),
];
