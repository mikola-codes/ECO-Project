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

    $stmt = mysqli_prepare($connection, 
        "SELECT log_id, employee_id, nickname, log_time, log_type, holiday_flag, holiday_name, duty_status, duty_reason, admin_override 
         FROM attendance_log 
         WHERE DATE(log_time) = ?
         ORDER BY log_time DESC"
    );
    mysqli_stmt_bind_param($stmt, "s", $date_filter);
} else {
    $stmt = mysqli_prepare($connection, 
        "SELECT log_id, employee_id, nickname, log_time, log_type, holiday_flag, holiday_name, duty_status, duty_reason, admin_override 
         FROM attendance_log 
         ORDER BY log_time DESC"
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
echo json_encode($records);
mysqli_close($connection);
?>
