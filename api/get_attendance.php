<?php
// get_attendance.php — Get attendance records (id, nickname, time)
include 'config.php';
header('Content-Type: application/json');

// Optional date filter: ?date=YYYY-MM-DD
$date_filter = isset($_GET['date']) ? trim($_GET['date']) : '';

if (!empty($date_filter)) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_filter)) {
        echo json_encode(["success" => false, "message" => "Invalid date format. Use YYYY-MM-DD"]);
        exit;
    }

    $start_date = $date_filter . ' 00:00:00';
    $end_date = $date_filter . ' 23:59:59';
    $stmt = mysqli_prepare($connection, 
        "SELECT log_id, employee_id, nickname, log_time, log_type, holiday_flag, holiday_name, duty_status, duty_reason, admin_override 
         FROM attendance_log 
         WHERE log_time >= ? AND log_time <= ?
         ORDER BY log_time DESC LIMIT 1000"
    );
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
} else {
    $stmt = mysqli_prepare($connection, 
        "SELECT log_id, employee_id, nickname, log_time, log_type, holiday_flag, holiday_name, duty_status, duty_reason, admin_override 
         FROM attendance_log 
         ORDER BY log_time DESC LIMIT 1000"
    );
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $records[] = [
        "log_id"         => $row['log_id'],
        "employee_id"    => $row['employee_id'],
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

mysqli_stmt_close($stmt);
echo json_encode(["success" => true, "data" => $records]);
mysqli_close($connection);
?>
