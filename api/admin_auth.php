<?php
/**
 * admin_auth.php — Simple password-only authentication for admin pages.
 *
 * POST admin_auth.php  { password: "..." }
 *
 * Returns { success: true/false, message: "..." }
 *
 * The admin password is stored as a hashed constant below.
 * To change it, generate a new hash with:  php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
 */
include 'config.php';
header('Content-Type: application/json');

// ─── Admin Password ──────────────────────────────────────────────────────────
// Default password: "ecozone2026"
// Change this hash if you want a different password.
define('ADMIN_PASSWORD_HASH', password_hash('ecozone2026', PASSWORD_DEFAULT));

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    echo json_encode(["success" => false, "message" => "POST method required"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$password = trim($input['password'] ?? '');

if (empty($password)) {
    echo json_encode(["success" => false, "message" => "Password is required"]);
    exit;
}

if (password_verify($password, ADMIN_PASSWORD_HASH)) {
    echo json_encode([
        "success" => true,
        "message" => "Access granted"
    ]);
} else {
    // Small delay to prevent brute-force
    usleep(500000); // 0.5 seconds
    echo json_encode([
        "success" => false,
        "message" => "Invalid password"
    ]);
}
?>
