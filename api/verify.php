<?php
// verify.php — Compare fingerprint against database and log attendance
include 'config.php';
header('Content-Type: application/json');

// Step 1: Export all fingerprints from DB to a temp file for the C++ scanner to read
$query = "SELECT employee_id, fingerprint_data FROM fingerprints";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(["success" => false, "message" => "No employees enrolled yet"]);
    exit;
}

$temp_file = sys_get_temp_dir() . '/ecozone_fmds.tmp';
$file_handle = fopen($temp_file, 'w');

while ($row = mysqli_fetch_assoc($result)) {
    // Format: employee_id|fingerprint_data
    fwrite($file_handle, $row['employee_id'] . '|' . $row['fingerprint_data'] . "\n");
}
fclose($file_handle);

// Step 2: Call the C++ scanner in verify mode, passing the temp file
$scanner_path = realpath(__DIR__ . '/../bin/scanner.exe');
$scanner_output = trim(shell_exec('"' . $scanner_path . '" verify "' . $temp_file . '"'));

// Clean up temp file
unlink($temp_file);

// Step 3: Handle the scanner output
if (strpos($scanner_output, 'MATCH:') === 0) {
    // Found a match
    $employee_id = (int)str_replace('MATCH:', '', $scanner_output);
    
    // Get employee details
    $emp_query = "SELECT first_name, last_name FROM employees WHERE employee_id = $employee_id";
    $emp_result = mysqli_query($connection, $emp_query);
    $employee = mysqli_fetch_assoc($emp_result);
    $full_name = $employee['first_name'] . " " . $employee['last_name'];
    
    // Log attendance
    $today_date   = date("Y-m-d");
    $current_time = date("H:i:s");
    
    $log_query = "INSERT INTO attendance_log (employee_id, log_date, log_time) 
                  VALUES ($employee_id, '$today_date', '$current_time')";
    mysqli_query($connection, $log_query);
    
    echo json_encode([
        "success" => true,
        "message" => "Attendance logged",
        "name"    => $full_name,
        "date"    => $today_date,
        "time"    => $current_time
    ]);
} 
else if ($scanner_output === 'NOMATCH') {
    echo json_encode(["success" => false, "message" => "Fingerprint not recognized"]);
} 
else {
    // Some other error from the scanner
    echo json_encode(["success" => false, "message" => "Scanner status: " . $scanner_output]);
}

mysqli_close($connection);
?>
