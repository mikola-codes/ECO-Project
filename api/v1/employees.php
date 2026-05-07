<?php
// Fetch all employees (excluding sensitive fingerprint data)
require_once 'auth.php'; // This checks API key automatically

$query = "SELECT employee_id, nickname, date_registered FROM fingerprints ORDER BY employee_id ASC";
$result = mysqli_query($connection, $query);

$employees = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = [
            "employee_id" => (int)$row['employee_id'],
            "nickname" => $row['nickname'],
            "date_registered" => $row['date_registered']
        ];
    }
}

echo json_encode([
    "success" => true,
    "app" => AUTHORIZED_APP,
    "data" => $employees
]);

mysqli_close($connection);
?>
