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

$id        = (int)($_POST['appointment_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';

// 'scheduled' is deliberately NOT a valid target. Appointment status only
// moves one way (scheduled -> completed / cancelled / no_show). Setting an
// appointment back to 'scheduled' would skip the double-booking check in
// appointments_add.php and could re-open a slot that has since been rebooked.
// To undo a mistake, book a new appointment instead.
$validStatuses = ['completed', 'cancelled', 'no_show'];
if (!in_array($newStatus, $validStatuses, true)) {
    respond(["success" => false, "message" => "Invalid status.", "csrf_token" => current_csrf_token()]);
}

// Fetch the appointment first — needed both to confirm it exists (IDOR
// check) and to enforce who is allowed to set which status.
$stmt = $conn->prepare("SELECT doctor_id, status FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(404);
    respond(["success" => false, "message" => "Appointment not found.", "csrf_token" => current_csrf_token()]);
}
$appointment = $result->fetch_assoc();
$stmt->close();

// Authorization: a doctor may only change the status of their OWN
// appointments, and only to completed/no_show — cancelling
// stays a staff action.
if ($_SESSION['role'] === 'doctor') {
    if ((int)$appointment['doctor_id'] !== (int)$_SESSION['user_id']) {
        http_response_code(403);
        respond(["success" => false, "message" => "You can only update your own appointments.", "csrf_token" => current_csrf_token()]);
    }
    if (!in_array($newStatus, ['completed', 'no_show'], true)) {
        http_response_code(403);
        respond(["success" => false, "message" => "Doctors can only mark appointments completed or no-show.", "csrf_token" => current_csrf_token()]);
    }
}

// Lifecycle rule: only a still-scheduled appointment can change. This is
// checked AFTER the ownership check so a doctor can't probe the status of
// someone else's appointment. It also protects completed appointments,
// which are tied to a saved consultation record.
if ($appointment['status'] !== 'scheduled') {
    $label = str_replace('_', '-', $appointment['status']);
    respond([
        "success" => false,
        "message" => "This appointment is already $label and can no longer be changed. To rebook, schedule a new appointment.",
        "csrf_token" => current_csrf_token()
    ]);
}

// The WHERE ... AND status = 'scheduled' makes the update itself safe if two
// people act on the same appointment at the same moment: only one succeeds.
$upd = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ? AND status = 'scheduled'");
$upd->bind_param("si", $newStatus, $id);
$upd->execute();
$changed = $upd->affected_rows;
$upd->close();

if ($changed !== 1) {
    respond(["success" => false, "message" => "This appointment was just updated by someone else. Please refresh the page.", "csrf_token" => current_csrf_token()]);
}

log_action('appointment_status_updated', 'Appointments', (string)$id, "Status changed to $newStatus");

respond(["success" => true, "csrf_token" => current_csrf_token()]);