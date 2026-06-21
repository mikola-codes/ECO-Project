<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $settings = [];
    $result = mysqli_query($connection, "SELECT setting_key, setting_value FROM notification_settings");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    echo json_encode(["success" => true, "data" => $settings]);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(["success" => false, "message" => "Invalid JSON payload"]);
        exit;
    }

    mysqli_begin_transaction($connection);
    try {
        $stmt = mysqli_prepare($connection, "INSERT INTO notification_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach ($data as $key => $value) {
            $strVal = (string)$value;
            mysqli_stmt_bind_param($stmt, "sss", $key, $strVal, $strVal);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
        mysqli_commit($connection);
        echo json_encode(["success" => true, "message" => "Settings updated successfully"]);
    } catch (Exception $e) {
        mysqli_rollback($connection);
        echo json_encode(["success" => false, "message" => "Failed to update settings: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
mysqli_close($connection);
?>
