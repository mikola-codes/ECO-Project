# ECOZONE Fingerprint Attendance System

## Project Overview

ECOZONE is a comprehensive, hardware-integrated time and attendance system that leverages 10-finger biometric data for highly reliable employee tracking limit enrollment.

## Key Features

- **10-Finger Enrollment**: Comprehensive registration process capturing all 10 fingers for an employee, ensuring high fault tolerance.
- **Fingerprint Skip & Undo**: Gracefully handles missing fingers by allowing specific fingers to be explicitly skipped, and offers a **Back** feature to undo errant scans.
- **Security & Anti-Duplication**: Actively prevents duplicate fingerprint registration; if a scanned finger exists under another employee, enrollment for that fingerprint is immediately blocked.
- **Attendance Logging**: Automatically records "TIME IN" and "TIME OUT" sessions upon successful fingerprint verification.
- **Real-Time Dashboard**: An interactive, browser-based UI for seamless enrollment and viewing of attendance logs.

## Architecture & Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JS, and Bootstrap 5.
- **Backend API**: PHP (handles routing, form parsing, database connectivity, and executing the scanner binary).
- **Hardware Integration**: Custom C++ executable (`scanner.cpp`) acting as a wrapper around the DigitalPersona (U.are.U) SDK (`dpfpdd.dll`, `dpfj.dll`).
- **Database**: MySQL, utilizing a flat-table schema design for structured biometric storage and fast retrieval.

## Directory Structure

- `api/`: PHP API endpoints (`enroll.php`, `verify.php`, `get_attendance.php`, etc.).
- `assets/`: Contains frontend styling (CSS) and images.
- `bin/`: Contains the compiled fingerprint scanner executable (`scanner.exe`).
- `database/`: Contains `schema.sql` to instantiate the required MySQL database schema.
- `src/`: Contains `scanner.cpp`, the C++ source code bridging the web application to the biometric hardware.
- Root HTML files:
  - `index.html`: The main dashboard for enrolling users and verifying attendance.
  - `attendance.html`: A page to view organized real-time attendance logs.

## Setup & Installation

### 1. Database Configuration

1. Open XAMPP or your preferred local web server environment.
2. Ensure the MySQL module is running.
3. Import the `database/schema.sql` file to create the `ecozone_attendance` database and its necessary tables.

### 2. Hardware Integration (DigitalPersona SDK)

1. Install the official DigitalPersona (U.are.U) SDK for Windows.
2. Ensure the driver is installed and the scanner device is plugged in via USB.
3. Required DLLs (`dpfpdd.dll`, `dpfj.dll`) must be locatable by the `scanner.exe` (they can be placed in `C:\Windows\System32`, the `bin/` dir, or the SDK default path).

### 3. Application Setup

1. Place the `ECO-Project-Razel` directory into your local web server's document root (e.g., `C:\xampp\htdocs\`).
2. Verify that the database connectivity credentials configured in `api/config.php` match your local MySQL configuration.
3. Ensure PHP processes have the permission required to execute `bin/scanner.exe`.

## Usage Guide

### Enrollment

1. Open `http://localhost/ECO-Project-Razel/index.html` in your web browser.
2. Enter the new employee's nickname.
3. Follow the guided 10-finger scanning process. If a finger is missing, click the **Skip** button.
4. Once all 10 fingers are scanned/skipped, the system logs the biometric signatures to the database.

### Attendance Verification

1. Open `http://localhost/ECO-Project-Razel/index.html`.
2. Locate the **Verify Attendance** section.
3. Instruct the employee to place any previously enrolled finger onto the biometric scanner.
4. Click **Scan Fingerprint**; upon successful verification, the system registers either a **TIME IN** or **TIME OUT**.
