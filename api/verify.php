<?php
// verify.php — Compare fingerprint against all 10 columns and log attendance
include 'config.php';
require_once 'helpers/holiday.php';
require_once 'helpers/notifications.php';
header('Content-Type: application/json');

// IP-based Throttling (Max 1 request per second)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$throttle_file = sys_get_temp_dir() . '/ecozone_throttle_' . md5($ip);
if (file_exists($throttle_file)) {
    if (time() - file_get_contents($throttle_file) < 1) {
        http_response_code(429);
        echo json_encode(["success" => false, "message" => "Too many requests. Please slow down."]);
        exit;
    }
}
file_put_contents($throttle_file, time());

require_once 'helpers/fingerprint_cache.php';

// Step 1: Get cached fingerprints
$cache_file = get_fingerprint_cache_file($connection);
$nicknames = get_nicknames_cache($connection);

if (empty($nicknames)) {
    echo json_encode(["success" => false, "message" => "No employees registered yet"]);
    exit;
}

// Step 2: Call the C++ scanner in verify mode
$scanner_path = realpath(__DIR__ . '/../bin/scanner.exe');

if (!$scanner_path || !file_exists($scanner_path)) {
    echo json_encode(["success" => false, "message" => "Scanner executable not found"]);
    exit;
}

$scanner_output = trim(shell_exec('"' . $scanner_path . '" verify "' . $cache_file . '"'));

// Step 3: Handle the scanner output
if (strpos($scanner_output, 'MATCH:') === 0) {
    $employee_id = (int)str_replace('MATCH:', '', $scanner_output);
    $nickname = $nicknames[$employee_id] ?? 'Unknown';

    $now = date("Y-m-d H:i:s");
    $today = date("Y-m-d");

    // --- Duplicate scan prevention: block re-scan within 60 seconds ---
    $start_of_day = $today . ' 00:00:00';
    $end_of_day = $today . ' 23:59:59';
    $dup_stmt = mysqli_prepare($connection, 
        "SELECT log_time FROM attendance_log 
         WHERE employee_id = ? AND log_time >= ? AND log_time <= ?
         ORDER BY log_time DESC LIMIT 1"
    );
    mysqli_stmt_bind_param($dup_stmt, "iss", $employee_id, $start_of_day, $end_of_day);
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
         WHERE employee_id = ? AND log_time >= ? AND log_time <= ?"
    );
    mysqli_stmt_bind_param($count_stmt, "iss", $employee_id, $start_of_day, $end_of_day);
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

    // --- Late Arrival Notification (Email + SMS) ---
    checkAndNotifyLate($connection, $employee_id, $nickname, $now, $log_type);

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
    // If the scanner executable outputs anything else (or nothing), it usually means the hardware is disconnected.
    $error_detail = empty($scanner_output) ? "Hardware unavailable" : $scanner_output;
    echo json_encode(["success" => false, "message" => "No device attached. Please connect the biometric scanner."]);
}

mysqli_close($connection);
?>
