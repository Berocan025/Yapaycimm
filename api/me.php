<?php
// diziportal - admin me API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
allow_cors();
start_session_if_needed();
json_response([
    'logged_in' => is_admin_logged_in(),
    'csrf' => csrf_token(),
]);