<?php
include 'config.php';
header('Content-Type: application/json');

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

        $fingerprint_data = trim(shell_exec('"' . $scanner_path . '" enroll 2>&1'));

        if (empty($fingerprint_data) || strpos($fingerprint_data, 'ERROR') === 0) {
            echo json_encode([
                "success" => false, 
                "message" => "Scanner error on " . $finger_names[$finger_index] . ": " . $fingerprint_data
            ]);
            exit;
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

// Require at least 1 real fingerprint
if ($real_count === 0) {
    echo json_encode(["success" => false, "message" => "At least 1 fingerprint is required. Cannot skip all 10."]);
    exit;
}

// --- Insert into fingerprints table (prepared statement) ---
$sql = "INSERT INTO fingerprints (nickname, right_thumb, right_index_f, right_middle, right_ring, right_pinky, left_thumb, left_index_f, left_middle, left_ring, left_pinky) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "sssssssssss", 
    $nickname,
    $fingers[0], $fingers[1], $fingers[2], $fingers[3], $fingers[4],
    $fingers[5], $fingers[6], $fingers[7], $fingers[8], $fingers[9]
);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => false, "message" => "Failed to save registration: " . mysqli_stmt_error($stmt)]);
    mysqli_stmt_close($stmt);
    exit;
}

$employee_id = mysqli_insert_id($connection);
mysqli_stmt_close($stmt);

echo json_encode([
    "success"     => true,
    "message"     => "Registration complete! All 10 fingerprints saved.",
    "employee_id" => $employee_id,
    "nickname"    => $nickname
]);

mysqli_close($connection);
?>
