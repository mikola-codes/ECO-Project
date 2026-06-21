import re

with open('admin.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Find the last <script> tag that doesn't have src or is just bare
script_match = re.search(r'(<script>(?!document\.documentElement).*?</script>)', content, re.DOTALL)
if not script_match:
    print("Could not find main script block!")
    exit(1)

js_block = script_match.group(1)

new_html = f'''<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECOZONE - Secure Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin-premium.css?v=2">
    <script>document.documentElement.classList.add('js-enabled');</script>
</head>
<body>
    <nav class="vault-nav" id="mainNav">
        <a href="landing.html" class="vault-nav-brand" style="text-decoration:none; color:inherit;">
            <div class="status-dot"></div> ECOZONE SECURE
        </a>
        <div class="vault-actions">
            <div id="liveClock" style="font-family: var(--font-mono); font-size: 13px;"></div>
            <button class="vault-btn-outline" id="logoutBtn" onclick="logout()" style="display:none; padding: 6px 12px; font-size: 11px;">DISCONNECT</button>
        </div>
    </nav>

    <!-- LOGIN GATE -->
    <div id="loginGate" class="login-gate">
        <div class="terminal-box">
            <div class="terminal-header">SYSTEM LOCKED</div>
            <div class="terminal-sub">AWAITING ADMINISTRATOR CREDENTIALS</div>
            <input type="password" id="adminPwd" class="terminal-input" placeholder="•••••••••" autocomplete="off">
            <button id="loginBtn" class="terminal-btn" onclick="attemptLogin()">AUTHORIZE ACCESS</button>
            <div id="loginErr" style="color: var(--accent-danger); font-family: var(--font-mono); font-size: 12px; margin-top: 16px; text-align: center;"></div>
        </div>
    </div>

    <!-- ADMIN PANEL -->
    <div id="adminPanel" style="display:none;" class="dashboard-layout">
        <div class="page-title">COMMAND CENTER</div>
        <div class="page-sub">Biometric Identity & Infrastructure Control</div>
        
        <div id="todayBanner" style="display:none; background: rgba(0, 230, 118, 0.1); border: 1px solid var(--accent-secure); padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 24px; font-family: var(--font-mono); font-size: 12px; color: var(--accent-secure);">
            <span id="todayBannerText"></span>
        </div>

        <!-- SEGMENTED TABS -->
        <div class="vault-tabs">
            <button class="vault-tab main-tab active" onclick="switchTab('enroll',this)">ENROLLMENT</button>
            <button class="vault-tab main-tab" onclick="switchTab('holidays',this)">HOLIDAYS</button>
            <button class="vault-tab main-tab" onclick="switchTab('manual',this)">MANUAL ATTENDANCE</button>
            <button class="vault-tab main-tab" onclick="switchTab('roles',this)">ROLES</button>
            <button class="vault-tab main-tab" onclick="switchTab('history',this)">HISTORY</button>
            <button class="vault-tab main-tab" onclick="switchTab('logs',this)">LOGS</button>
            <button class="vault-tab main-tab" onclick="switchTab('notifsettings',this)">NOTIFICATIONS</button>
            <button class="vault-tab main-tab" onclick="switchTab('notiflogs',this)">NOTIF LOGS</button>
        </div>

        <!-- TAB 0: ENROLLMENT -->
        <div id="tab-enroll" class="tab-pane active">
            <div class="vault-panel" id="enrollSection">
                <div class="panel-header">NEW IDENTITY ENROLLMENT <span class="badge badge-neutral">10 FINGERS</span></div>
                
                <div id="step1" style="max-width: 400px;">
                    <label class="vault-label">NICKNAME</label>
                    <input type="text" id="nickname" class="vault-input" placeholder="ENTER IDENTIFIER" style="margin-bottom:16px;">
                    
                    <label class="vault-label">EMAIL (OPTIONAL)</label>
                    <input type="email" id="enrollEmail" class="vault-input" placeholder="user@domain.com" style="margin-bottom:16px;">
                    
                    <label class="vault-label">PHONE (OPTIONAL)</label>
                    <input type="text" id="enrollPhone" class="vault-input" placeholder="+XX XXX XXXX" style="margin-bottom:24px;">
                    
                    <button id="startEnrollBtn" class="vault-btn-primary" style="width:100%;">INITIALIZE SEQUENCE</button>
                </div>

                <div id="step2" style="display:none;">
                    <div style="margin-bottom: 24px;">
                        <div style="font-family: var(--font-mono); font-size: 14px; margin-bottom: 8px;">SCANNING PROGRESS: <span id="progressText" class="text-accent">0/10</span></div>
                    <div style="display: flex; gap: 16px;">
                        <button id="backFingerBtn" class="vault-btn-outline" disabled>PREV</button>
                        <button id="scanFingerBtn" class="vault-btn-primary" style="flex-grow: 1;">SCAN FINGER</button>
                        <button id="skipFingerBtn" class="vault-btn-outline">SKIP</button>
                    </div>
                    <div id="enrollMessage" class="fmsg" style="text-align: center;"></div>
                </div>
            </div>
        </div>
        </div>

        <!-- TAB 1: HOLIDAYS -->
        <div id="tab-holidays" class="tab-pane">
            <div class="vault-panel">
                <div class="panel-header">REGISTER HOLIDAY</div>
                <div class="vault-grid" style="align-items: end;">
                    <div><label class="vault-label">NAME</label><input type="text" id="hName" class="vault-input" placeholder="e.g. Christmas"></div>
                    <div><label class="vault-label">DATE</label><input type="date" id="hDate" class="vault-input"></div>
                    <div>
                        <label class="vault-label">TYPE</label>
                        <select id="hType" class="vault-input">
                            <option value="REGULAR">REGULAR</option>
                            <option value="SPECIAL_NON_WORKING">SPECIAL NON-WORKING</option>
                            <option value="SPECIAL_WORKING">SPECIAL WORKING</option>
                            <option value="COMPANY">COMPANY</option>
                        </select>
                    </div>
                    <button id="hAddBtn" class="vault-btn-primary" onclick="addHoliday()">REGISTER</button>
                </div>
                <div id="hMsg" class="fmsg"></div>
            </div>
            
            <div class="vault-panel">
                <div class="panel-header">HOLIDAY DIRECTORY <span id="hCount" class="panel-count">0</span></div>
                <div class="vault-table-wrapper">
                    <table class="vault-table">
                        <thead><tr><th>HOLIDAY</th><th>DATE</th><th>TYPE</th><th>RECURRING</th><th style="text-align:right">ACTIONS</th></tr></thead>
                        <tbody id="hBody"></tbody>
                    </table>
                </div>
                <p id="hStatus" style="display:none; padding:32px; text-align:center; color:var(--text-secondary); font-family:var(--font-mono); font-size:12px;"></p>
            </div>
        </div>

        <!-- TAB 2: MANUAL ATTENDANCE -->
        <div id="tab-manual" class="tab-pane">
            <div class="vault-panel">
                <div class="panel-header">RECORD MANUAL OVERRIDE</div>
                <div class="vault-grid" style="margin-bottom: 16px;">
                    <div>
                        <label class="vault-label">EMPLOYEE</label>
                        <select id="maEmployee" class="vault-input"><option value="">LOADING...</option></select>
                    </div>
                    <div><label class="vault-label">DATE</label><input type="date" id="maDate" class="vault-input"></div>
                    <div>
                        <label class="vault-label">DUTY STATUS</label>
                        <select id="maStatus" class="vault-input">
                            <option value="ON_FIELD_DUTY">ON FIELD DUTY</option>
                            <option value="COMPANY_TASK">COMPANY TASK</option>
                            <option value="OFFICIAL_BUSINESS">OFFICIAL BUSINESS</option>
                            <option value="LATE_EXCUSED">LATE EXCUSED</option>
                            <option value="ADMIN_OVERRIDE">ADMIN OVERRIDE</option>
                        </select>
                    </div>
                </div>
                <div class="vault-grid" style="margin-bottom: 16px;">
                    <div><label class="vault-label">TIME IN (OPTIONAL)</label><input type="time" id="maTimeIn" class="vault-input"></div>
                    <div><label class="vault-label">TIME OUT (OPTIONAL)</label><input type="time" id="maTimeOut" class="vault-input"></div>
                </div>
                <div style="margin-bottom: 24px;">
                    <label class="vault-label">NOTES / REASON</label>
                    <textarea id="maReason" class="vault-input" placeholder="Justification..."></textarea>
                </div>
                <button id="maBtn" class="vault-btn-primary" onclick="addManualAttendance()">SUBMIT OVERRIDE</button>
                <div id="maMsg" class="fmsg"></div>
            </div>

            <div class="vault-panel">
                <div class="panel-header">MANUAL OVERRIDE LOGS <span id="maCount" class="panel-count">0</span></div>
                <div class="vault-table-wrapper">
                    <table class="vault-table">
                        <thead><tr><th>EMPLOYEE</th><th>DATE</th><th>STATUS</th><th>TIME IN/OUT</th><th>REASON</th><th style="text-align:right">DEL</th></tr></thead>
                        <tbody id="maBody"></tbody>
                    </table>
                </div>
                <p id="maStatus2" style="display:none; padding:32px; text-align:center; color:var(--text-secondary); font-family:var(--font-mono); font-size:12px;"></p>
            </div>
        </div>

        <!-- TAB 3: ROLES -->
        <div id="tab-roles" class="tab-pane">
            <div class="vault-panel">
                <div class="panel-header">IDENTITY ROLES <span id="roleCount" class="panel-count">0</span></div>
                <div style="margin-bottom: 24px; font-family: var(--font-mono); font-size: 11px; color: var(--text-secondary); line-height: 1.5; border: 1px solid var(--border-dim); padding: 12px; border-radius: var(--radius-sm);">
                    FIELD, DRIVER, and FLEXIBLE roles are exempt from rigid late penalties.
                </div>
                <div class="vault-table-wrapper">
                    <table class="vault-table">
                        <thead><tr><th>ID</th><th>NICKNAME</th><th>CURRENT ROLE</th><th style="text-align:right">ASSIGN ROLE</th></tr></thead>
                        <tbody id="roleBody"></tbody>
                    </table>
                </div>
                <div id="roleMsg" class="fmsg"></div>
            </div>
        </div>

        <!-- TAB 4: HISTORY -->
        <div id="tab-history" class="tab-pane">
            <div class="vault-panel">
                <div class="panel-header">SECURITY AUDIT / OVERRIDE HISTORY</div>
                <div class="vault-table-wrapper">
                    <table class="vault-table">
                        <thead><tr><th>EMP ID</th><th>TYPE</th><th>DESCRIPTION</th><th>TIMESTAMP</th></tr></thead>
                        <tbody id="histBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 5: LOGS -->
        <div id="tab-logs" class="tab-pane">
            <div class="vault-panel">
                <div class="panel-header">LIVE ATTENDANCE STREAM</div>
                <div class="vault-table-wrapper">
                    <table class="vault-table">
                        <thead><tr><th>ID</th><th>NICKNAME</th><th>TIMESTAMP</th><th>STATUS</th><th>HOLIDAY</th><th>DUTY</th></tr></thead>
                        <tbody id="logTableBody"></tbody>
                    </table>
                </div>
                <p id="statusMessage" style="text-align: center; padding: 24px; color: var(--text-secondary); font-family:var(--font-mono); font-size:12px;"><span class="spinner"></span> CONNECTING...</p>
            </div>
        </div>

        <!-- TAB 6: NOTIFICATIONS -->
        <div id="tab-notifsettings" class="tab-pane">
            <div class="vault-panel">
                <div class="panel-header">ALERT CONFIGURATION</div>
                <div class="vault-grid">
                    <div><label class="vault-label">LATE THRESHOLD (TIME)</label><input type="time" id="nsLateThreshold" class="vault-input"></div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="nsEnableEmail" style="width:16px; height:16px;">
                        <label class="vault-label" style="margin:0;">ENABLE EMAIL ALERTS</label>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="nsEnableSms" style="width:16px; height:16px;">
                        <label class="vault-label" style="margin:0;">ENABLE SMS ALERTS</label>
                    </div>
                </div>
                <p id="nsMsg" class="fmsg"></p>
                <button class="vault-btn-primary" onclick="saveNotifSettings()" style="margin-top:24px;">APPLY CONFIGURATION</button>
            </div>
        </div>

        <!-- TAB 7: NOTIF LOGS -->
        <div id="tab-notiflogs" class="tab-pane">
            <div class="vault-panel">
                <div class="panel-header">ALERT TRANSMISSION LOGS</div>
                <div class="vault-table-wrapper">
                    <table class="vault-table">
                        <thead><tr><th>EMP ID</th><th>CHANNEL</th><th>STATUS</th><th>PAYLOAD</th><th>TIMESTAMP</th></tr></thead>
                        <tbody id="notifLogBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Confirm Modal -->
    <div id="confirmOverlay" class="overlay">
        <div class="modal-box">
            <div class="modal-title" style="font-family: var(--font-mono); color: var(--accent-danger); font-size:18px; font-weight:700;">CONFIRM DELETION</div>
            <p id="confirmText" class="modal-text" style="font-family: var(--font-mono); font-size:12px;">ARE YOU SURE?</p>
            <div class="modal-btns">
                <button class="vault-btn-outline" onclick="cancelConfirm()">ABORT</button>
                <button class="vault-btn-primary" style="background: var(--accent-danger);" onclick="doConfirm()">EXECUTE</button>
            </div>
        </div>
    </div>

    {js_block}
</body>
</html>
'''

with open('admin.html', 'w', encoding='utf-8') as f:
    f.write(new_html)

print("HTML Replaced")
