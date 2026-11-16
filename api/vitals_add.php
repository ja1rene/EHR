<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(['staff', 'doctor']);

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

$patient_id     = (int)($_POST['patient_id'] ?? 0);
$appointment_id = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;

$bp          = clean_input($_POST['blood_pressure'] ?? '');
$temp        = $_POST['temperature_c'] ?? '';
$pulse       = $_POST['pulse_rate'] ?? '';
$resp        = $_POST['respiratory_rate'] ?? '';
$o2          = $_POST['oxygen_saturation'] ?? '';
$height      = $_POST['height_cm'] ?? '';
$weight      = $_POST['weight_kg'] ?? '';

// IDOR check: patient must exist and be active.
$pCheck = $conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ? AND status = 'active'");
$pCheck->bind_param("i", $patient_id);
$pCheck->execute();
if ($pCheck->get_result()->num_rows !== 1) {
    respond(["success" => false, "message" => "Patient not found.", "csrf_token" => current_csrf_token()]);
}
$pCheck->close();

// If an appointment was specified, confirm it exists and belongs to this
// patient — prevents attaching vitals to the wrong visit.
if ($appointment_id !== null) {
    $aCheck = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ? AND patient_id = ?");
    $aCheck->bind_param("ii", $appointment_id, $patient_id);
    $aCheck->execute();
    if ($aCheck->get_result()->num_rows !== 1) {
        respond(["success" => false, "message" => "Selected appointment could not be verified.", "csrf_token" => current_csrf_token()]);
    }
    $aCheck->close();
}

// Server-side range validation — reject obviously invalid numbers rather
// than silently storing garbage. All fields are optional (a field left
// blank is fine; a filled field must be plausible).
$errors = [];

function validRange($value, $min, $max, $label, &$errors) {
    if ($value === '' || $value === null) return null;
    if (!is_numeric($value) || $value < $min || $value > $max) {
        $errors[] = "$label must be between $min and $max.";
        return null;
    }
    return $value;
}

$temp   = validRange($temp, 30, 45, "Temperature (°C)", $errors);
$pulse  = validRange($pulse, 20, 250, "Pulse rate", $errors);
$resp   = validRange($resp, 5, 80, "Respiratory rate", $errors);
$o2     = validRange($o2, 40, 100, "Oxygen saturation", $errors);
$height = validRange($height, 20, 250, "Height (cm)", $errors);
$weight = validRange($weight, 0.5, 400, "Weight (kg)", $errors);

if ($bp !== '' && !preg_match('/^\d{2,3}\/\d{2,3}$/', $bp)) {
    $errors[] = "Blood pressure must be in the format 120/80.";
}

if (!empty($errors)) {
    respond(["success" => false, "message" => implode(' ', $errors), "csrf_token" => current_csrf_token()]);
}

// Require at least one measurement — an empty vitals row is meaningless.
if ($bp === '' && $temp === null && $pulse === null && $resp === null && $o2 === null && $height === null && $weight === null) {
    respond(["success" => false, "message" => "Enter at least one measurement.", "csrf_token" => current_csrf_token()]);
}

$stmt = $conn->prepare(
    "INSERT INTO vitals
        (patient_id, appointment_id, recorded_by, blood_pressure, temperature_c, pulse_rate, respiratory_rate, oxygen_saturation, height_cm, weight_kg)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "iiisdiiidd",
    $patient_id, $appointment_id, $_SESSION['user_id'], $bp, $temp, $pulse, $resp, $o2, $height, $weight
);
$stmt->execute();
$newId = $conn->insert_id;
$stmt->close();

log_action('vitals_recorded', 'Vitals', (string)$newId, "Vitals recorded for patient #$patient_id");

respond(["success" => true, "vital_id" => $newId, "csrf_token" => current_csrf_token()]);