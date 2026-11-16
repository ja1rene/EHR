<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/backup_config.php";
require_role('admin');

header('Content-Type: application/json');

$files = [];

if (is_dir(BACKUP_DIR)) {
    foreach (glob(BACKUP_DIR . '*.sql') as $filePath) {
        $files[] = [
            "name"     => basename($filePath),
            "size"     => round(filesize($filePath) / 1024, 2) . " KB",
            "modified" => date("Y-m-d H:i:s", filemtime($filePath)),
        ];
    }
    usort($files, fn($a, $b) => strcmp($b["modified"], $a["modified"]));
}

echo json_encode(["success" => true, "files" => $files]);