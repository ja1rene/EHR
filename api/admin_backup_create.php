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
    respond(["success" => false, "message" => "This server does not allow running the backup command. Contact your host or check php.ini's disable_functions setting.", "csrf_token" => current_csrf_token()]);
}

if (!is_dir(BACKUP_DIR)) {
    @mkdir(BACKUP_DIR, 0777, true);
}
if (!is_dir(BACKUP_DIR) || !is_writable(BACKUP_DIR)) {
    respond(["success" => false, "message" => "The backup folder could not be created or is not writable: " . BACKUP_DIR, "csrf_token" => current_csrf_token()]);
}

$date        = date("Y-m-d_H-i-s");
$filename    = "ehr_backup_" . $date . ".sql";
$backupFile  = BACKUP_DIR . $filename;

// $host/$username/$password/$database come from config/db.php, already
// loaded via auth.php — reusing the same credentials the app itself
// connects with, rather than duplicating them a second time anywhere.
$passwordArg = $password !== '' ? '--password=' . escapeshellarg($password) : '--password=';

$command = escapeshellarg(BACKUP_MYSQLDUMP_PATH) .
    ' --user=' . escapeshellarg($username) . ' ' .
    $passwordArg . ' ' .
    escapeshellarg($database) .
    ' > ' . escapeshellarg($backupFile) . ' 2>&1';

exec($command, $outputLines, $resultCode);

if ($resultCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
    $fileSize = round(filesize($backupFile) / 1024, 2) . " KB";

    $log = $conn->prepare(
        "INSERT INTO backup_logs (filename, file_size, created_by, status) VALUES (?, ?, ?, 'success')"
    );
    if ($log === false) {
        respond(["success" => false, "message" => "The backup_logs table doesn't exist yet. Run the CREATE TABLE backup_logs statement in phpMyAdmin, then try again. (The backup file itself was created successfully.)", "csrf_token" => current_csrf_token()]);
    }
    $log->bind_param("ssi", $filename, $fileSize, $_SESSION['user_id']);
    $log->execute();
    $log->close();

    log_action('backup_created', 'Backup & Recovery', $filename, "Created backup ($fileSize)");

    respond(["success" => true, "filename" => $filename, "file_size" => $fileSize, "csrf_token" => current_csrf_token()]);
} else {
    // Clean up a zero-byte or partial file if the command failed partway.
    if (file_exists($backupFile) && filesize($backupFile) === 0) {
        @unlink($backupFile);
    }

    $log = $conn->prepare(
        "INSERT INTO backup_logs (filename, file_size, created_by, status, notes) VALUES (?, 'N/A', ?, 'failed', 'mysqldump returned a non-zero exit code')"
    );
    if ($log === false) {
        respond(["success" => false, "message" => "Backup failed, and the backup_logs table doesn't exist yet. Run the CREATE TABLE backup_logs statement in phpMyAdmin first.", "csrf_token" => current_csrf_token()]);
    }
    $log->bind_param("si", $filename, $_SESSION['user_id']);
    $log->execute();
    $log->close();

    log_action('backup_failed', 'Backup & Recovery', $filename, 'mysqldump failed');

    respond(["success" => false, "message" => "Backup failed. Check that MySQL is running and the path in config/backup_config.php is correct.", "csrf_token" => current_csrf_token()]);
}