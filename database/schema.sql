-- ============================================================
-- ECOZONE Fingerprint Attendance System
-- Database Schema
-- ============================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS ecozone_attendance;
USE ecozone_attendance;

-- ============================================================
-- Table 1: employees
-- Stores basic information about each employee
-- ============================================================
CREATE TABLE employees (
    employee_id     INT AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    position        VARCHAR(100),
    date_registered DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE fingerprints (
    fingerprint_id   INT AUTO_INCREMENT PRIMARY KEY,
    employee_id      INT NOT NULL,
    fingerprint_data TEXT NOT NULL,
    date_enrolled    DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

CREATE TABLE attendance_log (
    log_id       INT AUTO_INCREMENT PRIMARY KEY,
    employee_id  INT NOT NULL,
    log_date     DATE NOT NULL,
    log_time     TIME NOT NULL,

    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);
