<?php
if (!defined('IN_APP')) { http_response_code(403); exit('Forbidden'); }
// api/helpers/fingerprint_cache.php

function rebuild_fingerprint_cache($connection) {
    $cache_dir = __DIR__ . '/../cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    $cache_file = $cache_dir . '/fmds_cache.txt';
    $temp_file = $cache_file . '.tmp';
    
    $query = "SELECT employee_id, nickname, right_thumb, right_index_f, right_middle, right_ring, right_pinky, left_thumb, left_index_f, left_middle, left_ring, left_pinky FROM fingerprints";
    $result = mysqli_query($connection, $query);
    
    $file_handle = fopen($temp_file, 'w');
    if (!$file_handle) return false;
    
    $nicknames = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $eid = $row['employee_id'];
            $nicknames[$eid] = $row['nickname'];
            $line = implode('|', [
                $eid,
                $row['right_thumb'],
                $row['right_index_f'],
                $row['right_middle'],
                $row['right_ring'],
                $row['right_pinky'],
                $row['left_thumb'],
                $row['left_index_f'],
                $row['left_middle'],
                $row['left_ring'],
                $row['left_pinky']
            ]) . "\n";
            fwrite($file_handle, $line);
        }
    }
    fclose($file_handle);
    rename($temp_file, $cache_file);
    
    // Also save nicknames map for fast verification lookup
    file_put_contents($cache_dir . '/nicknames_cache.json', json_encode($nicknames));
    
    return true;
}

function get_fingerprint_cache_file($connection) {
    $cache_file = __DIR__ . '/../cache/fmds_cache.txt';
    if (!file_exists($cache_file)) {
        rebuild_fingerprint_cache($connection);
    }
    return $cache_file;
}

function get_nicknames_cache($connection) {
    $cache_file = __DIR__ . '/../cache/nicknames_cache.json';
    if (!file_exists($cache_file)) {
        rebuild_fingerprint_cache($connection);
    }
    return json_decode(file_get_contents($cache_file), true) ?: [];
}
?>
