<?php
// get_employees.php — Get list of registered employees
include 'config.php';
header('Content-Type: application/json');

$stmt = mysqli_prepare($connection, 
    "SELECT employee_id, nickname, date_registered, employee_role, email FROM fingerprints ORDER BY employee_id ASC"
);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$employees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $employees[] = [
        "employee_id"     => $row['employee_id'],
        "nickname"        => $row['nickname'],
        "date_registered" => $row['date_registered'],
        "employee_role"   => $row['employee_role'],
        "email"           => $row['email']
    ];
}

mysqli_stmt_close($stmt);
echo json_encode($employees);
mysqli_close($connection);
?>
