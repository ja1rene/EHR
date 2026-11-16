<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/backup_config.php";
require_role('admin'); // <-- the actual fix: nobody reaches this file without being an authenticated admin

$requested = basename($_GET['file'] ?? '');

if ($requested === '' || pathinfo($requested, PATHINFO_EXTENSION) !== 'sql') {
    http_response_code(400);
    die('Invalid file.');
}

$path = BACKUP_DIR . $requested;

if (!file_exists($path)) {
    http_response_code(404);
    die('File not found.');
}

log_action('backup_downloaded', 'Backup & Recovery', $requested, "Downloaded backup file $requested");

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $requested . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store, no-cache, must-revalidate');
readfile($path);
exit();