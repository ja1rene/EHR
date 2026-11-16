<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(['staff', 'doctor']);

header('Content-Type: application/json');

$date   = $_GET['date'] ?? '';
$status = $_GET['status'] ?? '';
$search = clean_input($_GET['search'] ?? '');

$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, a.notes,
               p.patient_id, p.patient_code, p.first_name AS patient_first, p.last_name AS patient_last,
               u.user_id AS doctor_id, u.full_name AS doctor_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        JOIN users u ON a.doctor_id = u.user_id
        WHERE 1=1";
$params = [];
$types  = "";

// Doctors only ever see their own appointments — this is the actual
// IDOR/authorization boundary for this endpoint, not just role-gating.
if ($_SESSION['role'] === 'doctor') {
    $sql .= " AND a.doctor_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= "i";
}

if ($date !== '') {
    $sql .= " AND a.appointment_date = ?";
    $params[] = $date;
    $types .= "s";
}

if ($status !== '') {
    $sql .= " AND a.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($search !== '') {
    $sql .= " AND (p.first_name LIKE CONCAT('%', ?, '%') OR p.last_name LIKE CONCAT('%', ?, '%') OR p.patient_code LIKE CONCAT('%', ?, '%'))";
    $params[] = $search; $params[] = $search; $params[] = $search;
    $types .= "sss";
}

$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 200";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "appointments" => $appointments]);