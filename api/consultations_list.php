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
    "SELECT c.consultation_id, c.chief_complaint, c.symptoms, c.physical_exam,
            c.diagnosis, c.treatment_plan, c.doctor_notes, c.created_at,
            u.full_name AS doctor_name
     FROM consultations c
     JOIN users u ON c.doctor_id = u.user_id
     WHERE c.patient_id = ?
     ORDER BY c.created_at DESC"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$consultations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "consultations" => $consultations]);