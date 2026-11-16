<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff'); // only staff registers patients (per our RBAC matrix)

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

$first_name    = clean_input($_POST['first_name'] ?? '');
$middle_name   = clean_input($_POST['middle_name'] ?? '');
$last_name     = clean_input($_POST['last_name'] ?? '');
$dob           = $_POST['date_of_birth'] ?? '';
$sex           = $_POST['sex'] ?? '';
$blood_type    = $_POST['blood_type'] ?? 'unknown';
$contact       = clean_input($_POST['contact_number'] ?? '');
$address       = clean_input($_POST['address'] ?? '');
$ec_name       = clean_input($_POST['emergency_contact_name'] ?? '');
$ec_number     = clean_input($_POST['emergency_contact_number'] ?? '');
$allergies     = clean_input($_POST['allergies'] ?? '');
$conditions    = clean_input($_POST['medical_conditions'] ?? '');

// ── Server-side validation — never trust the client-side `required` alone ──
$errors = [];
if ($first_name === '') $errors[] = "First name is required.";
if ($last_name === '')  $errors[] = "Last name is required.";
if (!in_array($sex, ['male', 'female'], true)) $errors[] = "A valid sex must be selected.";

$validBloodTypes = ['A+','A-','B+','B-','AB+','AB-','O+','O-','unknown'];
if (!in_array($blood_type, $validBloodTypes, true)) $blood_type = 'unknown';

$dobTimestamp = strtotime($dob);
if ($dob === '' || $dobTimestamp === false) {
    $errors[] = "A valid date of birth is required.";
} elseif ($dobTimestamp > time()) {
    $errors[] = "Date of birth cannot be in the future.";
}

if (!empty($errors)) {
    respond(["success" => false, "message" => implode(' ', $errors), "csrf_token" => current_csrf_token()]);
}

// ── Generate a simple sequential patient code, e.g. PT-2026-0001 ──
$year = date('Y');
$countStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM patients WHERE YEAR(created_at) = ?");
$countStmt->bind_param("i", $year);
$countStmt->execute();
$count = $countStmt->get_result()->fetch_assoc()['cnt'];
$countStmt->close();

$patient_code = sprintf("PT-%s-%04d", $year, $count + 1);

$stmt = $conn->prepare(
    "INSERT INTO patients
        (patient_code, first_name, middle_name, last_name, date_of_birth, sex,
         contact_number, address, emergency_contact_name, emergency_contact_number,
         blood_type, allergies, medical_conditions, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "sssssssssssssi", // 13 strings + 1 integer (created_by), matching the 14 placeholders above
    $patient_code, $first_name, $middle_name, $last_name, $dob, $sex,
    $contact, $address, $ec_name, $ec_number, $blood_type, $allergies, $conditions,
    $_SESSION['user_id']
);
$stmt->execute();

if ($stmt->error) {
    respond(["success" => false, "message" => "Could not save the patient record. Please try again.", "csrf_token" => current_csrf_token()]);
}

$newPatientId = $conn->insert_id;
$stmt->close();

log_action('patient_registered', 'Patients', (string)$newPatientId, "Registered patient $patient_code");

respond(["success" => true, "patient_code" => $patient_code, "patient_id" => $newPatientId]);