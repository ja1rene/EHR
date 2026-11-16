<?php

define('BACKUP_MYSQLDUMP_PATH', 'C:\\xampp\\mysql\\bin\\mysqldump.exe');
define('BACKUP_MYSQL_PATH',     'C:\\xampp\\mysql\\bin\\mysql.exe');

$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? (__DIR__ . '/../../..'), '/\\');
define('BACKUP_DIR', $docRoot . '/../ehr_backups/');