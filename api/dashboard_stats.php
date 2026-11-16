<?php
require_once __DIR__ . "/../includes/auth.php";
require_role(['staff', 'doctor', 'admin']);

header('Content-Type: application/json');

if ($_SESSION['role'] === 'admin') {

    $totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
    $totalDoctors = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'doctor' AND account_status = 'active'")->fetch_assoc()['cnt'];
    $totalStaff = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'staff' AND account_status = 'active'")->fetch_assoc()['cnt'];
    $inactiveAccounts = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE account_status = 'inactive'")->fetch_assoc()['cnt'];
    $todayEvents = $conn->query("SELECT COUNT(*) AS cnt FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['cnt'];

    $recentLogs = $conn->query(
        "SELECT al.action, al.module, al.description, al.created_at, u.full_name AS user_name
         FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id
         ORDER BY al.created_at DESC LIMIT 8"
    )->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "success" => true,
        "role" => "admin",
        "stats" => [
            "total_users"       => (int)$totalUsers,
            "total_doctors"     => (int)$totalDoctors,
            "total_staff"       => (int)$totalStaff,
            "inactive_accounts" => (int)$inactiveAccounts,
            "today_events"      => (int)$todayEvents,
        ],
        "recent_activity" => $recentLogs
    ]);
    exit();
}

if ($_SESSION['role'] === 'staff') {

    $totalPatients = $conn->query("SELECT COUNT(*) AS cnt FROM patients WHERE status = 'active'")->fetch_assoc()['cnt'];

    $todayTotal = $conn->query("SELECT COUNT(*) AS cnt FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['cnt'];

    $todayCompleted = $conn->query("SELECT COUNT(*) AS cnt FROM appointments WHERE appointment_date = CURDATE() AND status = 'completed'")->fetch_assoc()['cnt'];

    $todayScheduled = $conn->query("SELECT COUNT(*) AS cnt FROM appointments WHERE appointment_date = CURDATE() AND status = 'scheduled'")->fetch_assoc()['cnt'];

    $newThisWeek = $conn->query("SELECT COUNT(*) AS cnt FROM patients WHERE status = 'active' AND created_at >= (CURDATE() - INTERVAL 7 DAY)")->fetch_assoc()['cnt'];

    echo json_encode([
        "success" => true,
        "role" => "staff",
        "stats" => [
            "total_patients"   => (int)$totalPatients,
            "today_total"      => (int)$todayTotal,
            "today_completed"  => (int)$todayCompleted,
            "today_scheduled"  => (int)$todayScheduled,
            "new_this_week"    => (int)$newThisWeek,
        ]
    ]);

} else { // doctor

    $doctorId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE()");
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $todayTotal = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE() AND status = 'completed'");
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $todayCompleted = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE() AND status = 'scheduled'");
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $todayScheduled = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(DISTINCT patient_id) AS cnt FROM consultations WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $totalPatientsSeen = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    // Today's schedule, ordered by time — used for the mini-schedule list.
    $stmt = $conn->prepare(
        "SELECT a.appointment_id, a.appointment_time, a.status, a.reason,
                p.patient_id, p.first_name, p.last_name, p.patient_code
         FROM appointments a
         JOIN patients p ON a.patient_id = p.patient_id
         WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
         ORDER BY a.appointment_time ASC"
    );
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $todaySchedule = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode([
        "success" => true,
        "role" => "doctor",
        "stats" => [
            "today_total"        => (int)$todayTotal,
            "today_completed"    => (int)$todayCompleted,
            "today_scheduled"    => (int)$todayScheduled,
            "total_patients_seen" => (int)$totalPatientsSeen,
        ],
        "today_schedule" => $todaySchedule
    ]);
}