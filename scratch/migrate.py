import os

with open('admin.html', 'r', encoding='utf-8') as f:
    admin = f.read()

with open('index.html', 'r', encoding='utf-8') as f:
    idx = f.read()

with open('attendance.html', 'r', encoding='utf-8') as f:
    att = f.read()

# 1. Extract Enrollment Card from index.html
enroll_start = idx.find('<!-- ============================== -->\n        <!-- Registration & Enrollment Card -->')
verify_start = idx.find('<!-- ============================== -->\n        <!-- Verification Card              -->')
if enroll_start != -1 and verify_start != -1:
    enroll_card = idx[enroll_start:verify_start]
else:
    enroll_card = "<!-- Enroll Card NOT FOUND -->"

# 2. Extract JS from index.html
js_start = idx.find('// =============================================\n        // Finger names')
js_end = idx.find('// =============================================\n        // Verification')
if js_start != -1 and js_end != -1:
    enroll_js = idx[js_start:js_end]
else:
    enroll_js = "/* Enroll JS NOT FOUND */"

# 3. Extract Attendance Card from attendance.html
att_filters_start = att.find('<!-- Filters -->')
att_card_end = att.find('<div class="footer-text">ECOZONE Attendance System')
if att_filters_start != -1 and att_card_end != -1:
    att_content = att[att_filters_start:att_card_end]
else:
    att_content = "<!-- Attendance Card NOT FOUND -->"

# 4. Extract JS from attendance.html
att_js_start = att.find('// Elements\n        var tableBody')
att_js_end = att.find('// Admin Panel Password Prompt')
if att_js_start != -1 and att_js_end != -1:
    att_js = att[att_js_start:att_js_end]
else:
    att_js = "/* Attendance JS NOT FOUND */"

# 5. Inject into admin.html
tabs_target = '<div class="admin-tabs">'
tabs_replacement = '''<div class="admin-tabs">
                <button class="admin-tab" onclick="switchTab(\'enroll\')">👆 Enrollment</button>
                <button class="admin-tab active" onclick="switchTab(\'manual\')">📋 Manual Attendance</button>
                <button class="admin-tab" onclick="switchTab(\'logs\')">📊 Attendance Logs</button>'''
admin = admin.replace(tabs_target, tabs_replacement)

tab_manual_start = admin.find('<!-- TAB 1: Manual Attendance -->')
admin_enroll_content = f'''<!-- TAB 0: Enrollment -->
            <div id="tab-enroll" class="tab-content">
                {enroll_card}
            </div>
            
'''
admin = admin[:tab_manual_start] + admin_enroll_content + admin[tab_manual_start:]

tab_roles_start = admin.find('<!-- TAB 2: Employee Roles -->')
admin_logs_content = f'''<!-- TAB 1.5: Attendance Logs -->
            <div id="tab-logs" class="tab-content">
                {att_content}
            </div>
            
'''
admin = admin[:tab_roles_start] + admin_logs_content + admin[tab_roles_start:]

admin_js_target = '// ─── Tabs ─────────────────────────────────'
admin_js_replacement = f'''{enroll_js}

{att_js}

        // ─── Tabs ─────────────────────────────────'''
admin = admin.replace(admin_js_target, admin_js_replacement)

# Update switchTab logic in admin
switch_tab_old = '''if (name === 'history') loadHistory();'''
switch_tab_new = '''if (name === 'history') loadHistory();
            if (name === 'logs') loadAttendance('');'''
admin = admin.replace(switch_tab_old, switch_tab_new)

# Update index.html
if enroll_start != -1 and verify_start != -1:
    idx = idx[:enroll_start] + idx[verify_start:]

link_att = '<div class="text-center">\\n            <a href="attendance.html" class="nav-link-bottom">View Attendance Logs &rarr;</a>\\n        </div>'
# Try a simpler replace if exact match fails
idx = idx.replace('<a href="attendance.html" class="nav-link-bottom">View Attendance Logs &rarr;</a>', '')

nav_target = '<a href="index.html" class="nav-cta">Dashboard Login</a>'
nav_replacement = '<a href="admin.html" class="nav-cta" title="Admin Panel">🔒 Admin</a>'
idx = idx.replace(nav_target, nav_replacement)
idx = idx.replace('<a href="attendance.html" class="nav-link-item">Attendance</a>', '')
admin = admin.replace('<a href="attendance.html" class="nav-link-item">Attendance</a>', '')

if js_start != -1 and js_end != -1:
    idx = idx[:js_start] + idx[js_end:]

with open('admin.html', 'w', encoding='utf-8') as f:
    f.write(admin)

with open('index.html', 'w', encoding='utf-8') as f:
    f.write(idx)

print("Migration complete.")
