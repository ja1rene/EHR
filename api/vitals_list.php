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
    "SELECT v.vital_id, v.blood_pressure, v.temperature_c, v.pulse_rate,
            v.respiratory_rate, v.oxygen_saturation, v.height_cm, v.weight_kg,
            v.recorded_at, u.full_name AS recorded_by_name
     FROM vitals v
     JOIN users u ON v.recorded_by = u.user_id
     WHERE v.patient_id = ?
     ORDER BY v.recorded_at DESC"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$vitals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "vitals" => $vitals]);