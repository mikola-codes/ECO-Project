<?php
/**
 * manual_attendance.php — Admin CRUD for manual attendance overrides
 *
 * GET    manual_attendance.php                     → list all manual entries
 * GET    manual_attendance.php?employee_id=3       → filter by employee
 * GET    manual_attendance.php?date=2026-05-19     → filter by date
 * POST   manual_attendance.php                     → add manual attendance
 * PUT    manual_attendance.php                     → update entry
 * DELETE manual_attendance.php?id=5                → delete entry
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

// ─── GET ─────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $where = ["1=1"];
    $params = [];
    $types = "";

    if (isset($_GET['employee_id']) && is_numeric($_GET['employee_id'])) {
        $where[] = "employee_id = ?";
        $params[] = (int)$_GET['employee_id'];
        $types .= "i";
    }
    if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
        $where[] = "attendance_date = ?";
        $params[] = $_GET['date'];
        $types .= "s";
    }

    $sql = "SELECT * FROM manual_attendance WHERE " . implode(" AND ", $where) . " ORDER BY attendance_date DESC, created_at DESC";
    $stmt = mysqli_prepare($connection, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
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

// ─── POST — Add manual attendance ────────────────────────────────────
if ($method === 'POST') {
    $employee_id    = (int)($_POST['employee_id'] ?? 0);
    $attendance_date = trim($_POST['attendance_date'] ?? '');
    $duty_status    = trim($_POST['duty_status'] ?? '');
    $duty_reason    = trim($_POST['duty_reason'] ?? '');
    $time_in        = trim($_POST['time_in'] ?? '');
    $time_out       = trim($_POST['time_out'] ?? '');
    $admin_notes    = trim($_POST['admin_notes'] ?? '');

    if (!$employee_id || empty($attendance_date) || empty($duty_status)) {
        echo json_encode(["success" => false, "message" => "employee_id, attendance_date, and duty_status are required"]);
        exit;
    }

    $valid_statuses = ['ON_FIELD_DUTY', 'COMPANY_TASK', 'OFFICIAL_BUSINESS', 'LATE_EXCUSED', 'ADMIN_OVERRIDE'];
    if (!in_array($duty_status, $valid_statuses)) {
        echo json_encode(["success" => false, "message" => "Invalid duty_status. Use: " . implode(', ', $valid_statuses)]);
        exit;
    }

    // Get nickname
    $nick_stmt = mysqli_prepare($connection, "SELECT nickname FROM fingerprints WHERE employee_id = ?");
    mysqli_stmt_bind_param($nick_stmt, "i", $employee_id);
    mysqli_stmt_execute($nick_stmt);
    $nick_result = mysqli_stmt_get_result($nick_stmt);
    $nick_row = mysqli_fetch_assoc($nick_result);
    mysqli_stmt_close($nick_stmt);

    if (!$nick_row) {
        echo json_encode(["success" => false, "message" => "Employee not found"]);
        exit;
    }
    $nickname = $nick_row['nickname'];

    $time_in_val  = !empty($time_in)  ? $attendance_date . ' ' . $time_in  : null;
    $time_out_val = !empty($time_out) ? $attendance_date . ' ' . $time_out : null;

    $stmt = mysqli_prepare($connection,
        "INSERT INTO manual_attendance (employee_id, nickname, attendance_date, duty_status, duty_reason, time_in, time_out, admin_notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "isssssss",
        $employee_id, $nickname, $attendance_date, $duty_status, $duty_reason, $time_in_val, $time_out_val, $admin_notes
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "success"  => true,
            "message"  => "Manual attendance recorded for $nickname",
            "id"       => (int)mysqli_insert_id($connection),
            "nickname" => $nickname
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed: " . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ─── PUT — Update entry ──────────────────────────────────────────────
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $put);
    $id             = (int)($put['id'] ?? 0);
    $duty_status    = trim($put['duty_status'] ?? '');
    $duty_reason    = trim($put['duty_reason'] ?? '');
    $time_in        = trim($put['time_in'] ?? '');
    $time_out       = trim($put['time_out'] ?? '');
    $admin_notes    = trim($put['admin_notes'] ?? '');
    $attendance_date = trim($put['attendance_date'] ?? '');

    if (!$id || empty($duty_status)) {
        echo json_encode(["success" => false, "message" => "id and duty_status are required"]);
        exit;
    }

    $time_in_val  = (!empty($time_in) && !empty($attendance_date))  ? $attendance_date . ' ' . $time_in  : (!empty($time_in) ? $time_in : null);
    $time_out_val = (!empty($time_out) && !empty($attendance_date)) ? $attendance_date . ' ' . $time_out : (!empty($time_out) ? $time_out : null);

    $stmt = mysqli_prepare($connection,
        "UPDATE manual_attendance SET duty_status=?, duty_reason=?, time_in=?, time_out=?, admin_notes=? WHERE id=?"
    );
    mysqli_stmt_bind_param($stmt, "sssssi", $duty_status, $duty_reason, $time_in_val, $time_out_val, $admin_notes, $id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["success" => true, "message" => "Entry updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "No entry found or no changes"]);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ─── DELETE ──────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(["success" => false, "message" => "id parameter required"]);
        exit;
    }

    $stmt = mysqli_prepare($connection, "DELETE FROM manual_attendance WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["success" => true, "message" => "Entry deleted"]);
    } else {
        echo json_encode(["success" => false, "message" => "Not found"]);
    }
    mysqli_stmt_close($stmt);
    exit;
}

echo json_encode(["success" => false, "message" => "Method not allowed"]);
mysqli_close($connection);
?>
