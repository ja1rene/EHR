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
    "SELECT p.prescription_id, p.notes, p.created_at, u.full_name AS doctor_name
     FROM prescriptions p
     JOIN users u ON p.doctor_id = u.user_id
     WHERE p.patient_id = ?
     ORDER BY p.created_at DESC"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$prescriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Attach each prescription's medicine items.
foreach ($prescriptions as &$rx) {
    $itemStmt = $conn->prepare(
        "SELECT medicine_name, dosage, frequency, duration, instructions
         FROM prescription_items WHERE prescription_id = ?"
    );
    $itemStmt->bind_param("i", $rx['prescription_id']);
    $itemStmt->execute();
    $rx['items'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $itemStmt->close();
}
unset($rx);

echo json_encode(["success" => true, "prescriptions" => $prescriptions]);