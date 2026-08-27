<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'HEAD', 'OPTIONS'],
    'allowed_origins' => array_filter(array_map('trim', explode(',', env(
        'FRONTEND_ORIGINS',
        'http://127.0.0.1:3000,http://localhost:3000,https://34buildermaster-blip.github.io'
    )))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => false,
];
