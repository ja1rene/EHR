<?php
/**
 * api/login.php
 * ------------------------------------------------------------
 * Called via fetch() from login.php's JavaScript, NOT by a
 * normal form POST. Always responds with JSON:
 *   { "success": true,  "redirect": "admin/dashboard.php" }
 *   { "success": false, "message": "Invalid username or password." }
 *
 * This is what makes the login page's validation asynchronous:
 * the browser sends the request in the background (fetch), and
 * the page stays fully interactive while it waits for this
 * endpoint to respond — nothing here blocks the UI thread.
 */

require_once __DIR__ . "/../includes/auth.php"; // pulls in db.php + security.php

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

$username = clean_input($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    respond(["success" => false, "message" => "Username and password are required.", "csrf_token" => current_csrf_token()]);
}

$stmt = $conn->prepare(
    "SELECT user_id, username, full_name, password_hash, role, account_status, avatar_path
     FROM users WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Unknown username — same generic message as a wrong password below.
    $log = $conn->prepare(
        "INSERT INTO audit_logs (action, module, description, ip_address)
         VALUES ('login_failed', 'Authentication', ?, ?)"
    );
    $desc = "Unknown username: $username";
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $log->bind_param("ss", $desc, $ip);
    $log->execute();
    $log->close();

    respond(["success" => false, "message" => "Invalid username or password.", "csrf_token" => current_csrf_token()]);
}

$row = $result->fetch_assoc();

if (!password_verify($password, $row["password_hash"])) {
    $log = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, module, description, ip_address)
         VALUES (?, 'login_failed', 'Authentication', 'Incorrect password', ?)"
    );
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $log->bind_param("is", $row["user_id"], $ip);
    $log->execute();
    $log->close();

    respond(["success" => false, "message" => "Invalid username or password.", "csrf_token" => current_csrf_token()]);
}

if ($row["account_status"] !== "active") {
    $log = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, module, description, ip_address)
         VALUES (?, 'login_blocked', 'Authentication', 'Inactive account attempted login', ?)"
    );
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $log->bind_param("is", $row["user_id"], $ip);
    $log->execute();
    $log->close();

    respond(["success" => false, "message" => "Your account is inactive. Please contact the system administrator.", "csrf_token" => current_csrf_token()]);
}

// ── Success ──
session_regenerate_id(true);

$upd = $conn->prepare("UPDATE users SET last_login_at = NOW() WHERE user_id = ?");
$upd->bind_param("i", $row["user_id"]);
$upd->execute();
$upd->close();

$_SESSION["user_id"]       = $row["user_id"];
$_SESSION["username"]      = $row["username"];
$_SESSION["full_name"]     = $row["full_name"];
$_SESSION["role"]          = $row["role"];
$_SESSION["avatar_path"]   = $row["avatar_path"];
$_SESSION["last_activity"] = time();

$log = $conn->prepare(
    "INSERT INTO audit_logs (user_id, action, module, ip_address)
     VALUES (?, 'login_success', 'Authentication', ?)"
);
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$log->bind_param("is", $row["user_id"], $ip);
$log->execute();
$log->close();

$dest = match ($row["role"]) {
    'admin'  => 'admin/dashboard.php',
    'doctor' => 'doctor/dashboard.php',
    'staff'  => 'staff/dashboard.php',
    default  => 'login.php',
};

respond(["success" => true, "redirect" => $dest]);