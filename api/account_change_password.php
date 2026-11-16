<?php
require_once __DIR__ . "/../includes/auth.php";
require_login(); // any logged-in role

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

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    respond(["success" => false, "message" => "All fields are required.", "csrf_token" => current_csrf_token()]);
}

if (strlen($newPassword) < 8) {
    respond(["success" => false, "message" => "New password must be at least 8 characters.", "csrf_token" => current_csrf_token()]);
}

$policyErrors = password_policy_errors($newPassword);
if (!empty($policyErrors)) {
    respond(["success" => false, "message" => "Password must contain: " . implode(', ', $policyErrors) . ".", "csrf_token" => current_csrf_token()]);
}

if ($newPassword !== $confirmPassword) {
    respond(["success" => false, "message" => "New password and confirmation do not match.", "csrf_token" => current_csrf_token()]);
}

$stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
    respond(["success" => false, "message" => "Current password is incorrect.", "csrf_token" => current_csrf_token()]);
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$upd = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
$upd->bind_param("si", $newHash, $_SESSION['user_id']);
$upd->execute();
$upd->close();

log_action('password_changed', 'Authentication', (string)$_SESSION['user_id'], 'User changed their own password');

respond(["success" => true, "csrf_token" => current_csrf_token()]);