<?php
// Middleware script to authenticate external API requests
include __DIR__ . '/../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: X-API-Key, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');

// PHP sometimes alters header cases (X-API-Key vs X-Api-Key)
$headers = array_change_key_case(getallheaders(), CASE_UPPER);
$api_key = isset($headers['X-API-KEY']) ? $headers['X-API-KEY'] : null;

if (!$api_key) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "X-API-Key header is missing"]);
    exit;
}

$stmt = mysqli_prepare($connection, "SELECT app_name FROM api_keys WHERE api_key = ? AND is_active = 1");
mysqli_stmt_bind_param($stmt, "s", $api_key);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$app = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$app) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid or inactive API Key"]);
    exit;
}

// Define the authenticated application name so endpoints can use it
define('AUTHORIZED_APP', $app['app_name']);
?>
