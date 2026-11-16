<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(['staff', 'doctor']);

header('Content-Type: application/json');

$patient_id = (int)($_GET['patient_id'] ?? 0);

if ($patient_id <= 0) {
    echo json_encode(["success" => false, "message" => "Patient ID required."]);
    exit();
}

$stmt = $conn->prepare(
    "SELECT lr.lab_result_id, lr.test_name, lr.test_date, lr.result_value,
            lr.reference_range, lr.remarks, lr.status, lr.created_at,
            u.full_name AS recorded_by_name
     FROM lab_results lr
     JOIN users u ON lr.recorded_by = u.user_id
     WHERE lr.patient_id = ?
     ORDER BY lr.test_date DESC, lr.created_at DESC"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "lab_results" => $results]);