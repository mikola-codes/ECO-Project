<?php
/**
 * holidays.php — Holiday CRUD API for ECOZONE Attendance System
 *
 * GET    holidays.php                  → list all holidays
 * GET    holidays.php?year=2026        → list holidays for a year
 * GET    holidays.php?check=YYYY-MM-DD → check if a date is a holiday
 * GET    holidays.php?check_today=1    → check if today is a holiday
 * POST   holidays.php                  → add a holiday
 * PUT    holidays.php                  → update a holiday
 * DELETE holidays.php?id=5            → delete a holiday
 */
include 'config.php';
require_once 'helpers/holiday.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// ─── GET ────────────────────────────────────────────────────────────────────
if ($method === 'GET') {

    // Check a specific date
    if (isset($_GET['check'])) {
        $date = trim($_GET['check']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(["success" => false, "message" => "Invalid date. Use YYYY-MM-DD"]);
            exit;
        }
        $holiday = check_holiday($connection, $date);
        echo json_encode([
            "success"      => true,
            "is_holiday"   => $holiday !== null,
            "holiday_name" => $holiday['holiday_name'] ?? null,
            "holiday_type" => $holiday['holiday_type'] ?? null,
            "label"        => $holiday ? holiday_label($holiday['holiday_type']) : null
        ]);
        exit;
    }

    // Check today
    if (isset($_GET['check_today'])) {
        $today = date('Y-m-d');
        $holiday = check_holiday($connection, $today);
        echo json_encode([
            "success"      => true,
            "date"         => $today,
            "is_holiday"   => $holiday !== null,
            "holiday_name" => $holiday['holiday_name'] ?? null,
            "holiday_type" => $holiday['holiday_type'] ?? null,
            "label"        => $holiday ? holiday_label($holiday['holiday_type']) : null
        ]);
        exit;
    }

    // List holidays (optional year filter)
    $year = isset($_GET['year']) ? (int)$_GET['year'] : null;

    if ($year) {
        $stmt = mysqli_prepare($connection,
            "SELECT * FROM holidays
             WHERE year_specific = ? OR is_recurring = TRUE
             ORDER BY DATE_FORMAT(holiday_date, '%m-%d')"
        );
        mysqli_stmt_bind_param($stmt, "i", $year);
    } else {
        $stmt = mysqli_prepare($connection,
            "SELECT * FROM holidays ORDER BY holiday_date ASC"
        );
    }

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

// ─── POST — Add a holiday ────────────────────────────────────────────────────
if ($method === 'POST') {
    $name        = trim($_POST['holiday_name'] ?? '');
    $date        = trim($_POST['holiday_date'] ?? '');
    $type        = trim($_POST['holiday_type'] ?? '');
    $is_recurring = isset($_POST['is_recurring']) ? (bool)$_POST['is_recurring'] : false;
    $year_specific = isset($_POST['year_specific']) && $_POST['year_specific'] !== ''
                      ? (int)$_POST['year_specific'] : null;

    if (empty($name) || empty($date) || empty($type)) {
        echo json_encode(["success" => false, "message" => "holiday_name, holiday_date, and holiday_type are required"]);
        exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(["success" => false, "message" => "Invalid date format. Use YYYY-MM-DD"]);
        exit;
    }
    $allowed_types = ['REGULAR', 'SPECIAL_NON_WORKING', 'SPECIAL_WORKING', 'COMPANY'];
    if (!in_array($type, $allowed_types)) {
        echo json_encode(["success" => false, "message" => "Invalid holiday_type"]);
        exit;
    }

    $stmt = mysqli_prepare($connection,
        "INSERT INTO holidays (holiday_name, holiday_date, holiday_type, is_recurring, year_specific)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssii", $name, $date, $type, $is_recurring, $year_specific);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Holiday added", "holiday_id" => (int)mysqli_insert_id($connection)]);
    } else {
        $err = mysqli_stmt_error($stmt);
        echo json_encode(["success" => false, "message" => "Failed to add holiday: $err"]);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ─── PUT — Update a holiday ──────────────────────────────────────────────────
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $put);
    $id           = (int)($put['holiday_id'] ?? 0);
    $name         = trim($put['holiday_name'] ?? '');
    $date         = trim($put['holiday_date'] ?? '');
    $type         = trim($put['holiday_type'] ?? '');
    $is_recurring  = isset($put['is_recurring']) ? (int)(bool)$put['is_recurring'] : 0;
    $year_specific = isset($put['year_specific']) && $put['year_specific'] !== ''
                      ? (int)$put['year_specific'] : null;

    if (!$id || empty($name) || empty($date) || empty($type)) {
        echo json_encode(["success" => false, "message" => "holiday_id, holiday_name, holiday_date, holiday_type are required"]);
        exit;
    }

    $stmt = mysqli_prepare($connection,
        "UPDATE holidays SET holiday_name=?, holiday_date=?, holiday_type=?, is_recurring=?, year_specific=?
         WHERE holiday_id=?"
    );
    mysqli_stmt_bind_param($stmt, "sssiii", $name, $date, $type, $is_recurring, $year_specific, $id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["success" => true, "message" => "Holiday updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "No holiday found with that ID or no changes made"]);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// ─── DELETE — Remove a holiday ───────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(["success" => false, "message" => "id parameter is required"]);
        exit;
    }

    $stmt = mysqli_prepare($connection, "DELETE FROM holidays WHERE holiday_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["success" => true, "message" => "Holiday deleted"]);
    } else {
        echo json_encode(["success" => false, "message" => "No holiday found with that ID"]);
    }
    mysqli_stmt_close($stmt);
    exit;
}

echo json_encode(["success" => false, "message" => "Method not allowed"]);
mysqli_close($connection);
?>
