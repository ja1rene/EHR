<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

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

$targetId  = (int)($_POST['user_id'] ?? 0);

// Block self-edit entirely, server-side. An admin editing their own
// account here (especially resetting their own password blind) risks
// an accidental self-lockout with no other admin able to undo it.
// Self password changes belong in account.php, which requires
// confirming the CURRENT password first.
if ($targetId === (int)$_SESSION['user_id']) {
    respond(["success" => false, "message" => "You cannot edit your own account here. Use My Account to update your profile or password.", "csrf_token" => current_csrf_token()]);
}

$full_name = clean_input($_POST['full_name'] ?? '');
$username  = clean_input($_POST['username'] ?? '');
$newPassword = $_POST['new_password'] ?? ''; // optional — blank means "don't change"
$reason    = clean_input($_POST['reason'] ?? '');

$errors = [];

if ($full_name === '') $errors[] = "Full name is required.";
if ($username === '' || !preg_match('/^[a-zA-Z0-9._]{3,50}$/', $username)) {
    $errors[] = "Username must be 3-50 characters (letters, numbers, dots, underscores only).";
}
if ($newPassword !== '' && strlen($newPassword) < 8) {
    $errors[] = "New password must be at least 8 characters (or leave blank to keep the current password).";
} elseif ($newPassword !== '') {
    $policyErrors = password_policy_errors($newPassword);
    if (!empty($policyErrors)) {
        $errors[] = "New password must contain: " . implode(', ', $policyErrors) . ".";
    }
}
// A reason is mandatory for any admin edit to someone else's account —
// this is the accountability check that makes admin edit power auditable
// rather than silent.
if ($reason === '') {
    $errors[] = "A reason for this change is required.";
}

if (!empty($errors)) {
    respond(["success" => false, "message" => implode(' ', $errors), "csrf_token" => current_csrf_token()]);
}

$check = $conn->prepare("SELECT user_id, username FROM users WHERE user_id = ?");
$check->bind_param("i", $targetId);
$check->execute();
$result = $check->get_result();
if ($result->num_rows !== 1) {
    http_response_code(404);
    respond(["success" => false, "message" => "User not found.", "csrf_token" => current_csrf_token()]);
}
$targetUser = $result->fetch_assoc();
$check->close();

// Username uniqueness, excluding the record being edited.
$dupCheck = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
$dupCheck->bind_param("si", $username, $targetId);
$dupCheck->execute();
if ($dupCheck->get_result()->num_rows > 0) {
    respond(["success" => false, "message" => "That username is already taken.", "csrf_token" => current_csrf_token()]);
}
$dupCheck->close();

if ($newPassword !== '') {
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ?, password_hash = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $full_name, $username, $newHash, $targetId);
} else {
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $full_name, $username, $targetId);
}
$stmt->execute();
$stmt->close();

$passwordNote = $newPassword !== '' ? ' (password reset)' : '';
log_action('user_edited', 'User Management', (string)$targetId, "Edited {$targetUser['username']}$passwordNote — Reason: $reason");

respond(["success" => true, "csrf_token" => current_csrf_token()]);