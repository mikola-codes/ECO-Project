const fs = require('fs');
const filepath = "admin.html";
let content = fs.readFileSync(filepath, 'utf8');

// 1. Add Email and Phone inputs to Enrollment tab
const emailPhoneHtml = `
                                    <p style="color: var(--text-muted); margin-bottom: 8px; line-height: 1.6;">Enter Employee Email</p>
                                    <input type="email" id="enrollEmail" class="form-control" placeholder="Email Address (Optional)" style="text-align: center; font-size: 16px; padding: 16px; border-radius: 12px; margin-bottom: 16px; border: 2px solid var(--border); background: var(--surface); color: var(--text-main); width: 100%;">
                                    <p style="color: var(--text-muted); margin-bottom: 8px; line-height: 1.6;">Enter Phone Number</p>
                                    <input type="text" id="enrollPhone" class="form-control" placeholder="Phone Number (Optional)" style="text-align: center; font-size: 16px; padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 2px solid var(--border); background: var(--surface); color: var(--text-main); width: 100%;">
`;
if (!content.includes('id="enrollEmail"')) {
    content = content.replace(/<button id="startEnrollBtn"/, emailPhoneHtml + '<button id="startEnrollBtn"');
}

// 2. Add Sidebar Tabs for Notification Settings and Notification Logs
const notifTabs = `
                      <button class="main-tab dash-tab" onclick="switchTab('notifsettings',this)"><span style="font-size:18px; opacity: 0.8;">⚙️</span> Notif Settings</button>
                      <button class="main-tab dash-tab" onclick="switchTab('notiflogs',this)"><span style="font-size:18px; opacity: 0.8;">📩</span> Notif Logs</button>
`;
if (!content.includes("switchTab('notifsettings'")) {
    content = content.replace(/(<button class="main-tab dash-tab" onclick="switchTab\('roles',this\)">.*?<\/button>)/, '$1' + notifTabs);
}

// 3. Add Tab Content for Notification Settings and Logs
const notifContent = `
                <!-- TAB 6: NOTIFICATION SETTINGS -->
                <div id="tab-notifsettings" class="tab-pane">
                    <div class="dash-glass-card animate-in delay-2">
                        <div class="dash-card-title">⚙️ Notification Settings</div>
                        <div class="form-grid g2">
                            <div class="fg">
                                <label style="font-weight: 700; color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">Late Threshold (Time)</label>
                                <input type="time" id="nsLateThreshold" class="form-control">
                            </div>
                            <div class="fg" style="display:flex; align-items:center;">
                                <label style="font-weight: 700; color: var(--text-muted); font-size: 13px; margin-right:10px;">Enable Email</label>
                                <input type="checkbox" id="nsEnableEmail" style="width:20px; height:20px;">
                            </div>
                            <div class="fg" style="display:flex; align-items:center;">
                                <label style="font-weight: 700; color: var(--text-muted); font-size: 13px; margin-right:10px;">Enable SMS</label>
                                <input type="checkbox" id="nsEnableSms" style="width:20px; height:20px;">
                            </div>
                        </div>
                        <p id="nsMsg" class="status-message" style="margin-top:16px;"></p>
                        <button class="btn-primary" onclick="saveNotifSettings()" style="width: 100%; border-radius: 100px; padding: 20px; font-size: 16px; font-weight: 700; box-shadow: 0 10px 20px var(--brand-glow); margin-top:20px;">Save Settings</button>
                    </div>
                </div>

                <!-- TAB 7: NOTIFICATION LOGS -->
                <div id="tab-notiflogs" class="tab-pane">
                    <div class="dash-glass-card animate-in delay-2">
                        <div class="dash-card-title">📩 Notification Logs</div>
                        <div class="table-responsive">
                            <table class="dash-table">
                                <thead><tr><th>Employee ID</th><th>Type</th><th>Status</th><th>Message</th><th>Sent At</th></tr></thead>
                                <tbody id="notifLogBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
`;
if (!content.includes('id="tab-notifsettings"')) {
    content = content.replace(/(<\/div>\s*<\/main>)/, notifContent + '\n        $1');
}

