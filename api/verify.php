<?php
// verify.php — Compare fingerprint against all 10 columns and log attendance
include 'config.php';
require_once 'helpers/holiday.php';
header('Content-Type: application/json');

// Step 1: Export all 10 fingerprints per employee to a temp file
$query = "SELECT employee_id, nickname, right_thumb, right_index_f, right_middle, right_ring, right_pinky, left_thumb, left_index_f, left_middle, left_ring, left_pinky FROM fingerprints";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(["success" => false, "message" => "No employees registered yet"]);
    exit;
}

// Write temp file: employee_id|fmd1|fmd2|...|fmd10
$temp_file = sys_get_temp_dir() . '/ecozone_fmds.tmp';
$file_handle = fopen($temp_file, 'w');

$nicknames = []; // Cache nicknames for later

while ($row = mysqli_fetch_assoc($result)) {
    $eid = $row['employee_id'];
    $nicknames[$eid] = $row['nickname'];

    $line = $eid
        . '|' . $row['right_thumb']
        . '|' . $row['right_index_f']
        . '|' . $row['right_middle']
        . '|' . $row['right_ring']
        . '|' . $row['right_pinky']
        . '|' . $row['left_thumb']
        . '|' . $row['left_index_f']
        . '|' . $row['left_middle']
        . '|' . $row['left_ring']
        . '|' . $row['left_pinky']
        . "\n";
    
    fwrite($file_handle, $line);
}
fclose($file_handle);

// Step 2: Call the C++ scanner in verify mode
$scanner_path = realpath(__DIR__ . '/../bin/scanner.exe');

if (!$scanner_path || !file_exists($scanner_path)) {
    unlink($temp_file);
    echo json_encode(["success" => false, "message" => "Scanner executable not found"]);
    exit;
}

$scanner_output = trim(shell_exec('"' . $scanner_path . '" verify "' . $temp_file . '"'));

// Clean up temp file
if (file_exists($temp_file)) {
    unlink($temp_file);
}

// Step 3: Handle the scanner output
if (strpos($scanner_output, 'MATCH:') === 0) {
    $employee_id = (int)str_replace('MATCH:', '', $scanner_output);
    $nickname = $nicknames[$employee_id] ?? 'Unknown';

    $now = date("Y-m-d H:i:s");
    $today = date("Y-m-d");

    // --- Duplicate scan prevention: block re-scan within 60 seconds ---
    $dup_stmt = mysqli_prepare($connection, 
        "SELECT log_time FROM attendance_log 
         WHERE employee_id = ? AND DATE(log_time) = ?
         ORDER BY log_time DESC LIMIT 1"
    );
    mysqli_stmt_bind_param($dup_stmt, "is", $employee_id, $today);
    mysqli_stmt_execute($dup_stmt);
    $dup_result = mysqli_stmt_get_result($dup_stmt);
    $last_log = mysqli_fetch_assoc($dup_result);
    mysqli_stmt_close($dup_stmt);

    if ($last_log) {
        $last_ts = strtotime($last_log['log_time']);
        $now_ts  = strtotime($now);
        $diff = $now_ts - $last_ts;

        if ($diff < 60) {
            $wait = 60 - $diff;
            echo json_encode([
                "success"  => false, 
                "message"  => "Already scanned. Please wait {$wait} seconds.",
                "nickname" => $nickname
            ]);
            exit;
        }
    }

    // --- Determine log_type: TIME_IN or TIME_OUT ---
    $count_stmt = mysqli_prepare($connection, 
        "SELECT COUNT(*) AS scan_count FROM attendance_log 
         WHERE employee_id = ? AND DATE(log_time) = ?"
    );
    mysqli_stmt_bind_param($count_stmt, "is", $employee_id, $today);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = mysqli_fetch_assoc($count_result);
    mysqli_stmt_close($count_stmt);

    $scan_count = (int)$count_row['scan_count'];
    $log_type = ($scan_count % 2 === 0) ? 'TIME_IN' : 'TIME_OUT';

    // --- Check if today is a holiday ---
    $holiday      = check_holiday($connection, $today);
    $holiday_flag = $holiday ? $holiday['holiday_type'] : null;
    $holiday_name = $holiday ? $holiday['holiday_name'] : null;

    // --- Log attendance ---
    $log_stmt = mysqli_prepare($connection, 
        "INSERT INTO attendance_log (employee_id, nickname, log_time, log_type, holiday_flag, holiday_name) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($log_stmt, "isssss", $employee_id, $nickname, $now, $log_type, $holiday_flag, $holiday_name);
    mysqli_stmt_execute($log_stmt);
    mysqli_stmt_close($log_stmt);

    $type_label = ($log_type === 'TIME_IN') ? 'Timed In' : 'Timed Out';

    echo json_encode([
        "success"      => true,
        "message"      => "$type_label",
        "employee_id"  => $employee_id,
        "nickname"     => $nickname,
        "log_time"     => $now,
        "log_type"     => $log_type,
        "is_holiday"   => $holiday !== null,
        "holiday_name" => $holiday_name,
        "holiday_type" => $holiday_flag
    ]);
} 
else if ($scanner_output === 'NOMATCH') {
    echo json_encode(["success" => false, "message" => "Fingerprint not recognized"]);
} 
else {
    echo json_encode(["success" => false, "message" => "Scanner status: " . $scanner_output]);
}

mysqli_close($connection);
?>
