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

$appointment_id = (int)($_POST['appointment_id'] ?? 0);
$chief_complaint = clean_input($_POST['chief_complaint'] ?? '');
$symptoms        = clean_input($_POST['symptoms'] ?? '');
$physical_exam   = clean_input($_POST['physical_exam'] ?? '');
$diagnosis       = clean_input($_POST['diagnosis'] ?? '');
$treatment_plan  = clean_input($_POST['treatment_plan'] ?? '');
$doctor_notes    = clean_input($_POST['doctor_notes'] ?? '');

// Fetch and validate the appointment: must exist, must belong to THIS
// doctor, and must still be 'scheduled' — this is both an IDOR check
// and a business-rule check (can't consult on someone else's, or an
// already-completed/cancelled, appointment).
$stmt = $conn->prepare(
    "SELECT patient_id, doctor_id, status FROM appointments WHERE appointment_id = ?"
);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(404);
    respond(["success" => false, "message" => "Appointment not found.", "csrf_token" => current_csrf_token()]);
}
$appointment = $result->fetch_assoc();
$stmt->close();

if ((int)$appointment['doctor_id'] !== (int)$_SESSION['user_id']) {
    http_response_code(403);
    respond(["success" => false, "message" => "You can only start a consultation for your own appointments.", "csrf_token" => current_csrf_token()]);
}

if ($appointment['status'] !== 'scheduled') {
    respond(["success" => false, "message" => "This appointment is no longer scheduled.", "csrf_token" => current_csrf_token()]);
}

if ($chief_complaint === '') {
    respond(["success" => false, "message" => "Chief complaint is required.", "csrf_token" => current_csrf_token()]);
}

// Both the consultation insert and the appointment status update should
// succeed together — use a transaction so a mid-way failure can't leave
// an appointment marked completed with no consultation record, or vice versa.
$conn->begin_transaction();

try {
    $insert = $conn->prepare(
        "INSERT INTO consultations
            (appointment_id, patient_id, doctor_id, chief_complaint, symptoms, physical_exam, diagnosis, treatment_plan, doctor_notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insert->bind_param(
        "iiissssss",
        $appointment_id, $appointment['patient_id'], $_SESSION['user_id'],
        $chief_complaint, $symptoms, $physical_exam, $diagnosis, $treatment_plan, $doctor_notes
    );
    $insert->execute();
    $newId = $conn->insert_id;
    $insert->close();

    $updateAppt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
    $updateAppt->bind_param("i", $appointment_id);
    $updateAppt->execute();
    $updateAppt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    respond(["success" => false, "message" => "Could not save the consultation. Please try again.", "csrf_token" => current_csrf_token()]);
}

log_action('consultation_created', 'Consultations', (string)$newId, "Consultation for appointment #$appointment_id");

respond(["success" => true, "consultation_id" => $newId, "csrf_token" => current_csrf_token()]);