// 4. Inject Missing Javascript for Enrollment and new Notification tabs
const injectedJs = `
        // ══════════════════════════════════════
        // NOTIFICATIONS
        // ══════════════════════════════════════
        function loadNotifSettings() {
            fetch('api/notification_settings.php').then(r => r.json()).then(d => {
                if (d.success && d.data) {
                    document.getElementById('nsLateThreshold').value = d.data.late_threshold;
                    document.getElementById('nsEnableEmail').checked = (d.data.enable_email == 1);
                    document.getElementById('nsEnableSms').checked = (d.data.enable_sms == 1);
                }
            }).catch(e => console.error(e));
        }

        function saveNotifSettings() {
            var fd = new FormData();
            fd.append('late_threshold', document.getElementById('nsLateThreshold').value);
            fd.append('enable_email', document.getElementById('nsEnableEmail').checked ? '1' : '0');
            fd.append('enable_sms', document.getElementById('nsEnableSms').checked ? '1' : '0');
            fetch('api/notification_settings.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
                var el = document.getElementById('nsMsg');
                if (el) { el.textContent = d.success ? '✅ Saved' : '❌ ' + d.message; el.className = 'status-message ' + (d.success ? 'ok' : 'err'); }
            }).catch(e => console.error(e));
        }

        function loadNotifLogs() {
            fetch('api/notification_log.php').then(r => r.json()).then(d => {
                if (d.success) {
                    var tbody = document.getElementById('notifLogBody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        d.data.forEach(row => {
                            var tr = document.createElement('tr');
                            tr.innerHTML = '<td>'+row.employee_id+'</td><td>'+row.notification_type+'</td><td><span class="badge-pill dt-field">'+row.status+'</span></td><td style="font-size:12px;max-width:160px;word-break:break-word;">'+row.message+'</td><td>'+row.sent_at+'</td>';
                            tbody.appendChild(tr);
                        });
                    }
                }
            }).catch(e => console.error(e));
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('button') && e.target.closest('button').getAttribute('onclick')) {
                var oc = e.target.closest('button').getAttribute('onclick');
                if (oc.includes("switchTab('notifsettings'")) loadNotifSettings();
                if (oc.includes("switchTab('notiflogs'")) loadNotifLogs();
            }
        });

        // ══════════════════════════════════════
        // ENROLLMENT (RESTORED)
        // ══════════════════════════════════════
        var startEnrollBtn = document.getElementById('startEnrollBtn');
        var enrollMessage = document.getElementById('enrollMessage');

        if(startEnrollBtn) {
            startEnrollBtn.addEventListener('click', function() {
                var nicknameInput = document.getElementById('nickname');
                var nn = nicknameInput.value.trim();
                if(!nn) { enrollMessage.innerHTML = '❌ Enter a nickname'; enrollMessage.className='status-message error'; return; }
                
                var finalFd = new FormData();
                finalFd.append('nickname', nn);
                
                var eml = document.getElementById('enrollEmail');
                if(eml) finalFd.append('email', eml.value.trim());
                
                var phn = document.getElementById('enrollPhone');
                if(phn) finalFd.append('phone_number', phn.value.trim());
                
                for(var i=0; i<10; i++) finalFd.append('finger_'+i, 'SKIP');
                
                startEnrollBtn.disabled = true;
                startEnrollBtn.textContent = 'Enrolling...';
                
                fetch('api/enroll.php', { method: 'POST', body: finalFd })
                    .then(res => res.json())
                    .then(data => {
                        startEnrollBtn.disabled = false;
                        startEnrollBtn.textContent = 'Start 10-Finger Enrollment →';
                        if (data.success) {
                            enrollMessage.innerHTML = '✅ Enrolled successfully! (Fingers skipped)';
                            enrollMessage.className = 'status-message ok';
                            nicknameInput.value = '';
                            if(eml) eml.value = '';
                            if(phn) phn.value = '';
                        } else {
                            enrollMessage.innerHTML = '❌ ' + data.message;
                            enrollMessage.className = 'status-message err';
                        }
                    }).catch(err => {
                        startEnrollBtn.disabled = false;
                        startEnrollBtn.textContent = 'Start 10-Finger Enrollment →';
                        enrollMessage.innerHTML = '❌ ' + err;
                        enrollMessage.className = 'status-message err';
                    });
            });
        }
`;
if (!content.includes('function loadNotifSettings')) {
    content = content.replace(/(<script>\s*\/\/ ══════════════════════════════════════)/, '<script>\n' + injectedJs + '\n$1');
}

fs.writeFileSync(filepath, content, 'utf8');
console.log("admin.html patched fully successfully");
