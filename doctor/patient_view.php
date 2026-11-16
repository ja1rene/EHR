<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('doctor');

$page_title = "Patient Profile";
$role_label = "Doctor";
$editable   = false;
$canPrescribe = true;
$canAddLabs = false;
$nav_links = [
    ['label' => 'Dashboard', 'url' => 'dashboard.php'],
    ['label' => 'Patients',  'url' => 'patients.php', 'active' => true],
    ['label' => 'Appointments', 'url' => 'appointments.php'],
    ['label' => 'Reports',      'url' => 'reports.php'],

];

require __DIR__ . "/../includes/patient_profile.php";