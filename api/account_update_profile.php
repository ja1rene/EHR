<?php
require_once __DIR__ . "/../includes/auth.php";
require_login();

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

$full_name = clean_input($_POST['full_name'] ?? '');

if ($full_name === '') {
    respond(["success" => false, "message" => "Full name cannot be empty.", "csrf_token" => current_csrf_token()]);
}

$newAvatarPath = null; // stays null unless a new file was actually uploaded

// Avatar upload is optional — only process it if a file was actually sent.
if (!empty($_FILES['avatar']['name'])) {
    $file = $_FILES['avatar'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        respond(["success" => false, "message" => "There was a problem uploading the image.", "csrf_token" => current_csrf_token()]);
    }

    // Real content-type check via getimagesize(), NOT the client-supplied
    // MIME type or file extension — both are trivially spoofable.
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        respond(["success" => false, "message" => "The uploaded file is not a valid image.", "csrf_token" => current_csrf_token()]);
    }

    $allowedTypes = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!isset($allowedTypes[$imageInfo[2]])) {
        respond(["success" => false, "message" => "Only JPG, PNG, or WEBP images are allowed.", "csrf_token" => current_csrf_token()]);
    }

    $maxBytes = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxBytes) {
        respond(["success" => false, "message" => "Image must be smaller than 2MB.", "csrf_token" => current_csrf_token()]);
    }

    $extension = $allowedTypes[$imageInfo[2]];
    $filename  = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
    $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        respond(["success" => false, "message" => "Could not save the uploaded image.", "csrf_token" => current_csrf_token()]);
    }

    $newAvatarPath = 'assets/uploads/avatars/' . $filename;

    // Clean up the previous avatar file so uploads don't accumulate forever.
    $old = $conn->prepare("SELECT avatar_path FROM users WHERE user_id = ?");
    $old->bind_param("i", $_SESSION['user_id']);
    $old->execute();
    $oldPath = $old->get_result()->fetch_assoc()['avatar_path'] ?? null;
    $old->close();

    if ($oldPath) {
        $oldFile = __DIR__ . '/../' . $oldPath;
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
    }
}

if ($newAvatarPath !== null) {
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, avatar_path = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $full_name, $newAvatarPath, $_SESSION['user_id']);
} else {
    $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
    $stmt->bind_param("si", $full_name, $_SESSION['user_id']);
}
$stmt->execute();
$stmt->close();

$_SESSION['full_name'] = $full_name; // keep session in sync immediately
if ($newAvatarPath !== null) {
    $_SESSION['avatar_path'] = $newAvatarPath;
}

log_action('profile_updated', 'Authentication', (string)$_SESSION['user_id'], 'Updated own profile');

respond(["success" => true, "avatar_path" => $newAvatarPath, "csrf_token" => current_csrf_token()]);