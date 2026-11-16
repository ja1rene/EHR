<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff'); // only staff schedules, so only staff needs this list

header('Content-Type: application/json');

$stmt = $conn->prepare(
    "SELECT user_id, full_name FROM users WHERE role = 'doctor' AND account_status = 'active' ORDER BY full_name"
);
$stmt->execute();
$doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "doctors" => $doctors]);