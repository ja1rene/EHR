<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(['staff', 'doctor']);

header('Content-Type: application/json');

$appointment_id = (int)($_GET['appointment_id'] ?? 0);

if ($appointment_id <= 0) {
    echo json_encode(["success" => false, "message" => "Appointment ID required."]);
    exit();
}

// Prefer vitals recorded specifically for this appointment. If none were
// recorded against the appointment directly, fall back to the patient's
// most recent vitals overall — still useful reference for the doctor.
$stmt = $conn->prepare(
    "SELECT blood_pressure, temperature_c, pulse_rate, respiratory_rate, oxygen_saturation, height_cm, weight_kg, recorded_at
     FROM vitals
     WHERE appointment_id = ?
     ORDER BY recorded_at DESC LIMIT 1"
);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo json_encode(["success" => true, "vitals" => $result->fetch_assoc(), "source" => "this_appointment"]);
    $stmt->close();
    exit();
}
$stmt->close();

$patientStmt = $conn->prepare("SELECT patient_id FROM appointments WHERE appointment_id = ?");
$patientStmt->bind_param("i", $appointment_id);
$patientStmt->execute();
$apptRow = $patientStmt->get_result()->fetch_assoc();
$patientStmt->close();

if (!$apptRow) {
    echo json_encode(["success" => false, "message" => "Appointment not found."]);
    exit();
}

$fallback = $conn->prepare(
    "SELECT blood_pressure, temperature_c, pulse_rate, respiratory_rate, oxygen_saturation, height_cm, weight_kg, recorded_at
     FROM vitals
     WHERE patient_id = ?
     ORDER BY recorded_at DESC LIMIT 1"
);
$fallback->bind_param("i", $apptRow['patient_id']);
$fallback->execute();
$fallbackResult = $fallback->get_result();

if ($fallbackResult->num_rows === 1) {
    echo json_encode(["success" => true, "vitals" => $fallbackResult->fetch_assoc(), "source" => "previous_visit"]);
} else {
    echo json_encode(["success" => false, "message" => "No vitals recorded yet."]);
}
$fallback->close();