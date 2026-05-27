-- ============================================================
-- ECOZONE Fingerprint Attendance System
-- Database Schema — 10-Finger Flat Table Design
-- ============================================================

CREATE DATABASE IF NOT EXISTS ecozone_attendance;
USE ecozone_attendance;

-- ============================================================
-- Table 1: fingerprints
-- Flat table storing employee info + all 10 fingerprints
-- All finger columns are NOT NULL
-- ============================================================
CREATE TABLE IF NOT EXISTS fingerprints (
    employee_id     INT AUTO_INCREMENT PRIMARY KEY,
    nickname        VARCHAR(100) NOT NULL,
    right_thumb     TEXT NOT NULL,
    right_index_f   TEXT NOT NULL,
    right_middle    TEXT NOT NULL,
    right_ring      TEXT NOT NULL,
    right_pinky     TEXT NOT NULL,
    left_thumb      TEXT NOT NULL,
    left_index_f    TEXT NOT NULL,
    left_middle     TEXT NOT NULL,
    left_ring       TEXT NOT NULL,
    left_pinky      TEXT NOT NULL,
    date_registered DATETIME DEFAULT CURRENT_TIMESTAMP,
    employee_role   VARCHAR(50) DEFAULT 'REGULAR'
);

-- ============================================================
-- Table 2: attendance_log
-- Stores employee_id, nickname, and full timestamp
-- ============================================================
CREATE TABLE IF NOT EXISTS attendance_log (
    log_id        INT AUTO_INCREMENT PRIMARY KEY,
    employee_id   INT NOT NULL,
    nickname      VARCHAR(100) NOT NULL,
    log_time      DATETIME NOT NULL,
    log_type      ENUM('TIME_IN', 'TIME_OUT') NOT NULL DEFAULT 'TIME_IN',
    holiday_flag  VARCHAR(50) DEFAULT NULL,
    holiday_name  VARCHAR(200) DEFAULT NULL,
    duty_status   VARCHAR(50) DEFAULT NULL,
    duty_reason   VARCHAR(500) DEFAULT NULL,
    admin_override BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (employee_id) REFERENCES fingerprints(employee_id) ON DELETE CASCADE,
    INDEX idx_employee_date (employee_id, log_time)
);

-- ============================================================
-- Table 3: manual_attendance
-- Admin-entered attendance for field employees
-- ============================================================
CREATE TABLE IF NOT EXISTS manual_attendance (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    employee_id     INT NOT NULL,
    nickname        VARCHAR(100) NOT NULL,
    attendance_date DATE NOT NULL,
    duty_status     VARCHAR(50) NOT NULL,
    duty_reason     VARCHAR(500) DEFAULT NULL,
    time_in         DATETIME DEFAULT NULL,
    time_out        DATETIME DEFAULT NULL,
    admin_notes     VARCHAR(500) DEFAULT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES fingerprints(employee_id) ON DELETE CASCADE,
    INDEX idx_emp_date (employee_id, attendance_date)
);

-- ============================================================
-- Table 3: api_keys
-- Manages valid API keys for external applications
-- ============================================================
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    app_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table 4: holidays
-- Stores all holidays (fixed, special, custom)
-- ============================================================
CREATE TABLE IF NOT EXISTS holidays (
    holiday_id      INT AUTO_INCREMENT PRIMARY KEY,
    holiday_name    VARCHAR(200) NOT NULL,
    holiday_date    DATE NOT NULL,
    holiday_type    ENUM('REGULAR', 'SPECIAL_NON_WORKING', 'SPECIAL_WORKING', 'COMPANY') NOT NULL,
    is_recurring    BOOLEAN DEFAULT FALSE,
    year_specific   INT DEFAULT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_holiday (holiday_date, holiday_name),
    INDEX idx_holiday_date (holiday_date)
);

-- ============================================================
-- Seed: Philippine Holidays 2026
-- ============================================================
INSERT INTO holidays (holiday_name, holiday_date, holiday_type, is_recurring, year_specific) VALUES
-- Regular Holidays (Fixed — Recurring)
('New Year''s Day',             '2026-01-01', 'REGULAR', TRUE, NULL),
('Araw ng Kagitingan',          '2026-04-09', 'REGULAR', TRUE, NULL),
('Labor Day',                   '2026-05-01', 'REGULAR', TRUE, NULL),
('Independence Day',            '2026-06-12', 'REGULAR', TRUE, NULL),
('Bonifacio Day',               '2026-11-30', 'REGULAR', TRUE, NULL),
('Christmas Day',               '2026-12-25', 'REGULAR', TRUE, NULL),
('Rizal Day',                   '2026-12-30', 'REGULAR', TRUE, NULL),
-- Regular Holidays (Movable — Year-specific)
('Maundy Thursday',             '2026-04-02', 'REGULAR', FALSE, 2026),
('Good Friday',                 '2026-04-03', 'REGULAR', FALSE, 2026),
('Eid''l Fitr',                 '2026-03-20', 'REGULAR', FALSE, 2026),
('Eid''l Adha',                 '2026-05-27', 'REGULAR', FALSE, 2026),
('National Heroes Day',         '2026-08-31', 'REGULAR', FALSE, 2026),
-- Special Non-Working Holidays
('EDSA People Power Anniversary','2026-02-25', 'SPECIAL_NON_WORKING', TRUE, NULL),
('Chinese New Year',             '2026-02-17', 'SPECIAL_NON_WORKING', FALSE, 2026),
('Black Saturday',               '2026-04-04', 'SPECIAL_NON_WORKING', FALSE, 2026),
('Ninoy Aquino Day',             '2026-08-21', 'SPECIAL_NON_WORKING', TRUE, NULL),
('All Saints'' Day',             '2026-11-01', 'SPECIAL_NON_WORKING', TRUE, NULL),
('Feast of Immaculate Conception','2026-12-08', 'SPECIAL_NON_WORKING', TRUE, NULL),
('Last Day of the Year',         '2026-12-31', 'SPECIAL_NON_WORKING', TRUE, NULL);
