<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/security.php";

secure_session_start();

/**
 * base_url()
 * ------------------------------------------------------------
 * Returns the correct absolute URL prefix for this app, computed
 * automatically from where the files actually sit on disk — NOT
 * a hardcoded folder name. This used to be a literal string here
 * (e.g. "/EHR/"), which broke every fetch() call in the system
 * (data loading, logout, everything) the moment the real folder
 * name didn't match that string exactly. Computing it avoids that
 * entire class of bug permanently: rename or move the folder, and
 * this still resolves correctly with zero edits needed.
 */
function base_url($path = "") {
    static $basePath = null;

    if ($basePath === null) {
        // includes/ is always exactly one level below the app root,
        // so go up one level from this file's real location.
        $appRoot = realpath(__DIR__ . '/..');
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');

        if ($appRoot && $docRoot && stripos($appRoot, $docRoot) === 0) {
            $basePath = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
        } else {
            // Fallback: derive it from the currently executing script's
            // own path instead, in case DOCUMENT_ROOT isn't reliable.
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            // Walk up from wherever the current script lives until we're
            // at the app root — handles pages nested in staff/, doctor/, etc.
            $basePath = preg_replace('#/(staff|doctor|admin|api)$#', '', $scriptDir);
        }

        $basePath = '/' . trim($basePath, '/');
    }

    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
}

// Every protected page starts with this.
function require_login() {
    global $conn;

    if (!isset($_SESSION["user_id"])) {
        header("Location: " . base_url("login.php"));
        exit();
    }

    // Session timeout — 30 minutes of inactivity. `last_activity` is set
    // at login and refreshed below on every request; if too much time has
    // passed since the last one, the session is destroyed and the user is
    // sent back to login with a clear message rather than silently
    // staying "logged in" forever.
    $timeoutSeconds = 1800;
    if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"]) > $timeoutSeconds) {
        session_destroy();
        header("Location: " . base_url("login.php?timeout=1"));
        exit();
    }
    $_SESSION["last_activity"] = time();

    $stmt = $conn->prepare("SELECT account_status FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        session_destroy();
        header("Location: " . base_url("login.php"));
        exit();
    }

    if ($user["account_status"] !== "active") {
        session_destroy();
        header("Location: " . base_url("login.php?inactive=1"));
        exit();
    }
}

/**
 * Call after require_login() on pages that only certain roles may open.
 * Example: require_role('admin');  or  require_role(['doctor','staff']);
 */
function require_role($allowedRoles) {
    require_login();

    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    if (!in_array($_SESSION["role"], $allowedRoles, true)) {
        header("Location: " . base_url("403.php"));
        exit();
    }
}

// Reusable audit writer — call this from any module (login, patients, etc).
function log_action($action, $module, $record_id = null, $description = null) {
    global $conn;

    $user_id   = $_SESSION["user_id"] ?? null;
    $ip        = $_SERVER['REMOTE_ADDR'] ?? null;

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, module, record_id, description, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isssss", $user_id, $action, $module, $record_id, $description, $ip);
    $stmt->execute();
    $stmt->close();
}

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return "Just now";
    if ($diff < 3600)   return floor($diff / 60) . "m ago";
    if ($diff < 86400)  return floor($diff / 3600) . "h ago";
    if ($diff < 604800) return floor($diff / 86400) . "d ago";
    return date('M j', strtotime($datetime));
}
?>