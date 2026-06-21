<?php
include 'config.php';
header('Content-Type: application/json');

$query = "SELECT l.id, l.employee_id, f.nickname, l.notification_type, l.recipient, l.subject, l.message, l.status, l.error_message, l.created_at 
          FROM notification_log l
          LEFT JOIN fingerprints f ON l.employee_id = f.employee_id
          ORDER BY l.created_at DESC 
          LIMIT 100";

$result = mysqli_query($connection, $query);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Failed to fetch logs"]);
    exit;
}

$logs = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Truncate message
    if (strlen($row['message']) > 100) {
        $row['message'] = substr($row['message'], 0, 97) . '...';
    }
    $logs[] = $row;
}

echo json_encode(["success" => true, "count" => count($logs), "data" => $logs]);
mysqli_close($connection);
?>
