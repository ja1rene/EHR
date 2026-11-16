<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff');

header('Content-Type: application/json');

function respond(array $data): void {
    echo json_encode($data);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(["success" => false, "message" => "Invalid request method."]);
}

if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    respond(["success" => false, "message" => "Your session has expired. Please try again.", "csrf_token" => current_csrf_token()]);
}

$patient_id      = (int)($_POST['patient_id'] ?? 0);
$consultation_id = !empty($_POST['consultation_id']) ? (int)$_POST['consultation_id'] : null;
$test_name       = clean_input($_POST['test_name'] ?? '');
$test_date       = $_POST['test_date'] ?? '';
$result_value    = clean_input($_POST['result_value'] ?? '');
$reference_range = clean_input($_POST['reference_range'] ?? '');
$remarks         = clean_input($_POST['remarks'] ?? '');
$status          = $_POST['status'] ?? 'pending';

$errors = [];

$pCheck = $conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ? AND status = 'active'");
$pCheck->bind_param("i", $patient_id);
$pCheck->execute();
if ($pCheck->get_result()->num_rows !== 1) $errors[] = "Patient not found.";
$pCheck->close();

if ($consultation_id !== null) {
    $cCheck = $conn->prepare("SELECT consultation_id FROM consultations WHERE consultation_id = ? AND patient_id = ?");
    $cCheck->bind_param("ii", $consultation_id, $patient_id);
    $cCheck->execute();
    if ($cCheck->get_result()->num_rows !== 1) $errors[] = "Selected consultation could not be verified.";
    $cCheck->close();
}

if ($test_name === '') $errors[] = "Test name is required.";

$dateTimestamp = strtotime($test_date);
if ($test_date === '' || $dateTimestamp === false) {
    $errors[] = "A valid test date is required.";
}

if (!in_array($status, ['pending', 'completed'], true)) {
    $status = 'pending';
}

if (!empty($errors)) {
    respond(["success" => false, "message" => implode(' ', $errors), "csrf_token" => current_csrf_token()]);
}

$stmt = $conn->prepare(
    "INSERT INTO lab_results
        (patient_id, consultation_id, test_name, test_date, result_value, reference_range, remarks, status, recorded_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "iissssssi",
    $patient_id, $consultation_id, $test_name, $test_date, $result_value, $reference_range, $remarks, $status, $_SESSION['user_id']
);
$stmt->execute();
$newId = $conn->insert_id;
$stmt->close();

log_action('lab_result_recorded', 'Laboratory', (string)$newId, "Recorded $test_name for patient #$patient_id");

respond(["success" => true, "lab_result_id" => $newId, "csrf_token" => current_csrf_token()]);