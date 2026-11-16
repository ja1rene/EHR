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
$newStatus = $_POST['status'] ?? '';

if (!in_array($newStatus, ['active', 'inactive'], true)) {
    respond(["success" => false, "message" => "Invalid status.", "csrf_token" => current_csrf_token()]);
}

// Prevent an admin from deactivating their own account — a self-lockout
// with no other admin available would leave nobody able to manage users.
if ($targetId === (int)$_SESSION['user_id']) {
    respond(["success" => false, "message" => "You cannot deactivate your own account.", "csrf_token" => current_csrf_token()]);
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

$stmt = $conn->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
$stmt->bind_param("si", $newStatus, $targetId);
$stmt->execute();
$stmt->close();

log_action('user_status_changed', 'User Management', (string)$targetId, "Set {$targetUser['username']} to $newStatus");

respond(["success" => true, "csrf_token" => current_csrf_token()]);