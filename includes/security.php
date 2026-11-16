<?php
function secure_session_start() {
    session_start();
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

/**
 * Convenience for JSON API endpoints: call this once at the top of
 * every JSON response (success or failure) so the front-end JS can
 * refresh its hidden csrf_token field. Without this, a failed
 * validation rotates the token server-side but the page still holds
 * the old one, so every retry after a mistake would fail too.
 */
function current_csrf_token() {
    generate_csrf_token();
    return $_SESSION['csrf_token'];
}

/**
 * Password policy, enforced everywhere a password is set (self-service
 * change, admin create, admin reset): at least 8 characters, containing
 * a letter, a number, and a special character. Returns an array of
 * human-readable problems — empty array means the password passes.
 * The client-side strength meter (assets/js/password-strength.js)
 * mirrors these same rules for live feedback, but this function is
 * the authoritative check — never trust the client-side meter alone.
 */
function password_policy_errors(string $password): array {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "at least 8 characters";
    }
    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = "at least one letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "at least one number";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "at least one special character";
    }
    return $errors;
}
?>