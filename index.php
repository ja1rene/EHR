<?php
require_once "includes/security.php";
secure_session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$dest = match ($_SESSION["role"] ?? null) {
    'admin'  => 'admin/dashboard.php',
    'doctor' => 'doctor/dashboard.php',
    'staff'  => 'staff/dashboard.php',
    default  => 'login.php',
};

header("Location: $dest");
exit();