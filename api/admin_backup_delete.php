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

$requested = basename($_POST['filename'] ?? '');

if ($requested === '' || pathinfo($requested, PATHINFO_EXTENSION) !== 'sql') {
    respond(["success" => false, "message" => "Invalid file.", "csrf_token" => current_csrf_token()]);
}

$path = BACKUP_DIR . $requested;

if (!file_exists($path)) {
    respond(["success" => false, "message" => "File not found.", "csrf_token" => current_csrf_token()]);
}

unlink($path);

$del = $conn->prepare("DELETE FROM backup_logs WHERE filename = ?");
if ($del !== false) {
    $del->bind_param("s", $requested);
    $del->execute();
    $del->close();
}
// If backup_logs doesn't exist, the file itself is still deleted
// successfully — the log entry just won't exist to clean up, which
// is fine, not worth failing the whole delete over.

log_action('backup_deleted', 'Backup & Recovery', $requested, "Deleted backup file $requested");

respond(["success" => true, "csrf_token" => current_csrf_token()]);