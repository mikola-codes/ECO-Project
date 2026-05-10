<?php
$scanner_path = realpath(__DIR__ . '/../bin/scanner.exe');

echo "Scanning finger...\n";
$fingerprint_data = trim(shell_exec('"' . $scanner_path . '" enroll 2>&1'));

if (empty($fingerprint_data) || strpos($fingerprint_data, 'ERROR') === 0) {
    die("Scanner error: " . $fingerprint_data . "\n");
}

echo "Captured hex length: " . strlen($fingerprint_data) . "\n";

$temp_file = sys_get_temp_dir() . '/test_check.tmp';
$file_handle = fopen($temp_file, 'w');

// Mock data: ID|F1|F2...
$line_parts = array_fill(0, 11, '');
$line_parts[0] = '999999';
$line_parts[1] = $fingerprint_data;
$line = implode('|', $line_parts) . "\n";
fwrite($file_handle, $line);
fclose($file_handle);

echo "Running check mode...\n";
$check_output = trim(shell_exec('"' . $scanner_path . '" check "' . $fingerprint_data . '" "' . $temp_file . '" 2>&1'));
echo "Check output: \n" . $check_output . "\n";

if (file_exists($temp_file)) unlink($temp_file);
?>
