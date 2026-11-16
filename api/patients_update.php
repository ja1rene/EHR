<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff'); // only staff edits demographic info (per our RBAC matrix)

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

$id = (int)($_POST['patient_id'] ?? 0);

// IDOR check: confirm this patient actually exists and is active before
// touching it — never trust that a submitted ID is valid just because
// it was submitted (Security 21.8).
$check = $conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ? AND status = 'active'");
$check->bind_param("i", $id);
$check->execute();
if ($check->get_result()->num_rows !== 1) {
    http_response_code(404);
    respond(["success" => false, "message" => "Patient not found."]);
}
$check->close();

$first_name  = clean_input($_POST['first_name'] ?? '');
$middle_name = clean_input($_POST['middle_name'] ?? '');
$last_name   = clean_input($_POST['last_name'] ?? '');
$dob         = $_POST['date_of_birth'] ?? '';
$sex         = $_POST['sex'] ?? '';
$civil       = $_POST['civil_status'] ?? '';
$blood_type  = $_POST['blood_type'] ?? 'unknown';
$contact     = clean_input($_POST['contact_number'] ?? '');
$address     = clean_input($_POST['address'] ?? '');
$ec_name     = clean_input($_POST['emergency_contact_name'] ?? '');
$ec_number   = clean_input($_POST['emergency_contact_number'] ?? '');
$allergies   = clean_input($_POST['allergies'] ?? '');
$conditions  = clean_input($_POST['medical_conditions'] ?? '');

$errors = [];
if ($first_name === '') $errors[] = "First name is required.";
if ($last_name === '')  $errors[] = "Last name is required.";
if (!in_array($sex, ['male', 'female'], true)) $errors[] = "A valid sex must be selected.";

$dobTimestamp = strtotime($dob);
if ($dob === '' || $dobTimestamp === false) {
    $errors[] = "A valid date of birth is required.";
} elseif ($dobTimestamp > time()) {
    $errors[] = "Date of birth cannot be in the future.";
}

$validCivil = ['single', 'married', 'widowed', 'separated'];
if (!in_array($civil, $validCivil, true)) $civil = null;

$validBloodTypes = ['A+','A-','B+','B-','AB+','AB-','O+','O-','unknown'];
if (!in_array($blood_type, $validBloodTypes, true)) $blood_type = 'unknown';

if (!empty($errors)) {
    respond(["success" => false, "message" => implode(' ', $errors), "csrf_token" => current_csrf_token()]);
}

$stmt = $conn->prepare(
    "UPDATE patients SET
        first_name = ?, middle_name = ?, last_name = ?, date_of_birth = ?, sex = ?,
        civil_status = ?, contact_number = ?, address = ?,
        emergency_contact_name = ?, emergency_contact_number = ?,
        blood_type = ?, allergies = ?, medical_conditions = ?
     WHERE patient_id = ?"
);
$stmt->bind_param(
    "sssssssssssssi",
    $first_name, $middle_name, $last_name, $dob, $sex, $civil,
    $contact, $address, $ec_name, $ec_number, $blood_type, $allergies, $conditions,
    $id
);
$stmt->execute();
$stmt->close();

log_action('patient_updated', 'Patients', (string)$id, "Updated demographic info");

respond(["success" => true, "csrf_token" => current_csrf_token()]);