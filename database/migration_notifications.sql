-- Migration: Add notification support to ECOZONE
-- Run this in phpMyAdmin or MySQL CLI after the initial schema.sql

-- 1. Add contact info columns to fingerprints table
ALTER TABLE fingerprints 
  ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER employee_role,
  ADD COLUMN phone_number VARCHAR(20) DEFAULT NULL AFTER email;

-- 2. Create notification_log table to track all sent notifications  
CREATE TABLE IF NOT EXISTS notification_log (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    employee_id     INT NOT NULL,
    notification_type ENUM('SMS', 'EMAIL') NOT NULL,
    recipient       VARCHAR(255) NOT NULL,
    subject         VARCHAR(255) DEFAULT NULL,
    message         TEXT,
    status          ENUM('SENT', 'FAILED', 'SKIPPED') DEFAULT 'SENT',
    error_message   VARCHAR(500) DEFAULT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES fingerprints(employee_id) ON DELETE CASCADE,
    INDEX idx_notif_emp (employee_id),
    INDEX idx_notif_date (created_at)
);

-- 3. Create notification_settings table for admin configuration
CREATE TABLE IF NOT EXISTS notification_settings (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    setting_key     VARCHAR(100) UNIQUE NOT NULL,
    setting_value   TEXT,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Seed default notification settings
INSERT INTO notification_settings (setting_key, setting_value) VALUES
('late_threshold', '08:00'),
('sms_enabled', '0'),
('email_enabled', '0'),
('semaphore_api_key', ''),
('semaphore_sender_name', 'ECOZONE'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_from_email', 'noreply@ecozone.ph'),
('smtp_from_name', 'ECOZONE Attendance'),
('sms_template', 'ECOZONE: Hi {nickname}, you checked in LATE today at {time}. Your scheduled time was {threshold}. Please coordinate with Admin.'),
('email_subject_template', 'ECOZONE Late Check-In Alert — {nickname}'),
('notify_roles', 'REGULAR')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
