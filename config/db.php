<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "ehr_system";

date_default_timezone_set('Asia/Manila');

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = '+08:00'");