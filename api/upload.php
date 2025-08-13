<?php
// diziportal - upload API
// Developer: DiziPortal.Com
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
allow_cors();
require_admin();

$cfg = app_config();
$up = $cfg['uploads'];

if (!isset($_FILES['file'])) {
    json_response(['error' => 'Dosya yok'], 400);
}
$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'Yükleme hatası'], 400);
}
if ($file['size'] > $up['max_bytes']) {
    json_response(['error' => 'Dosya çok büyük'], 400);
}
$mime = mime_content_type($file['tmp_name']);
if (!in_array($mime, $up['allowed_mime'], true)) {
    json_response(['error' => 'İzin verilmeyen tür: ' . $mime], 400);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin';
$name = 'up_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
$target = rtrim($up['path'], '/').'/'.$name;
if (!is_dir($up['path'])) @mkdir($up['path'], 0775, true);
if (!move_uploaded_file($file['tmp_name'], $target)) {
    json_response(['error' => 'Kaydedilemedi'], 500);
}
$url = rtrim($up['base_url'], '/').'/'.$name;
json_response(['ok' => true, 'url' => $url]);