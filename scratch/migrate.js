const fs = require('fs');

let admin = fs.readFileSync('admin.html', 'utf-8');
let idx = fs.readFileSync('index.html', 'utf-8');
let att = fs.readFileSync('attendance.html', 'utf-8');

// 1. Extract Enrollment Card from index.html
const enroll_start = idx.indexOf('<!-- ============================== -->\n        <!-- Registration & Enrollment Card -->');
const verify_start = idx.indexOf('<!-- ============================== -->\n        <!-- Verification Card              -->');
let enroll_card = idx.substring(enroll_start, verify_start);

// 2. Extract JS from index.html
const js_start = idx.indexOf('// =============================================\n        // Finger names');
const js_end = idx.indexOf('// =============================================\n        // Verification');
let enroll_js = idx.substring(js_start, js_end);

// 3. Extract Attendance Card from attendance.html
const att_filters_start = att.indexOf('<!-- Filters -->');
const att_card_end = att.indexOf('<div class="footer-text">ECOZONE Attendance System');
let att_content = att.substring(att_filters_start, att_card_end);

// 4. Extract JS from attendance.html
const att_js_start = att.indexOf('// Elements\n        var tableBody');
const att_js_end = att.indexOf('// Admin Panel Password Prompt');
let att_js = att.substring(att_js_start, att_js_end);

// 5. Inject into admin.html
const tabs_target = '<div class="admin-tabs">';
const tabs_replacement = `<div class="admin-tabs">
                <button class="admin-tab" onclick="switchTab('enroll')">👆 Enrollment</button>
                <button class="admin-tab active" onclick="switchTab('manual')">📋 Manual Attendance</button>
                <button class="admin-tab" onclick="switchTab('roles')">👤 Employee Roles</button>
                <button class="admin-tab" onclick="switchTab('history')">📜 Override History</button>
                <button class="admin-tab" onclick="switchTab('logs')">📊 Attendance Logs</button>`;
admin = admin.replace(tabs_target, tabs_replacement);

const tab_manual_start = admin.indexOf('<!-- TAB 1: Manual Attendance -->');
const admin_enroll_content = `<!-- TAB 0: Enrollment -->
            <div id="tab-enroll" class="tab-content">
                ${enroll_card}
            </div>
            
`;
admin = admin.substring(0, tab_manual_start) + admin_enroll_content + admin.substring(tab_manual_start);

const tab_history_start = admin.indexOf('<!-- TAB 3: Override History -->');
// Wait, in my view of admin.html previously, "history" was TAB 3.
// Let's just put logs before the footer.
const footer_start = admin.indexOf('<div class="footer-text">ECOZONE');
const admin_logs_content = `<!-- TAB 4: Attendance Logs -->
            <div id="tab-logs" class="tab-content">
                ${att_content}
            </div>
            
`;
admin = admin.substring(0, footer_start) + admin_logs_content + admin.substring(footer_start);

const admin_js_target = '// ─── Tabs ─────────────────────────────────';
const admin_js_replacement = `${enroll_js}

${att_js}

        // ─── Tabs ─────────────────────────────────`;
admin = admin.replace(admin_js_target, admin_js_replacement);

// Update switchTab logic in admin
const switch_tab_old = "if (name === 'history') loadHistory();";
const switch_tab_new = `if (name === 'history') loadHistory();
            if (name === 'logs') loadAttendance('');`;
admin = admin.replace(switch_tab_old, switch_tab_new);

// Also remove the attendance nav link from admin.html
admin = admin.replace('<a href="attendance.html" class="nav-link-item">Attendance</a>', '');

// Also remove from admin.html the button loading helper since we import it from index.html JS which requires it
const btn_loading = `// =============================================
        // Button loading helper
        // =============================================
        function setButtonLoading(btn, loading) {
            var txt = btn.querySelector('.btn-text');
            var spin = btn.querySelector('.btn-spinner');
            if (loading) { txt.style.display = 'none'; spin.style.display = 'inline-flex'; btn.disabled = true; }
            else { txt.style.display = 'inline'; spin.style.display = 'none'; btn.disabled = false; }
        }`;
admin = admin.replace(admin_js_target, btn_loading + '\\n\\n        ' + admin_js_target);


// Update index.html
idx = idx.substring(0, enroll_start) + idx.substring(verify_start);
idx = idx.replace('<div class="text-center">\\n            <a href="attendance.html" class="nav-link-bottom">View Attendance Logs &rarr;</a>\\n        </div>', '');
idx = idx.replace('<a href="attendance.html" class="nav-link-bottom">View Attendance Logs &rarr;</a>', '');
idx = idx.replace('<a href="attendance.html" class="nav-link-item">Attendance</a>', '');
idx = idx.replace('<a href="index.html" class="nav-cta">Dashboard Login</a>', '<a href="admin.html" class="nav-cta" title="Admin Panel">🔒 Admin Login</a>');
idx = idx.substring(0, js_start) + idx.substring(js_end);

fs.writeFileSync('admin.html', admin, 'utf-8');
fs.writeFileSync('index.html', idx, 'utf-8');

console.log("Migration complete.");
