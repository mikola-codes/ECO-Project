<?php
define('IN_APP', true);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Manila');

$database_host     = "localhost";
$database_username = "root";
$database_password = "";  // Default XAMPP password is empty
$database_name     = "ecozone_attendance";

// Connect to MySQL (suppress warnings so they don't break JSON)
$connection = @mysqli_connect(
    $database_host,
    $database_username,
    $database_password,
    $database_name
);

// Check if the connection worked — return JSON error for API consumers
if (!$connection) {
    error_log("Database connection failed: " . mysqli_connect_error());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection error."
    ]);
    exit;
}

// Set charset for proper encoding
mysqli_set_charset($connection, "utf8mb4");
?>
