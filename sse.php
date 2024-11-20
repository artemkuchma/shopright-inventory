<?php

header('Content-Type: text/event-stream');
header('Connection: keep-alive');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Expires: 0');
header('Pragma: no-cache');


require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Sse.php';
require_once __DIR__ . '/app/models/Products.php';
require_once __DIR__ . '/app/helpers/SseHelper.php';
require_once __DIR__ . '/app/helpers/NotificationService.php';

// Call the static method 'sse' from the SseHelper class to start the SSE stream.
\helpers\SseHelper::sse();
