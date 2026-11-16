<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/backup_config.php";
require_role('admin');

header('Content-Type: application/json');

function respond(array $data): void {
    echo json_encode($data);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(["success" => false, "message" => "Invalid request method."]);
}

if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    respond(["success" => false, "message" => "Your session has expired. Please try again.", "csrf_token" => current_csrf_token()]);
}

if (!function_exists('exec')) {
    respond(["success" => false, "message" => "This server does not allow running the restore command.", "csrf_token" => current_csrf_token()]);
}

$sourceFile = null;
$sourceLabel = null;

// Option 1: restore from a file already sitting in BACKUP_DIR.
if (!empty($_POST['restore_filename'])) {
    // basename() strips any directory traversal attempt (../, absolute
    // paths); the extension check below rejects anything that isn't a
    // .sql file even if it somehow ended up in the folder.
    $requested = basename($_POST['restore_filename']);
    if (pathinfo($requested, PATHINFO_EXTENSION) !== 'sql') {
        respond(["success" => false, "message" => "Only .sql files can be restored.", "csrf_token" => current_csrf_token()]);
    }
    $candidate = BACKUP_DIR . $requested;
    if (!file_exists($candidate)) {
        respond(["success" => false, "message" => "That backup file could not be found.", "csrf_token" => current_csrf_token()]);
    }
    $sourceFile = $candidate;
    $sourceLabel = $requested;
}
// Option 2: restore from a freshly uploaded file.
elseif (!empty($_FILES['backup_file']['name'])) {
    $uploaded = $_FILES['backup_file'];

    if ($uploaded['error'] !== UPLOAD_ERR_OK) {
        respond(["success" => false, "message" => "There was a problem uploading the file.", "csrf_token" => current_csrf_token()]);
    }
    if (strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION)) !== 'sql') {
        respond(["success" => false, "message" => "Only .sql files are allowed.", "csrf_token" => current_csrf_token()]);
    }
    $maxBytes = 100 * 1024 * 1024; // 100MB
    if ($uploaded['size'] > $maxBytes) {
        respond(["success" => false, "message" => "File is too large (max 100MB).", "csrf_token" => current_csrf_token()]);
    }

    $sourceFile = $uploaded['tmp_name'];
    $sourceLabel = basename($uploaded['name']) . ' (uploaded)';
} else {
    respond(["success" => false, "message" => "No backup file selected or uploaded.", "csrf_token" => current_csrf_token()]);
}

$passwordArg = $password !== '' ? '--password=' . escapeshellarg($password) : '--password=';

$command = escapeshellarg(BACKUP_MYSQL_PATH) .
    ' --user=' . escapeshellarg($username) . ' ' .
    $passwordArg . ' ' .
    escapeshellarg($database) .
    ' < ' . escapeshellarg($sourceFile) . ' 2>&1';

exec($command, $outputLines, $resultCode);

if ($resultCode === 0) {
    log_action('database_restored', 'Backup & Recovery', $sourceLabel, "Restored database from $sourceLabel");
    respond(["success" => true, "message" => "Database restored from $sourceLabel.", "csrf_token" => current_csrf_token()]);
} else {
    log_action('restore_failed', 'Backup & Recovery', $sourceLabel, 'Restore command returned a non-zero exit code');
    respond(["success" => false, "message" => "Restore failed. Check that the file is a valid EHR System backup.", "csrf_token" => current_csrf_token()]);
}