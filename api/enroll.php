<?php
include 'config.php';
header('Content-Type: application/json');

$first_name       = $_POST['first_name'] ?? '';
$last_name        = $_POST['last_name'] ?? '';
$position         = $_POST['position'] ?? '';
$fingerprint_data = $_POST['fingerprint_data'] ?? '';

// If no data provided, scan the fingerprint using the C++ scanner in enroll mode
if (empty($fingerprint_data)) {
    $scanner_path = realpath(__DIR__ . '/../bin/scanner.exe');
    $fingerprint_data = trim(shell_exec('"' . $scanner_path . '" enroll'));

    if (empty($fingerprint_data) || strpos($fingerprint_data, 'ERROR') === 0) {
        echo json_encode(["success" => false, "message" => "Scanner error: " . $fingerprint_data]);
        exit;
    }
}

if (empty($first_name) || empty($last_name)) {
    echo json_encode(["success" => false, "message" => "First name and last name are required"]);
    exit;
}

// Save employee
$first_name = mysqli_real_escape_string($connection, $first_name);
$last_name  = mysqli_real_escape_string($connection, $last_name);
$position   = mysqli_real_escape_string($connection, $position);
$fingerprint_data = mysqli_real_escape_string($connection, $fingerprint_data);

$query = "INSERT INTO employees (first_name, last_name, position) VALUES ('$first_name', '$last_name', '$position')";
$result = mysqli_query($connection, $query);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Failed to save employee"]);
    exit;
}

$employee_id = mysqli_insert_id($connection);

// Save fingerprint data (raw FMD)
$query = "INSERT INTO fingerprints (employee_id, fingerprint_data) VALUES ($employee_id, '$fingerprint_data')";
$result = mysqli_query($connection, $query);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Failed to save fingerprint data"]);
    exit;
}

echo json_encode([
    "success"     => true,
    "message"     => "Employee enrolled successfully",
    "employee_id" => $employee_id,
    "name"        => $first_name . " " . $last_name
]);

mysqli_close($connection);
?>
