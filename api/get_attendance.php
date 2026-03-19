<?php
// get_attendance.php — Get all attendance records
include 'config.php';
header('Content-Type: application/json');

$query = "SELECT employees.first_name, employees.last_name, 
                 attendance_log.log_date, attendance_log.log_time 
          FROM attendance_log 
          JOIN employees ON attendance_log.employee_id = employees.employee_id 
          ORDER BY attendance_log.log_date DESC, attendance_log.log_time DESC";

$result = mysqli_query($connection, $query);

$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $records[] = [
        "name" => $row['first_name'] . " " . $row['last_name'],
        "date" => $row['log_date'],
        "time" => $row['log_time']
    ];
}

echo json_encode($records);
mysqli_close($connection);
?>
