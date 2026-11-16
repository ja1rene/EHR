<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

header('Content-Type: application/json');

// Everything here is a COUNT — never an individual patient name, record,
// or clinical detail. This matches the RBAC boundary established from
// the start of the project: Admin manages the system, not clinical data.

$totalPatients = $conn->query("SELECT COUNT(*) AS cnt FROM patients WHERE status = 'active'")->fetch_assoc()['cnt'];

$totalAppointments = $conn->query("SELECT COUNT(*) AS cnt FROM appointments")->fetch_assoc()['cnt'];
$totalConsultations = $conn->query("SELECT COUNT(*) AS cnt FROM consultations")->fetch_assoc()['cnt'];
$totalPrescriptions = $conn->query("SELECT COUNT(*) AS cnt FROM prescriptions")->fetch_assoc()['cnt'];
$totalLabResults = $conn->query("SELECT COUNT(*) AS cnt FROM lab_results")->fetch_assoc()['cnt'];

// Consultations per doctor — a name is attached here, but that's staff
// directory info (who works here), not patient/clinical information.
$consultsPerDoctor = $conn->query(
    "SELECT u.full_name, COUNT(c.consultation_id) AS cnt
     FROM users u
     LEFT JOIN consultations c ON c.doctor_id = u.user_id
     WHERE u.role = 'doctor'
     GROUP BY u.user_id, u.full_name
     ORDER BY cnt DESC"
)->fetch_all(MYSQLI_ASSOC);

// Appointment status breakdown, all-time.
$statusRows = $conn->query(
    "SELECT status, COUNT(*) AS cnt FROM appointments GROUP BY status"
)->fetch_all(MYSQLI_ASSOC);
$statusBreakdown = ['scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
foreach ($statusRows as $row) {
    $statusBreakdown[$row['status']] = (int)$row['cnt'];
}

// Audit activity volume, last 7 days.
$auditTrend = $conn->query(
    "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
     FROM audit_logs
     WHERE created_at >= (CURDATE() - INTERVAL 6 DAY)
     GROUP BY DATE(created_at)
     ORDER BY day ASC"
)->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "success" => true,
    "total_patients" => (int)$totalPatients,
    "total_appointments" => (int)$totalAppointments,
    "total_consultations" => (int)$totalConsultations,
    "total_prescriptions" => (int)$totalPrescriptions,
    "total_lab_results" => (int)$totalLabResults,
    "consults_per_doctor" => $consultsPerDoctor,
    "status_breakdown" => $statusBreakdown,
    "audit_trend" => $auditTrend,
]);