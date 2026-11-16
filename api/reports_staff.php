<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff');

header('Content-Type: application/json');

$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // default: start of this month
$dateTo   = $_GET['date_to'] ?? date('Y-m-d');    // default: today

if (strtotime($dateFrom) === false || strtotime($dateTo) === false) {
    echo json_encode(["success" => false, "message" => "Invalid date range."]);
    exit();
}

// Appointment status breakdown within the range.
$stmt = $conn->prepare(
    "SELECT status, COUNT(*) AS cnt
     FROM appointments
     WHERE appointment_date BETWEEN ? AND ?
     GROUP BY status"
);
$stmt->bind_param("ss", $dateFrom, $dateTo);
$stmt->execute();
$statusRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$statusBreakdown = ['scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
foreach ($statusRows as $row) {
    $statusBreakdown[$row['status']] = (int)$row['cnt'];
}
$totalAppointments = array_sum($statusBreakdown);

// Appointments per day within the range (for a simple trend view).
$stmt = $conn->prepare(
    "SELECT appointment_date, COUNT(*) AS cnt
     FROM appointments
     WHERE appointment_date BETWEEN ? AND ?
     GROUP BY appointment_date
     ORDER BY appointment_date ASC"
);
$stmt->bind_param("ss", $dateFrom, $dateTo);
$stmt->execute();
$dailyTrend = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// New patient registrations within the range.
$stmt = $conn->prepare(
    "SELECT patient_id, patient_code, first_name, last_name, created_at
     FROM patients
     WHERE status = 'active' AND DATE(created_at) BETWEEN ? AND ?
     ORDER BY created_at DESC"
);
$stmt->bind_param("ss", $dateFrom, $dateTo);
$stmt->execute();
$newPatients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    "success" => true,
    "date_from" => $dateFrom,
    "date_to" => $dateTo,
    "total_appointments" => $totalAppointments,
    "status_breakdown" => $statusBreakdown,
    "daily_trend" => $dailyTrend,
    "new_patients_count" => count($newPatients),
    "new_patients" => $newPatients,
]);