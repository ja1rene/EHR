<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(['staff', 'doctor']);

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    respond_404();
}

function respond_404(): void {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Patient not found."]);
    exit();
}

// Staff can open archived records (read-only on the profile page) so
// archived patients stay retrievable. A doctor only ever sees active ones.
$statusClause = ($_SESSION['role'] === 'staff') ? "" : " AND status = 'active'";

$stmt = $conn->prepare(
    "SELECT patient_id, patient_code, first_name, middle_name, last_name,
            date_of_birth, sex, civil_status, address, contact_number,
            emergency_contact_name, emergency_contact_number, blood_type,
            allergies, medical_conditions, status, created_at, updated_at
     FROM patients WHERE patient_id = ?" . $statusClause
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    respond_404();
}

$patient = $result->fetch_assoc();
$stmt->close();

echo json_encode(["success" => true, "patient" => $patient]);