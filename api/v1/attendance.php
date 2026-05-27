<?php
// Fetch attendance records, with optional filtering
require_once 'auth.php'; // Authenticate via API key

$where_clauses = ["1=1"];
$params = [];
$types = "";

if (isset($_GET['employee_id']) && is_numeric($_GET['employee_id'])) {
    $where_clauses[] = "employee_id = ?";
    $params[] = (int)$_GET['employee_id'];
    $types .= "i";
}

if (isset($_GET['date'])) {
    $where_clauses[] = "DATE(log_time) = ?";
    $params[] = $_GET['date'];
    $types .= "s";
}

$where_sql = implode(" AND ", $where_clauses);
$query = "SELECT log_id, employee_id, nickname, log_time, log_type, holiday_flag, holiday_name, duty_status, duty_reason, admin_override FROM attendance_log WHERE $where_sql ORDER BY log_time DESC";

$stmt = mysqli_prepare($connection, $query);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$logs = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = [
            "log_id"         => (int)$row['log_id'],
            "employee_id"    => (int)$row['employee_id'],
            "nickname"       => $row['nickname'],
            "log_time"       => $row['log_time'],
            "log_type"       => $row['log_type'],
            "holiday_flag"   => $row['holiday_flag'],
            "holiday_name"   => $row['holiday_name'],
            "duty_status"    => $row['duty_status'],
            "duty_reason"    => $row['duty_reason'],
            "admin_override" => (bool)$row['admin_override']
        ];
    }
}

mysqli_stmt_close($stmt);

echo json_encode([
    "success" => true,
    "app" => AUTHORIZED_APP,
    "count" => count($logs),
    "data" => $logs
]);

mysqli_close($connection);
?>
