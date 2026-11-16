<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/clinic_config.php";
require_once __DIR__ . "/../includes/mailer.php";
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

$patient_id = (int)($_POST['patient_id'] ?? 0);
$doctor_id  = (int)($_POST['doctor_id'] ?? 0);
$date       = $_POST['appointment_date'] ?? '';
$time       = $_POST['appointment_time'] ?? '';
$reason     = clean_input($_POST['reason'] ?? '');

// Visit length in minutes. Optional — defaults to the standard 30-minute
// slot, and only a fixed set of lengths is accepted.
$allowedDurations = [15, 30, 45, 60, 90, 120];
$duration = (int)($_POST['duration_minutes'] ?? 30);
if (!in_array($duration, $allowedDurations, true)) {
    $duration = 30;
}

$errors = [];

// Confirm the patient actually exists and is active — never trust a
// submitted ID at face value (Security 21.8). The name and code are kept
// for the doctor's notification email.
$patient = null;
$pCheck = $conn->prepare("SELECT patient_code, first_name, last_name FROM patients WHERE patient_id = ? AND status = 'active'");
$pCheck->bind_param("i", $patient_id);
$pCheck->execute();
$pResult = $pCheck->get_result();
if ($pResult->num_rows !== 1) {
    $errors[] = "Selected patient could not be found.";
} else {
    $patient = $pResult->fetch_assoc();
}
$pCheck->close();

// Confirm the doctor exists and is actually a doctor. The name is kept
// so a conflict message can say whose schedule is full, and the email
// is kept so that specific doctor can be notified.
$doctorName  = '';
$doctorEmail = '';
$dCheck = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ? AND role = 'doctor' AND account_status = 'active'");
$dCheck->bind_param("i", $doctor_id);
$dCheck->execute();
$dResult = $dCheck->get_result();
if ($dResult->num_rows !== 1) {
    $errors[] = "Selected doctor could not be found.";
} else {
    $doc = $dResult->fetch_assoc();
    $doctorName  = $doc['full_name'];
    $doctorEmail = $doc['email'] ?? '';
}
$dCheck->close();

$startTs = false;
$dateTimestamp = strtotime($date);
if ($date === '' || $dateTimestamp === false) {
    $errors[] = "A valid appointment date is required.";
}
if ($time === '' || !preg_match('/^\d{2}:\d{2}$/', $time)) {
    $errors[] = "A valid appointment time is required.";
}
if ($date !== '' && $dateTimestamp !== false && preg_match('/^\d{2}:\d{2}$/', $time)) {
    $startTs = strtotime("$date $time");
    if ($startTs === false) {
        $errors[] = "A valid appointment time is required.";
    }
}

// Work out when this visit ends, and make sure the WHOLE visit sits inside
// clinic hours (config/clinic_config.php). Checking only the start time
// isn't enough: a 9:00 PM start would still run past a 9:00 PM closing.
// Because closing time is before midnight, this also rules out visits
// that would cross into the next day.
if ($startTs !== false) {
    $endTs = $startTs + ($duration * 60);

    $toMinutes = function (string $hhmm): int {
        [$h, $m] = array_map('intval', explode(':', $hhmm));
        return $h * 60 + $m;
    };
    $openMin  = $toMinutes(CLINIC_OPEN_TIME);
    $closeMin = $toMinutes(CLINIC_CLOSE_TIME);
    $startMin = ((int)date('G', $startTs)) * 60 + (int)date('i', $startTs);
    $endMin   = $startMin + $duration;

    if ($startMin < $openMin || $endMin > $closeMin) {
        $errors[] = "Appointments must be within clinic hours (" .
            date('g:i A', strtotime(CLINIC_OPEN_TIME)) . " to " .
            date('g:i A', strtotime(CLINIC_CLOSE_TIME)) .
            "), and the visit must finish by closing time.";
    }
}

if (!empty($errors)) {
    respond(["success" => false, "message" => implode(' ', $errors), "csrf_token" => current_csrf_token()]);
}

$newStart = date('H:i:s', $startTs);
$newEnd   = date('H:i:s', $endTs);

// The conflict check and the insert run inside ONE transaction, with the
// doctor's rows for that day locked (FOR UPDATE). Without the lock, two
// staff members booking the same slot in the same second could both pass
// the check and both insert.
$conn->begin_transaction();

// Two appointments overlap when each one starts before the other ends.
// Back-to-back (one ends exactly when the next starts) is allowed.
// Cancelled and no-show appointments free their slot; scheduled and
// completed ones keep it.
$conflictStmt = $conn->prepare(
    "SELECT appointment_time, duration_minutes
     FROM appointments
     WHERE doctor_id = ?
       AND appointment_date = ?
       AND status IN ('scheduled', 'completed')
       AND appointment_time < ?
       AND ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)) > ?
     ORDER BY appointment_time ASC
     LIMIT 1
     FOR UPDATE"
);

if ($conflictStmt === false) {
    $conn->rollback();
    respond(["success" => false, "message" => "The appointments table is missing the duration_minutes column. Run the ALTER TABLE statement in phpMyAdmin, then try again.", "csrf_token" => current_csrf_token()]);
}

$conflictStmt->bind_param("isss", $doctor_id, $date, $newEnd, $newStart);
$conflictStmt->execute();
$conflict = $conflictStmt->get_result()->fetch_assoc();
$conflictStmt->close();

if ($conflict) {
    $conn->rollback();

    $existingStart = strtotime($conflict['appointment_time']);
    $existingEnd   = $existingStart + ((int)$conflict['duration_minutes'] * 60);
    $niceDate      = date('M j, Y', strtotime($date));

    respond([
        "success" => false,
        "message" => "$doctorName already has an appointment from " . date('g:i A', $existingStart) .
                     " to " . date('g:i A', $existingEnd) . " on $niceDate. Please choose a different time.",
        "csrf_token" => current_csrf_token()
    ]);
}

$stmt = $conn->prepare(
    "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, duration_minutes, reason, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("iissisi", $patient_id, $doctor_id, $date, $time, $duration, $reason, $_SESSION['user_id']);
$stmt->execute();
$newId = $conn->insert_id;
$stmt->close();

$conn->commit();

log_action('appointment_scheduled', 'Appointments', (string)$newId, "Scheduled for patient #$patient_id with doctor #$doctor_id ($duration min)");

// Notify the chosen doctor by email. This runs AFTER the commit so a mail
// failure can never undo a valid booking. The visit reason is deliberately
// left out of the email (medical info over an insecure channel).
$notified = false;
if ($doctorEmail !== '' && $patient) {
    $notified = send_appointment_notification([
        'doctor_email' => $doctorEmail,
        'doctor_name'  => $doctorName,
        'patient_name' => $patient['first_name'] . ' ' . $patient['last_name'],
        'patient_code' => $patient['patient_code'],
        'date_label'   => date('M j, Y', strtotime($date)),
        'time_label'   => date('g:i A', $startTs),
        'duration'     => $duration,
        'scheduled_by' => $_SESSION['full_name'] ?? 'Clinic staff',
    ]);
    log_action($notified ? 'doctor_notified' : 'doctor_notify_failed', 'Appointments', (string)$newId, "Email notification to doctor #$doctor_id");
}

respond(["success" => true, "appointment_id" => $newId, "notified" => $notified, "csrf_token" => current_csrf_token()]);