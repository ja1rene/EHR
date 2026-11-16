<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

header('Content-Type: application/json');

$module = clean_input($_GET['module'] ?? '');
$search = clean_input($_GET['search'] ?? '');
$date   = $_GET['date'] ?? '';

$sql = "SELECT al.audit_log_id, al.action, al.module, al.record_id, al.description,
               al.ip_address, al.created_at, u.full_name AS user_name, u.username
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.user_id
        WHERE 1=1";
$params = [];
$types = "";

if ($module !== '') {
    $sql .= " AND al.module = ?";
    $params[] = $module;
    $types .= "s";
}

if ($search !== '') {
    $sql .= " AND (u.full_name LIKE CONCAT('%', ?, '%') OR u.username LIKE CONCAT('%', ?, '%') OR al.action LIKE CONCAT('%', ?, '%'))";
    $params[] = $search; $params[] = $search; $params[] = $search;
    $types .= "sss";
}

if ($date !== '') {
    $sql .= " AND DATE(al.created_at) = ?";
    $params[] = $date;
    $types .= "s";
}

$sql .= " ORDER BY al.created_at DESC LIMIT 300";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Distinct module list, for the filter dropdown.
$modules = $conn->query("SELECT DISTINCT module FROM audit_logs ORDER BY module")->fetch_all(MYSQLI_ASSOC);

echo json_encode(["success" => true, "logs" => $logs, "modules" => array_column($modules, 'module')]);