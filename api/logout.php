<?php
// diziportal - admin logout API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
allow_cors();
start_session_if_needed();
$_SESSION = [];
session_destroy();
json_response(['ok' => true]);