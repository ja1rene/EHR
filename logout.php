<?php
require_once "config/db.php";
require_once "includes/security.php";

secure_session_start();

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
header("Location: login.php");
exit();