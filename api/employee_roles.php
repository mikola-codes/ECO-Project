<?php
/**
 * employee_roles.php — Manage employee roles (REGULAR, FIELD, DRIVER, FLEXIBLE)
 *
 * GET  employee_roles.php              → list all employees with roles
 * PUT  employee_roles.php              → update an employee's role
 */
include 'config.php';
require_once 'auth_guard.php';
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ─── GET — List employees with roles ──────────────────────────────────
if ($method === 'GET') {
    $stmt = mysqli_prepare($connection,
        "SELECT employee_id, nickname, employee_role, date_registered FROM fingerprints ORDER BY employee_id ASC"
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode(["success" => true, "count" => count($rows), "data" => $rows]);
    exit;
}

// ─── PUT — Update employee role ───────────────────────────────────────
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $put);
    $employee_id = (int)($put['employee_id'] ?? 0);
    $role        = trim($put['employee_role'] ?? '');

    if (!$employee_id || empty($role)) {
        echo json_encode(["success" => false, "message" => "employee_id and employee_role required"]);
        exit;
    }

    $valid_roles = ['REGULAR', 'FIELD', 'DRIVER', 'FLEXIBLE'];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(["success" => false, "message" => "Invalid role. Use: " . implode(', ', $valid_roles)]);
        exit;
    }

    $stmt = mysqli_prepare($connection, "UPDATE fingerprints SET employee_role = ? WHERE employee_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $role, $employee_id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["success" => true, "message" => "Role updated to $role"]);
    } else {
        echo json_encode(["success" => false, "message" => "Employee not found or same role"]);
    }
    mysqli_stmt_close($stmt);
    exit;
}

echo json_encode(["success" => false, "message" => "Method not allowed"]);
mysqli_close($connection);
?>
