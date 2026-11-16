<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

header('Content-Type: application/json');

// Defensive check: if backup_logs doesn't exist yet, respond with a
// clear JSON error instead of letting the query calls below fatal-crash
// and dump raw PHP error text into what should be a clean JSON response.
$tableCheck = $conn->query("SHOW TABLES LIKE 'backup_logs'");
if ($tableCheck->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "The backup_logs table doesn't exist yet. Run the CREATE TABLE backup_logs statement in phpMyAdmin.",
        "logs" => [],
        "total_success" => 0,
        "this_week" => 0,
        "last_backup_at" => null,
    ]);
    exit();
}

$logs = $conn->query(
    "SELECT bl.backup_log_id, bl.filename, bl.file_size, bl.status, bl.notes, bl.created_at,
            u.full_name AS created_by_name
     FROM backup_logs bl
     LEFT JOIN users u ON bl.created_by = u.user_id
     ORDER BY bl.created_at DESC
     LIMIT 50"
)->fetch_all(MYSQLI_ASSOC);

$totalSuccess = $conn->query("SELECT COUNT(*) AS c FROM backup_logs WHERE status = 'success'")->fetch_assoc()['c'];
$thisWeek = $conn->query("SELECT COUNT(*) AS c FROM backup_logs WHERE status = 'success' AND created_at >= (NOW() - INTERVAL 7 DAY)")->fetch_assoc()['c'];
$lastBackup = $conn->query("SELECT created_at FROM backup_logs WHERE status = 'success' ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

echo json_encode([
    "success" => true,
    "logs" => $logs,
    "total_success" => (int)$totalSuccess,
    "this_week" => (int)$thisWeek,
    "last_backup_at" => $lastBackup['created_at'] ?? null,
]);