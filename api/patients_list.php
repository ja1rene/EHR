<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(['staff', 'doctor']); // both can view; only staff can register (enforced in patients_add.php)

header('Content-Type: application/json');

$search = clean_input($_GET['search'] ?? '');

// Only staff may browse archived patients. Anyone else (including a doctor
// who edits the URL by hand) always gets the active list.
$statusFilter = $_GET['status'] ?? 'active';
if ($statusFilter !== 'archived' || $_SESSION['role'] !== 'staff') {
    $statusFilter = 'active';
}

$sql = "SELECT patient_id, patient_code, first_name, last_name, date_of_birth,
               sex, contact_number, blood_type, created_at
        FROM patients
        WHERE status = ?";
$types  = "s";
$params = [$statusFilter];

if ($search !== '') {
    $sql .= " AND (first_name LIKE CONCAT('%', ?, '%')
                OR last_name LIKE CONCAT('%', ?, '%')
                OR patient_code LIKE CONCAT('%', ?, '%'))";
    $types .= "sss";
    array_push($params, $search, $search, $search);
}

$sql .= " ORDER BY created_at DESC LIMIT 100";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$patients = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "patients" => $patients]);