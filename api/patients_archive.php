<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff'); // only staff manage patient records

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

$id     = (int)($_POST['patient_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!in_array($action, ['archive', 'restore'], true)) {
    respond(["success" => false, "message" => "Invalid action.", "csrf_token" => current_csrf_token()]);
}

// The patient must exist — never act on a submitted ID blindly (IDOR check).
$stmt = $conn->prepare("SELECT patient_code, status FROM patients WHERE patient_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(404);
    respond(["success" => false, "message" => "Patient not found.", "csrf_token" => current_csrf_token()]);
}
$patient = $result->fetch_assoc();
$stmt->close();

if ($action === 'archive') {
    if ($patient['status'] === 'archived') {
        respond(["success" => false, "message" => "This patient is already archived.", "csrf_token" => current_csrf_token()]);
    }

    // Business rule: don't archive someone who is still booked. The staff
    // member needs to complete or cancel those appointments first.
    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE patient_id = ? AND status = 'scheduled'");
    $check->bind_param("i", $id);
    $check->execute();
    $scheduled = (int)$check->get_result()->fetch_assoc()['cnt'];
    $check->close();

    if ($scheduled > 0) {
        $word = $scheduled === 1 ? 'appointment' : 'appointments';
        respond([
            "success" => false,
            "message" => "This patient still has $scheduled scheduled $word. Complete or cancel them before archiving.",
            "csrf_token" => current_csrf_token()
        ]);
    }

    $newStatus = 'archived';
} else {
    if ($patient['status'] === 'active') {
        respond(["success" => false, "message" => "This patient is already active.", "csrf_token" => current_csrf_token()]);
    }
    $newStatus = 'active';
}

$upd = $conn->prepare("UPDATE patients SET status = ? WHERE patient_id = ?");
$upd->bind_param("si", $newStatus, $id);
$upd->execute();
$upd->close();

log_action(
    $action === 'archive' ? 'patient_archived' : 'patient_restored',
    'Patients',
    (string)$id,
    ($action === 'archive' ? 'Archived' : 'Restored') . " patient {$patient['patient_code']}"
);

respond(["success" => true, "csrf_token" => current_csrf_token()]);