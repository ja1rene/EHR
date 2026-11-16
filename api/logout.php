<?php
require_once __DIR__ . "/../includes/auth.php"; // db.php + security.php

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(["success" => false, "message" => "Your session has expired."]);
    exit();
}

if (isset($_SESSION["user_id"])) {
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, module) VALUES (?, 'logout', 'Authentication')"
    );
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
}

$_SESSION = [];
session_destroy();

echo json_encode(["success" => true, "redirect" => "login.php"]);