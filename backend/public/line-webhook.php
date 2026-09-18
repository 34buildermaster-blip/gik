<?php

declare(strict_types=1);

// Use a dedicated public entry point so hosts can allow LINE without opening
// access to every Laravel route. The controller still validates the signature.
$_SERVER['REQUEST_URI'] = '/api/line/webhook';

require __DIR__.'/index.php';
