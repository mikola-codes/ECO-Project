<?php
// api/helpers/notifications.php

// ========================================================================
// 🛑 CONFIGURATION SECTION 🛑
// ========================================================================

// 📱 1. SEMAPHORE SMS CONFIGURATION
// Go to https://semaphore.co/, create an account, and paste your API Key below:
define('SEMAPHORE_API_KEY', 'YOUR_SEMAPHORE_API_KEY_HERE');
define('SEMAPHORE_SENDER_NAME', 'ECOZONE'); // Max 11 characters. Leave as is unless you bought a custom sender name.

// 📧 2. GMAIL SMTP CONFIGURATION
// You need a Gmail App Password. Go to your Google Account -> Security -> 2-Step Verification -> App passwords.
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'YOUR_16_DIGIT_APP_PASSWORD_HERE');
define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
define('SMTP_FROM_NAME', 'ECOZONE Attendance System');

// ========================================================================

function getNotifSettings($connection) {
    $settings = [];
    $result = mysqli_query($connection, "SELECT setting_key, setting_value FROM notification_settings");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function sendSemaphoreSMS($phone, $message) {
    if (SEMAPHORE_API_KEY === 'YOUR_SEMAPHORE_API_KEY_HERE') {
        return ['success' => false, 'response' => 'Semaphore API Key not configured in code.'];
    }

    $url = 'https://api.semaphore.co/api/v4/messages';
    $data = [
        'apikey' => SEMAPHORE_API_KEY,
        'number' => $phone,
        'message' => $message,
        'sendername' => SEMAPHORE_SENDER_NAME
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return ['success' => false, 'response' => 'Network error or unable to reach Semaphore API'];
    }

    $decoded = json_decode($result, true);
    if (isset($decoded['message_id']) || (is_array($decoded) && isset($decoded[0]['message_id']))) {
         return ['success' => true, 'response' => $result];
    }
    
    return ['success' => false, 'response' => $result];
}

function sendEmail($to, $subject, $htmlBody) {
    if (SMTP_PASSWORD === 'YOUR_16_DIGIT_APP_PASSWORD_HERE') {
        return ['success' => false, 'message' => 'Gmail App Password not configured in code.'];
    }

    // Connect to Gmail SMTP using fsockopen
    $smtp_server = 'ssl://smtp.gmail.com';
    $port = 465;
    $timeout = 30;

    $socket = @fsockopen($smtp_server, $port, $errno, $errstr, $timeout);
    if (!$socket) {
        return ['success' => false, 'message' => "Could not connect to SMTP server: $errstr"];
    }

    stream_set_timeout($socket, $timeout);
    $res = fread($socket, 515);

    $commands = [
        "EHLO localhost",
        "AUTH LOGIN",
        base64_encode(SMTP_USERNAME),
        base64_encode(SMTP_PASSWORD),
        "MAIL FROM:<" . SMTP_FROM_EMAIL . ">",
        "RCPT TO:<" . $to . ">",
        "DATA"
    ];

    foreach ($commands as $cmd) {
        fwrite($socket, $cmd . "\r\n");
        $res = fread($socket, 515);
        if (substr($res, 0, 1) == '4' || substr($res, 0, 1) == '5') {
            fclose($socket);
            return ['success' => false, 'message' => "SMTP Error on command $cmd: $res"];
        }
    }

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "To: <" . $to . ">\r\n";
    $headers .= "Subject: " . $subject . "\r\n\r\n";

    fwrite($socket, $headers . $htmlBody . "\r\n.\r\n");
    $res = fread($socket, 515);
    if (substr($res, 0, 1) == '4' || substr($res, 0, 1) == '5') {
        fclose($socket);
        return ['success' => false, 'message' => "SMTP Error on sending data: $res"];
    }

    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return ['success' => true, 'message' => 'Email sent successfully via SMTP'];
}

function buildLateEmailHTML($nickname, $logTime, $threshold, $employeeId) {
    return "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #059669; color: white; padding: 20px; text-align: center;'>
            <h2 style='margin: 0;'>ECOZONE Attendance</h2>
        </div>
        <div style='padding: 20px; color: #374151;'>
            <h3 style='color: #ef4444; margin-top: 0;'>Late Check-In Detected</h3>
            <p>Hi <strong>{$nickname}</strong> (ID: {$employeeId}),</p>
            <p>This is an automated notification that you have checked in late today.</p>
            <div style='background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>Check-in Time:</strong> {$logTime}</p>
                <p style='margin: 5px 0;'><strong>Expected Time:</strong> {$threshold}</p>
            </div>
            <p>Please coordinate with the Admin regarding this late arrival.</p>
        </div>
        <div style='background-color: #f9fafb; padding: 15px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #e5e7eb;'>
            ZAMBOECOZONE Employee Attendance System
        </div>
    </div>";
}

function logNotification($connection, $employeeId, $type, $recipient, $subject, $message, $status, $errorMsg) {
    $stmt = mysqli_prepare($connection, "INSERT INTO notification_log (employee_id, notification_type, recipient, subject, message, status, error_message) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssss", $employeeId, $type, $recipient, $subject, $message, $status, $errorMsg);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function checkAndNotifyLate($connection, $employeeId, $nickname, $logTime, $logType) {
    if ($logType !== 'TIME_IN') return;

    $settings = getNotifSettings($connection);
    $lateThreshold = $settings['late_threshold'] ?? '08:00';
    
    $timeOnly = date('H:i', strtotime($logTime));
    
    if ($timeOnly <= $lateThreshold) return; // Not late

    $empStmt = mysqli_prepare($connection, "SELECT employee_role, email, phone_number FROM fingerprints WHERE employee_id = ?");
    mysqli_stmt_bind_param($empStmt, "i", $employeeId);
    mysqli_stmt_execute($empStmt);
    $empResult = mysqli_stmt_get_result($empStmt);
    $emp = mysqli_fetch_assoc($empResult);
    mysqli_stmt_close($empStmt);

    if (!$emp) return;

    $today = date('Y-m-d', strtotime($logTime));
    $holidayStmt = mysqli_prepare($connection, "SELECT 1 FROM holidays WHERE holiday_date = ?");
    mysqli_stmt_bind_param($holidayStmt, "s", $today);
    mysqli_stmt_execute($holidayStmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($holidayStmt)) > 0) {
        mysqli_stmt_close($holidayStmt);
        return; // Holiday, don't notify
    }
    mysqli_stmt_close($holidayStmt);

    $startOfDay = $today . ' 00:00:00';
    $endOfDay = $today . ' 23:59:59';
    $dupStmt = mysqli_prepare($connection, "SELECT 1 FROM notification_log WHERE employee_id = ? AND created_at >= ? AND created_at <= ?");
    mysqli_stmt_bind_param($dupStmt, "iss", $employeeId, $startOfDay, $endOfDay);
    mysqli_stmt_execute($dupStmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($dupStmt)) > 0) {
        mysqli_stmt_close($dupStmt);
        return; // Already notified today
    }
    mysqli_stmt_close($dupStmt);

    $replace = ['{nickname}' => $nickname, '{time}' => $timeOnly, '{threshold}' => $lateThreshold];

    // SEND SMS
    if (($settings['enable_sms'] ?? '0') === '1' && !empty($emp['phone_number'])) {
        $smsMsg = "Hi {$nickname}, you timed in late today at {$timeOnly}. Expected time: {$lateThreshold}. - ECOZONE";
        $res = sendSemaphoreSMS($emp['phone_number'], $smsMsg);
        
        $status = $res['success'] ? 'SENT' : 'FAILED';
        $errorMsg = $res['success'] ? null : substr($res['response'], 0, 500);
        logNotification($connection, $employeeId, 'SMS', $emp['phone_number'], null, $smsMsg, $status, $errorMsg);
    }

    // SEND EMAIL
    if (($settings['enable_email'] ?? '0') === '1' && !empty($emp['email'])) {
        $subject = "Late Check-In Alert: {$nickname}";
        $htmlBody = buildLateEmailHTML($nickname, $timeOnly, $lateThreshold, $employeeId);
        $res = sendEmail($emp['email'], $subject, $htmlBody);
        
        $status = $res['success'] ? 'SENT' : 'FAILED';
        $errorMsg = $res['success'] ? null : $res['message'];
        logNotification($connection, $employeeId, 'EMAIL', $emp['email'], $subject, "HTML Email Sent", $status, $errorMsg);
    }
}
?>
