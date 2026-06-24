<?php
include 'config.php';
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

$nickname = trim($_POST['nickname'] ?? '');

// --- Validation ---
if (empty($nickname)) {
    echo json_encode(["success" => false, "message" => "Nickname is required"]);
    exit;
}

if (strlen($nickname) > 100) {
    echo json_encode(["success" => false, "message" => "Nickname must be 100 characters or less"]);
    exit;
}

// --- Check for duplicate nickname ---
$check_stmt = mysqli_prepare($connection, "SELECT employee_id FROM fingerprints WHERE nickname = ?");
mysqli_stmt_bind_param($check_stmt, "s", $nickname);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(["success" => false, "message" => "Nickname '$nickname' is already registered"]);
    mysqli_stmt_close($check_stmt);
    exit;
}
mysqli_stmt_close($check_stmt);

// --- Determine which finger to scan ---
// The frontend sends finger_index (0-9) to scan one finger at a time.
// If finger_index is not set, this is a "scan all at once" request (10 sequential calls from frontend).
$finger_index = isset($_POST['finger_index']) ? (int)$_POST['finger_index'] : -1;

// Finger names in order (matches the database columns)
$finger_names = [
    'Right Thumb', 'Right Index', 'Right Middle', 'Right Ring', 'Right Pinky',
    'Left Thumb', 'Left Index', 'Left Middle', 'Left Ring', 'Left Pinky'
];

$finger_columns = [
    'right_thumb', 'right_index_f', 'right_middle', 'right_ring', 'right_pinky',
    'left_thumb', 'left_index_f', 'left_middle', 'left_ring', 'left_pinky'
];

// --- Single finger scan mode (called 10 times by frontend) ---
if ($finger_index >= 0 && $finger_index <= 9) {
    $fingerprint_data = trim($_POST['fingerprint_data'] ?? '');

    // Allow "SKIP" for missing fingers
    if ($fingerprint_data === 'SKIP') {
        echo json_encode([
            "success"      => true,
            "message"      => $finger_names[$finger_index] . " skipped",
            "finger_index" => $finger_index,
            "finger_name"  => $finger_names[$finger_index],
            "finger_data"  => "SKIP"
        ]);
        exit;
    }

    if (empty($fingerprint_data)) {
        // Call scanner to capture this finger
        $scanner_path = realpath(__DIR__ . '/../bin/scanner.exe');

        if (!$scanner_path || !file_exists($scanner_path)) {
            echo json_encode(["success" => false, "message" => "Scanner executable not found"]);
            exit;
        }

        $fingerprint_data = trim(shell_exec('"' . $scanner_path . '" enroll'));

        if (empty($fingerprint_data) || strpos($fingerprint_data, 'ERROR') === 0) {
            $error_detail = empty($fingerprint_data) ? "No device detected." : str_replace('ERROR:', '', $fingerprint_data);
            echo json_encode([
                "success" => false, 
                "message" => "Hardware Error: " . $error_detail . " Please ensure the scanner is plugged in."
            ]);
            exit;
        }

        // --- Duplicate Check Against Existing Fingerprints & Current Session ---
        require_once 'helpers/fingerprint_cache.php';
        $base_cache = get_fingerprint_cache_file($connection);
        $nicknames = get_nicknames_cache($connection);

        $temp_file = sys_get_temp_dir() . '/ecozone_enroll_check_' . uniqid() . '.tmp';
        
        // Copy the base cache to a temp file so we can append session fingers
        if (file_exists($base_cache)) {
            copy($base_cache, $temp_file);
            $has_data_to_check = (filesize($temp_file) > 0);
        } else {
            $has_data_to_check = false;
        }

        $file_handle = fopen($temp_file, 'a');

        // Add fingers from the current ongoing 10-finger session
        $session_line_parts = array_fill(0, 11, '');
        $session_line_parts[0] = 999999; // Mock ID for current session
        $has_session_fingers = false;
        for ($i = 0; $i < 10; $i++) {
            $sess_finger = $_POST['session_finger_' . $i] ?? '';
            if (!empty($sess_finger) && $sess_finger !== 'SKIP') {
                $session_line_parts[$i + 1] = trim($sess_finger);
                $has_session_fingers = true;
                $has_data_to_check = true;
            }
        }
        
        if ($has_session_fingers) {
            $nicknames[999999] = 'YOUR PREVIOUSLY SCANNED FINGER';
            $line = implode('|', $session_line_parts) . "\n";
            fwrite($file_handle, $line);
        }
        
        fclose($file_handle);

        if ($has_data_to_check) {
            $check_output = trim(shell_exec('"' . $scanner_path . '" check "' . $fingerprint_data . '" "' . $temp_file . '"'));
            if (file_exists($temp_file)) unlink($temp_file);
            
            if (strpos($check_output, 'MATCH:') === 0) {
                $matched_id = (int)str_replace('MATCH:', '', $check_output);
                
                if ($matched_id === 999999) {
                    echo json_encode([
                        "success" => false, 
                        "message" => "Security Alert: You already scanned this exact fingerprint in the current session!"
                    ]);
                } else {
                    $dup_nickname = $nicknames[$matched_id] ?? 'Unknown';
                    echo json_encode([
                        "success" => false, 
                        "message" => "Security Alert: This fingerprint is already registered to " . $dup_nickname . "!"
                    ]);
                }
                exit;
            }
        } else {
            if (file_exists($temp_file)) unlink($temp_file);
        }
    }

    // Return the scanned data — frontend collects all 10 and sends final save
    echo json_encode([
        "success"      => true,
        "message"      => $finger_names[$finger_index] . " scanned successfully",
        "finger_index" => $finger_index,
        "finger_name"  => $finger_names[$finger_index],
        "finger_data"  => $fingerprint_data
    ]);
    exit;
}

// --- Final save mode: all 10 fingerprints provided (or SKIP) ---
$fingers = [];
$real_count = 0;
for ($i = 0; $i < 10; $i++) {
    $key = 'finger_' . $i;
    $data = trim($_POST[$key] ?? '');

    if (empty($data)) {
        echo json_encode([
            "success" => false, 
            "message" => $finger_names[$i] . " is missing. Please scan or skip all 10 finger slots."
        ]);
        exit;
    }

    if ($data !== 'SKIP') {
        $real_count++;
    }

    $fingers[] = $data;
}

if ($real_count === 0) { echo json_encode(["success" => false, "message" => "You must successfully scan at least 1 finger to register an identity. You cannot skip all 10."]); exit; }

$email = trim($_POST['email'] ?? '');
$email = empty($email) ? null : $email;

// --- Insert into fingerprints table (prepared statement) ---
$sql = "INSERT INTO fingerprints (nickname, right_thumb, right_index_f, right_middle, right_ring, right_pinky, left_thumb, left_index_f, left_middle, left_ring, left_pinky, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "ssssssssssss", 
    $nickname,
    $fingers[0], $fingers[1], $fingers[2], $fingers[3], $fingers[4],
    $fingers[5], $fingers[6], $fingers[7], $fingers[8], $fingers[9],
    $email
);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => false, "message" => "Failed to save registration: " . mysqli_stmt_error($stmt)]);
    mysqli_stmt_close($stmt);
    exit;
}

$employee_id = mysqli_insert_id($connection);
mysqli_stmt_close($stmt);

require_once 'helpers/fingerprint_cache.php';
rebuild_fingerprint_cache($connection);

echo json_encode([
    "success"     => true,
    "message"     => "Registration complete! All 10 fingerprints saved.",
    "employee_id" => $employee_id,
    "nickname"    => $nickname
]);

mysqli_close($connection);
?>




