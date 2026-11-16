<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('doctor');

header('Content-Type: application/json');

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo   = $_GET['date_to'] ?? date('Y-m-d');
$doctorId = $_SESSION['user_id'];

if (strtotime($dateFrom) === false || strtotime($dateTo) === false) {
    echo json_encode(["success" => false, "message" => "Invalid date range."]);
    exit();
}

// Consultation count within range — scoped to THIS doctor only, same
// IDOR boundary used everywhere else a doctor queries their own data.
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS cnt FROM consultations
     WHERE doctor_id = ? AND DATE(created_at) BETWEEN ? AND ?"
);
$stmt->bind_param("iss", $doctorId, $dateFrom, $dateTo);
$stmt->execute();
$totalConsultations = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Distinct patients seen within range.
$stmt = $conn->prepare(
    "SELECT COUNT(DISTINCT patient_id) AS cnt FROM consultations
     WHERE doctor_id = ? AND DATE(created_at) BETWEEN ? AND ?"
);
$stmt->bind_param("iss", $doctorId, $dateFrom, $dateTo);
$stmt->execute();
$distinctPatients = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Appointment outcome breakdown for this doctor within range.
$stmt = $conn->prepare(
    "SELECT status, COUNT(*) AS cnt FROM appointments
     WHERE doctor_id = ? AND appointment_date BETWEEN ? AND ?
     GROUP BY status"
);
$stmt->bind_param("iss", $doctorId, $dateFrom, $dateTo);
$stmt->execute();
$statusRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$statusBreakdown = ['scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
foreach ($statusRows as $row) {
    $statusBreakdown[$row['status']] = (int)$row['cnt'];
}

// Diagnosis frequency — grouped by exact text match. This is a real
// limitation of a free-text diagnosis field (no coded catalog), so
// "Hypertension" and "hypertension, uncontrolled" count separately.
// Flagged clearly in the UI rather than presented as more precise
// than it is.
$stmt = $conn->prepare(
    "SELECT diagnosis, COUNT(*) AS cnt FROM consultations
     WHERE doctor_id = ? AND DATE(created_at) BETWEEN ? AND ?
       AND diagnosis IS NOT NULL AND diagnosis != ''
     GROUP BY diagnosis
     ORDER BY cnt DESC, diagnosis ASC
     LIMIT 10"
);
$stmt->bind_param("iss", $doctorId, $dateFrom, $dateTo);
$stmt->execute();
$diagnosisFrequency = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    "success" => true,
    "date_from" => $dateFrom,
    "date_to" => $dateTo,
    "total_consultations" => (int)$totalConsultations,
    "distinct_patients" => (int)$distinctPatients,
    "status_breakdown" => $statusBreakdown,
    "diagnosis_frequency" => $diagnosisFrequency,
]);