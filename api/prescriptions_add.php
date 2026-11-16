<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('doctor');

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
$notes           = clean_input($_POST['notes'] ?? '');

// Dynamic medicine rows — arrays submitted as medicine_name[], dosage[], etc.
$names         = $_POST['medicine_name'] ?? [];
$dosages       = $_POST['dosage'] ?? [];
$frequencies   = $_POST['frequency'] ?? [];
$durations     = $_POST['duration'] ?? [];
$instructions  = $_POST['instructions'] ?? [];

// IDOR check: patient must actually exist and be active.
$pCheck = $conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ? AND status = 'active'");
$pCheck->bind_param("i", $patient_id);
$pCheck->execute();
if ($pCheck->get_result()->num_rows !== 1) {
    respond(["success" => false, "message" => "Patient not found.", "csrf_token" => current_csrf_token()]);
}
$pCheck->close();

// If a consultation was specified, confirm it exists and belongs to this
// doctor and this patient — prevents attaching a prescription to someone
// else's consultation record.
if ($consultation_id !== null) {
    $cCheck = $conn->prepare("SELECT consultation_id FROM consultations WHERE consultation_id = ? AND doctor_id = ? AND patient_id = ?");
    $cCheck->bind_param("iii", $consultation_id, $_SESSION['user_id'], $patient_id);
    $cCheck->execute();
    if ($cCheck->get_result()->num_rows !== 1) {
        respond(["success" => false, "message" => "Selected consultation could not be verified.", "csrf_token" => current_csrf_token()]);
    }
    $cCheck->close();
}

// Build a clean list of medicine rows, dropping any row with no name
// (an empty row left over from a removed field in the UI).
$items = [];
for ($i = 0; $i < count($names); $i++) {
    $name = clean_input($names[$i] ?? '');
    if ($name === '') continue;
    $items[] = [
        'medicine_name' => $name,
        'dosage'        => clean_input($dosages[$i] ?? ''),
        'frequency'     => clean_input($frequencies[$i] ?? ''),
        'duration'      => clean_input($durations[$i] ?? ''),
        'instructions'  => clean_input($instructions[$i] ?? ''),
    ];
}

if (empty($items)) {
    respond(["success" => false, "message" => "At least one medicine is required.", "csrf_token" => current_csrf_token()]);
}

$conn->begin_transaction();

try {
    $insertRx = $conn->prepare(
        "INSERT INTO prescriptions (consultation_id, patient_id, doctor_id, notes) VALUES (?, ?, ?, ?)"
    );
    $insertRx->bind_param("iiis", $consultation_id, $patient_id, $_SESSION['user_id'], $notes);
    $insertRx->execute();
    $prescriptionId = $conn->insert_id;
    $insertRx->close();

    $insertItem = $conn->prepare(
        "INSERT INTO prescription_items (prescription_id, medicine_name, dosage, frequency, duration, instructions)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    foreach ($items as $item) {
        $insertItem->bind_param(
            "isssss",
            $prescriptionId, $item['medicine_name'], $item['dosage'], $item['frequency'], $item['duration'], $item['instructions']
        );
        $insertItem->execute();
    }
    $insertItem->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    respond(["success" => false, "message" => "Could not save the prescription. Please try again.", "csrf_token" => current_csrf_token()]);
}

log_action('prescription_created', 'Prescriptions', (string)$prescriptionId, "Prescribed " . count($items) . " item(s)");

respond(["success" => true, "prescription_id" => $prescriptionId, "csrf_token" => current_csrf_token()]);