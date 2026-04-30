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
    date_registered DATETIME DEFAULT CURRENT_TIMESTAMP
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

    FOREIGN KEY (employee_id) REFERENCES fingerprints(employee_id),
    INDEX idx_employee_date (employee_id, log_time)
);
