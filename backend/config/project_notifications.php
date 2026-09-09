<?php

return [
    'database' => true,
    'email' => env('PROJECT_EMAIL_NOTIFICATIONS', false),
    'line' => env('PROJECT_LINE_NOTIFICATIONS', false),
    'line_channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN'),
    'line_channel_secret' => env('LINE_CHANNEL_SECRET'),
    'line_add_friend_url' => env('LINE_ADD_FRIEND_URL'),
    'line_push_url' => env('LINE_PUSH_URL', 'https://api.line.me/v2/bot/message/push'),
    'line_reply_url' => env('LINE_REPLY_URL', 'https://api.line.me/v2/bot/message/reply'),
    'line_link_token_url' => env('LINE_LINK_TOKEN_URL', 'https://api.line.me/v2/bot/user/%s/linkToken'),
    'line_account_link_url' => env('LINE_ACCOUNT_LINK_URL', 'https://access.line.me/dialog/bot/accountLink'),
];
