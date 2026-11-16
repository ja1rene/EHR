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

$username  = clean_input($_POST['username'] ?? '');
$full_name = clean_input($_POST['full_name'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

$errors = [];

if ($username === '' || !preg_match('/^[a-zA-Z0-9._]{3,50}$/', $username)) {
    $errors[] = "Username must be 3-50 characters (letters, numbers, dots, underscores only).";
}
if ($full_name === '') {
    $errors[] = "Full name is required.";
}
if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}
if (!in_array($role, ['admin', 'doctor', 'staff'], true)) {
    $errors[] = "A valid role must be selected.";
}

if (!empty($errors)) {
    respond(["success" => false, "message" => implode(' ', $errors), "csrf_token" => current_csrf_token()]);
}

// Uniqueness check.
$check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    respond(["success" => false, "message" => "That username is already taken.", "csrf_token" => current_csrf_token()]);
}
$check->close();

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (username, password_hash, full_name, role, account_status) VALUES (?, ?, ?, ?, 'active')"
);
$stmt->bind_param("ssss", $username, $passwordHash, $full_name, $role);
$stmt->execute();
$newId = $conn->insert_id;
$stmt->close();

log_action('user_created', 'User Management', (string)$newId, "Created $role account: $username");

respond(["success" => true, "user_id" => $newId, "csrf_token" => current_csrf_token()]);