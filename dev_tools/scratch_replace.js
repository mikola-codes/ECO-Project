const fs = require('fs');

const filepath = "C:\\Users\\Librero Kevin Trazie\\OneDrive\\Desktop\\ECO-Project\\admin.html";
const content = fs.readFileSync(filepath, 'utf8');

const match = content.match(/(<script src="assets\/js\/bootstrap\.bundle\.min\.js"><\/script>[\s\S]*)/) || content.match(/(<script src="libs\/bootstrap\.bundle\.min\.js"><\/script>[\s\S]*)/);

if (!match) {
    console.error("Could not find JS part");
    process.exit(1);
}

const jsPart = match[1];

const newHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECOZONE - Secure Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <script>document.documentElement.classList.add('js-enabled');</script>
</head>
<body>
    <!-- Ambient Background Blobs -->
    <div class="ambient-bg">
        <div class="ambient-blob ambient-blob-1"></div>
        <div class="ambient-blob ambient-blob-2"></div>
    </div>

    <!-- Navbar -->
    <nav class="eco-navbar animate-in" id="mainNav">
        <div class="nav-inner">
            <a href="landing.html" class="nav-brand">
                <img src="assets/images/logo.jpg" alt="ECOZONE" class="nav-logo">
                <span class="nav-brand-text">ECOZONE</span>
            </a>
            <div class="nav-links d-none d-md-flex">
                <a href="landing.html" class="nav-link-item">Home</a>
                <a href="index.html" class="nav-link-item">Dashboard</a>
            </div>
            <div class="nav-actions">
                <button class="btn-action" style="background: var(--danger); padding: 8px 16px; font-size: 13px;" id="logoutBtn" onclick="logout()" style="display:none;">🔒 Logout</button>
                <div id="liveClock" class="nav-clock d-none d-sm-block"></div>
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"><span id="themeIcon">🌙</span></button>
            </div>
        </div>
    </nav>

    <!-- LOGIN GATE -->
    <div id="loginGate" class="login-gate animate-in delay-1">
        <div class="glass-card" style="display: grid; grid-template-columns: 1fr 1fr; max-width: 900px; width: 100%; padding: 0; overflow: hidden; align-items: center;">
            <div style="padding: 64px 48px;">
                <h1 class="hero-h1" style="font-size: 36px; margin-bottom: 16px;">Secure Admin Access</h1>
                <p style="color: var(--text-muted); margin-bottom: 40px; font-weight: 500;">Enter your credentials to manage biometric devices and track employee attendance.</p>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label class="step-label">Administrator Password</label>
                        <input type="password" id="adminPwd" class="login-input form-control" placeholder="••••••••" autocomplete="off" style="font-size: 20px; letter-spacing: 4px; padding: 16px;">
                    </div>
                    <button id="loginBtn" class="btn-action" style="padding: 18px;" onclick="attemptLogin()">Unlock Dashboard</button>
                    <div id="loginErr" class="login-error" style="color: var(--danger); font-weight: 700; font-size: 14px; text-align: center;"></div>
                </div>
            </div>
            <div style="background: var(--surface-elevated); height: 100%; display: flex; align-items: center; justify-content: center; position: relative;">
                <div class="ambient-glow-halo" style="background: radial-gradient(circle, var(--brand-glow) 0%, transparent 60%);"></div>
                <svg class="scanner-icon" viewBox="0 0 24 24" fill="none" stroke="var(--brand-primary)" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round" style="width: 200px; height: 200px; opacity: 0.2;"><path d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10"/><path d="M5 12c0-3.866 3.134-7 7-7s7 3.134 7 7"/><path d="M8 12c0-2.209 1.791-4 4-4s4 1.791 4 4"/><path d="M11 12c0-.552.448-1 1-1s1 .448 1 1"/><path d="M12 22C6.477 22 2 17.523 2 12"/><path d="M12 19c-3.866 0-7-3.134-7-7"/><path d="M12 16c-2.209 0-4-1.791-4-4"/><path d="M12 13c-.552 0-1-.448-1-1"/></svg>
            </div>
        </div>
    </div>

    <!-- ADMIN PANEL -->
    <div id="adminPanel" style="display:none; padding-bottom: 100px;">
        <main class="page-content">
            <div class="main-container main-container-wide">
                
                <div class="page-header animate-in">
                    <h1 class="page-title">Admin Panel</h1>
                    <p class="page-subtitle">Holiday Management & Field Duty Override</p>
                </div>

                <!-- Holiday Banner -->
                <div id="todayBanner" class="holiday-banner animate-in" style="margin-bottom: 32px; border-radius: var(--radius-badge);">
                    <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span> 
                    <span id="todayBannerText"></span>
                </div>

                <!-- SEGMENTED TABS -->
                <div class="main-tabs animate-in delay-1">
                    <button class="main-tab" onclick="switchTab('enroll',this)">👆 Enrollment</button>
                    <button class="main-tab active" onclick="switchTab('holidays',this)">🏖️ Holiday Management</button>
                    <button class="main-tab" onclick="switchTab('manual',this)">📋 Manual Attendance</button>
                    <button class="main-tab" onclick="switchTab('roles',this)">👤 Employee Roles</button>
                    <button class="main-tab" onclick="switchTab('history',this)">📜 Override History</button>
                    <button class="main-tab" onclick="switchTab('logs',this)">📊 Attendance Logs</button>
                </div>

                <!-- TAB 0: ENROLLMENT -->
                <div id="tab-enroll" class="tab-pane">
                    <div class="glass-card mb-4" id="enrollSection">
                        <div class="card-header-bar">
                            👆 Register New Employee — 10 Fingerprints
                        </div>
                        <div class="card-body-content">
                            <!-- Step 1 -->
                            <div id="step1" style="max-width: 500px; margin: 0 auto; text-align: center;">
                                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--brand-glow); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </div>
                                <h3 style="font-size: 24px; margin-bottom: 8px;">Step 1 — Employee Identity</h3>
                                <p style="color: var(--text-muted); margin-bottom: 32px;">Enter a unique nickname to begin the 10-finger biometric enrollment process.</p>
                                <input type="text" id="nickname" class="form-control" placeholder="Enter Employee Nickname" required maxlength="100" style="text-align: center; font-size: 18px; margin-bottom: 24px;">
                                <button id="startEnrollBtn" class="btn-action" style="width: 100%;">Start 10-Finger Enrollment &rarr;</button>
                            </div>

                            <!-- Step 2 -->
                            <div id="step2" style="display:none;">
                                <div style="text-align: center; margin-bottom: 40px;">
                                    <h3 style="font-size: 24px; margin-bottom: 8px;">Step 2 — Scan All 10 Fingers</h3>
                                    <p style="color: var(--text-muted);">Scan each finger one at a time. Click <b>Skip</b> if a finger cannot be scanned.</p>
                                    
                                    <div class="progress-bar-wrap" style="max-width: 400px; margin: 24px auto 12px;">
                                        <div class="progress-bar-fill" id="progressBar" style="width:0%"></div>
                                    </div>
                                    <p id="progressText" style="font-weight: 700; color: var(--brand-primary); font-size: 14px;">0 / 10 fingers scanned</p>
                                </div>

                                <div class="bento-hands-wrapper">
                                    <!-- Left Hand -->
                                    <div class="glass-card" style="padding: 32px;">
                                        <div class="hand-card-header">Left Hand</div>
                                        <div class="fingers-grid">
                                            <div class="finger-bento-slot" id="slot-9" data-index="9"><div class="finger-circle-bento">10</div><span class="caption">Pinky</span></div>
                                            <div class="finger-bento-slot" id="slot-8" data-index="8"><div class="finger-circle-bento">9</div><span class="caption">Ring</span></div>
                                            <div class="finger-bento-slot" id="slot-7" data-index="7"><div class="finger-circle-bento">8</div><span class="caption">Middle</span></div>
                                            <div class="finger-bento-slot" id="slot-6" data-index="6"><div class="finger-circle-bento">7</div><span class="caption">Index</span></div>
                                            <div class="finger-bento-slot" id="slot-5" data-index="5"><div class="finger-circle-bento">6</div><span class="caption">Thumb</span></div>
                                        </div>
                                    </div>
                                    <!-- Right Hand -->
                                    <div class="glass-card" style="padding: 32px;">
                                        <div class="hand-card-header">Right Hand</div>
                                        <div class="fingers-grid">
                                            <div class="finger-bento-slot" id="slot-0" data-index="0"><div class="finger-circle-bento">1</div><span class="caption">Thumb</span></div>
                                            <div class="finger-bento-slot" id="slot-1" data-index="1"><div class="finger-circle-bento">2</div><span class="caption">Index</span></div>
                                            <div class="finger-bento-slot" id="slot-2" data-index="2"><div class="finger-circle-bento">3</div><span class="caption">Middle</span></div>
                                            <div class="finger-bento-slot" id="slot-3" data-index="3"><div class="finger-circle-bento">4</div><span class="caption">Ring</span></div>
                                            <div class="finger-bento-slot" id="slot-4" data-index="4"><div class="finger-circle-bento">5</div><span class="caption">Pinky</span></div>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 16px; margin-top: 40px;">
                                    <button id="backFingerBtn" class="btn-outline" disabled>&larr; Back</button>
                                    <button id="scanFingerBtn" class="btn-scan" style="flex-grow: 1;">
                                        <span class="btn-text">Scan Right Thumb</span>
                                        <span class="btn-spinner" style="display:none;"><span class="spinner"></span> Scanning...</span>
                                    </button>
                                    <button id="skipFingerBtn" class="btn-outline">Skip</button>
                                </div>
                            </div>
                            <div id="enrollMessage" class="status-message" style="margin-top: 24px; text-align: center;"></div>
                        </div>
                    </div>
                </div>

                <!-- TAB 1: HOLIDAY MANAGEMENT -->
                <div id="tab-holidays" class="tab-pane active">
                    <div class="glass-card mb-4">
                        <div class="card-header-bar">➕ Add New Holiday</div>
                        <div class="card-body-content">
                            <div class="form-grid g4">
                                <div class="fg">
                                    <label for="hName">Holiday Name</label>
                                    <input type="text" id="hName" placeholder="e.g. Christmas Day" class="form-control">
                                </div>
                                <div class="fg">
                                    <label for="hDate">Date</label>
                                    <input type="date" id="hDate" class="form-control">
                                </div>
                                <div class="fg">
                                    <label for="hType">Type</label>
                                    <select id="hType" class="form-control">
                                        <option value="REGULAR">Regular Holiday</option>
                                        <option value="SPECIAL_NON_WORKING">Special Non-Working</option>
                                        <option value="SPECIAL_WORKING">Special Working</option>
                                        <option value="COMPANY">Company Holiday</option>
                                    </select>
                                </div>
                                <button class="btn-action" id="hAddBtn" onclick="addHoliday()">Add</button>
                            </div>
                            <div id="hMsg" class="fmsg"></div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-header-bar">
                            📋 Holiday Calendar <span id="hCount" class="count-pill">0</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Holiday</th><th>Date</th><th>Type</th><th>Recurring</th><th style="text-align:right">Actions</th></tr></thead>
                                <tbody id="hBody"></tbody>
                            </table>
                        </div>
                        <p id="hStatus" class="empty-note" style="display:none; padding:32px; text-align:center; color:var(--text-muted);"></p>
                    </div>
                </div>

                <!-- TAB 2: MANUAL ATTENDANCE -->
                <div id="tab-manual" class="tab-pane">
                    <div class="glass-card mb-4">
                        <div class="card-header-bar">➕ Record Manual / Field Attendance</div>
                        <div class="card-body-content">
                            <div class="form-grid g3">
                                <div class="fg">
                                    <label for="maEmployee">Employee</label>
                                    <select id="maEmployee" class="form-control"><option value="">Loading...</option></select>
                                </div>
                                <div class="fg"><label for="maDate">Date</label><input type="date" id="maDate" class="form-control"></div>
                                <div class="fg">
                                    <label for="maStatus">Duty Status</label>
                                    <select id="maStatus" class="form-control">
                                        <option value="ON_FIELD_DUTY">🚛 On Field Duty</option>
                                        <option value="COMPANY_TASK">🏢 Company Task</option>
                                        <option value="OFFICIAL_BUSINESS">💼 Official Business</option>
                                        <option value="LATE_EXCUSED">⏰ Late Excused</option>
                                        <option value="ADMIN_OVERRIDE">🔑 Admin Override</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-grid g2" style="margin-top:20px">
                                <div class="fg"><label for="maTimeIn">Time In (optional)</label><input type="time" id="maTimeIn" class="form-control"></div>
                                <div class="fg"><label for="maTimeOut">Time Out (optional)</label><input type="time" id="maTimeOut" class="form-control"></div>
                            </div>
                            <div class="fg" style="margin-top:20px">
                                <label for="maReason">Reason / Notes</label>
                                <textarea id="maReason" placeholder="e.g. Driver assigned to deliver supplies" class="form-control"></textarea>
                            </div>
                            <button class="btn-action" id="maBtn" onclick="addManualAttendance()" style="margin-top:24px">Submit Manual Attendance</button>
                            <div id="maMsg" class="fmsg"></div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-header-bar">
                            📋 Manual Attendance Records <span id="maCount" class="count-pill">0</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Employee</th><th>Date</th><th>Duty Status</th><th>Time In/Out</th><th>Reason</th><th style="text-align:right">Del</th></tr></thead>
                                <tbody id="maBody"></tbody>
                            </table>
                        </div>
                        <p id="maStatus2" class="empty-note" style="display:none; padding:32px; text-align:center; color:var(--text-muted);"></p>
                    </div>
                </div>

                <!-- TAB 3: EMPLOYEE ROLES -->
                <div id="tab-roles" class="tab-pane">
                    <div class="glass-card">
                        <div class="card-header-bar">👤 Employee Roles <span id="roleCount" class="count-pill">0</span></div>
                        <div style="margin: 24px; padding: 16px; background: rgba(59,130,246,0.1); border-left: 4px solid #3b82f6; border-radius: 8px; color: #3b82f6; font-size: 14px; font-weight: 600;">
                            Assign roles to employees. <strong>FIELD</strong>, <strong>DRIVER</strong>, and <strong>FLEXIBLE</strong> roles allow late/manual attendance without being marked absent.
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>ID</th><th>Nickname</th><th>Current Role</th><th style="text-align:right">Change Role</th></tr></thead>
                                <tbody id="roleBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: OVERRIDE HISTORY -->
                <div id="tab-history" class="tab-pane">
                    <div class="glass-card">
                        <div class="card-header-bar">📜 All Manual Attendance History <span id="histCount" class="count-pill">0</span></div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Employee</th><th>Date</th><th>Status</th><th>Time In</th><th>Time Out</th><th>Reason</th><th>Submitted</th></tr></thead>
                                <tbody id="histBody"></tbody>
                            </table>
                        </div>
                        <p id="histStatus" class="empty-note" style="display:none; padding:32px; text-align:center; color:var(--text-muted);"></p>
                    </div>
                </div>

                <!-- TAB 5: ATTENDANCE LOGS -->
                <div id="tab-logs" class="tab-pane">
                    <div class="glass-card mb-4" style="padding: 24px;">
                        <div class="form-grid g3">
                            <div class="fg"><label>Filter by Date</label><input type="date" id="dateFilter" class="form-control"></div>
                            <div class="fg"><label>Search Nickname</label><input type="text" id="searchFilter" class="form-control" placeholder="Type a nickname..."></div>
                            <div class="fg" style="justify-content: flex-end;"><button id="clearFilters" class="btn-outline">Clear Filters</button></div>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-header-bar">📊 Attendance Records <span id="recordCount" class="count-pill">0</span></div>
                        <div class="table-responsive">
                            <table class="table" id="attendanceTable">
                                <thead><tr><th>ID</th><th>Nickname</th><th>Date & Time</th><th>Status</th><th>Holiday</th><th>Duty</th></tr></thead>
                                <tbody id="logTableBody"></tbody>
                            </table>
                        </div>
                        <p id="statusMessage" style="text-align: center; padding: 24px; color: var(--text-muted);"><span class="spinner"></span> Loading records...</p>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Confirm Modal -->
    <div id="confirmOverlay" class="overlay">
        <div class="modal-box">
            <h3 class="modal-title">Confirm Delete</h3>
            <p id="confirmText" class="modal-text">Are you sure?</p>
            <div class="modal-btns">
                <button class="mbtn" onclick="cancelConfirm()">Cancel</button>
                <button class="mbtn-yes" onclick="doConfirm()">Delete</button>
            </div>
        </div>
    </div>
` + jsPart;

fs.writeFileSync(filepath, newHtml, 'utf8');
console.log("admin.html rewritten via Node.js");
