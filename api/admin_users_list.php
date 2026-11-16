<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

header('Content-Type: application/json');

$search = clean_input($_GET['search'] ?? '');
$role   = $_GET['role'] ?? '';

$sql = "SELECT user_id, username, full_name, role, account_status, last_login_at, created_at FROM users WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (username LIKE CONCAT('%', ?, '%') OR full_name LIKE CONCAT('%', ?, '%'))";
    $params[] = $search; $params[] = $search;
    $types .= "ss";
}

if (in_array($role, ['admin', 'doctor', 'staff'], true)) {
    $sql .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(["success" => true, "users" => $users]);