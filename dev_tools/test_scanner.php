<?php
$scanner_path = realpath(__DIR__ . '/bin/scanner.exe');
echo "Path: " . $scanner_path . "\n";
$out = shell_exec('"' . $scanner_path . '" enroll 2>&1');
echo "Output: " . $out . "\n";
?>
