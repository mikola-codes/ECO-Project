<?php
date_default_timezone_set('Asia/Manila');
$database_host     = "localhost";
$database_username = "root";
$database_password = "";  // Default XAMPP password is empty
$database_name     = "ecozone_attendance";

// Connect to MySQL
$connection = mysqli_connect(
    $database_host,
    $database_username,
    $database_password,
    $database_name
);

// Check if the connection worked
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
