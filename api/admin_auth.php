<?php
/**
 * admin_auth.php — Password-only authentication for admin pages.
 */
include 'config.php';
header('Content-Type: application/json');

// Get hash from environment or use fallback
$admin_pass_hash = getenv('ECOZONE_ADMIN_HASH') ?: '$2y$10$Rtr3EtWumTPW1uHpSmsuBOViMnKQwORW3PU3opTC.SADCUh6xtORW';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    echo json_encode(["success" => false, "message" => "POST method required"]);
    exit;
}

// Ensure session variables exist for rate limiting
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

if ($_SESSION['login_attempts'] >= 5 && time() < $_SESSION['lockout_time']) {
    echo json_encode(["success" => false, "message" => "Too many failed attempts. Please try again later."]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$password = trim($input['password'] ?? '');

if (empty($password)) {
    echo json_encode(["success" => false, "message" => "Password is required"]);
    exit;
}

if (password_verify($password, $admin_pass_hash)) {
    $_SESSION['is_admin'] = true;
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
    echo json_encode([
        "success" => true,
        "message" => "Access granted"
    ]);
} else {
    $_SESSION['login_attempts']++;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['lockout_time'] = time() + 900; // 15 mins lockout
    }
    echo json_encode([
        "success" => false,
        "message" => "Invalid password"
    ]);
}
?>
