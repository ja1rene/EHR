<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

generate_csrf_token();

$page_title = "Backup &amp; Recovery";
$role_label = "Administrator";
$nav_links = [
    ['label' => 'Dashboard',       'url' => 'dashboard.php'],
    ['label' => 'Users',           'url' => 'users.php'],
    ['label' => 'Audit Logs',      'url' => 'audit_logs.php'],
    ['label' => 'Reports',         'url' => 'reports.php'],
    ['label' => 'Backup & Recovery', 'url' => 'backup.php', 'active' => true],
];

ob_start();
?>
<div class="report-note" id="staleWarning" style="display:none;">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    No backup has been made in the last 7 days.
</div>

<div class="kpi-grid" id="kpiGrid">
    <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
        <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></div>
        <div class="kpi-number kpi-skeleton" id="kpiTotalBackups">—</div>
        <div class="kpi-label">Successful Backups</div>
    </div>
    <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
        <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="kpi-number kpi-skeleton" id="kpiLastBackup" style="font-size:1.1rem;">—</div>
        <div class="kpi-label">Last Backup</div>
    </div>
    <div class="kpi-card" style="--kpi-accent: #B45309; --kpi-icon-bg: var(--peach-tint);">
        <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="kpi-number kpi-skeleton" id="kpiThisWeek">—</div>
        <div class="kpi-label">Backups This Week</div>
    </div>
    <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
        <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="kpi-number kpi-skeleton" id="kpiFileCount">—</div>
        <div class="kpi-label">Backup Files</div>
    </div>
</div>

<div class="two-col-grid">
    <div class="card action-card">
        <div class="action-card-icon teal">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </div>
        <h3>Create Backup</h3>
        <p>Runs a full export of the database — patients, appointments, consultations, prescriptions, everything — into a timestamped .sql file.</p>
        <button type="button" class="btn-primary full" id="btnCreateBackup">Backup Now</button>
    </div>

    <div class="card action-card">
        <div class="action-card-icon amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.29"/></svg>
        </div>
        <h3>Restore Database</h3>
        <div class="warning-box">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>This overwrites all current data. Cannot be undone — back up first.</span>
        </div>
        <div class="upload-zone" id="uploadZone">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p><strong>Click to upload</strong> a .sql file, or drag it here</p>
            <input type="file" id="backupFileInput" accept=".sql" style="display:none;">
        </div>
        <div class="file-chosen" id="fileChosen"></div>
        <button type="button" class="btn-secondary full" id="btnRestoreUpload" style="margin-top:12px;">Restore from Upload</button>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Backup Files on Server</div>
    <div class="card">
        <table class="data-table" id="filesTable">
            <thead><tr><th>Filename</th><th>Size</th><th>Created</th><th></th></tr></thead>
            <tbody id="filesBody"></tbody>
        </table>
        <div id="filesEmpty" class="empty-state" style="display:none;"><p>No backup files yet. Click "Backup Now" to create the first one.</p></div>
        <div id="filesLoading" class="loading-state">Loading…</div>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Backup History</div>
    <div class="card">
        <table class="data-table" id="historyTable">
            <thead><tr><th>Filename</th><th>Size</th><th>Created By</th><th>Date</th><th>Status</th></tr></thead>
            <tbody id="historyBody"></tbody>
        </table>
        <div id="historyEmpty" class="empty-state" style="display:none;"><p>No backup history yet.</p></div>
        <div id="historyLoading" class="loading-state">Loading…</div>
    </div>
</div>

<style>
.two-col-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 24px 0 32px; }
.action-card { padding: 24px; }
.action-card-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
.action-card-icon.teal { background: var(--teal-tint); color: var(--teal); }
.action-card-icon.amber { background: var(--peach-tint); color: #B45309; }
.action-card h3 { font-family: 'Fraunces', serif; font-size: 1.05rem; font-weight: 600; margin-bottom: 6px; }
.action-card p { font-size: 0.83rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 16px; }

.warning-box {
    display: flex; gap: 9px; align-items: flex-start;
    background: var(--peach-tint); color: #B45309; border-radius: 8px;
    padding: 10px 12px; font-size: 0.78rem; line-height: 1.4; margin-bottom: 14px;
}
.warning-box svg { flex-shrink: 0; margin-top: 1px; }

.upload-zone {
    border: 1.5px dashed var(--border); border-radius: 10px; padding: 20px; text-align: center;
    cursor: pointer; transition: border-color 0.15s, background 0.15s; margin-bottom: 6px;
}
.upload-zone:hover, .upload-zone.dragover { border-color: var(--indigo); background: var(--indigo-tint); }
.upload-zone svg { color: var(--text-muted); margin: 0 auto 8px; display: block; }
.upload-zone p { font-size: 0.82rem; color: var(--text-secondary); }
.file-chosen { font-size: 0.78rem; color: var(--teal); font-weight: 600; margin-top: 6px; display: none; }

.btn-primary {
    background: var(--indigo); color: white; border: none; padding: 11px 20px; border-radius: 999px;
    font-size: 0.85rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;
}
.btn-primary:hover { background: var(--indigo-dark); }
.btn-primary.full { width: 100%; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-secondary {
    background: var(--bg); color: var(--text-secondary); border: 1.5px solid var(--border);
    padding: 11px 20px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.btn-secondary.full { width: 100%; }
.btn-secondary:disabled { opacity: 0.6; cursor: not-allowed; }

.data-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.data-table th { text-align: left; padding: 10px 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border-bottom: 1.5px solid var(--border); }
.data-table td { padding: 11px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.filename-mono { font-family: monospace; font-size: 0.78rem; }

.row-actions-inline { display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap; }
.btn-mini {
    padding: 5px 11px; border-radius: 999px; border: 1px solid var(--border); background: var(--surface);
    font-size: 0.74rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; text-decoration: none; color: var(--text-secondary);
}
.btn-mini.download:hover { background: var(--teal-tint); border-color: #cfe4e2; color: var(--teal); }
.btn-mini.restore:hover { background: var(--peach-tint); border-color: #f0dcb8; color: #B45309; }
.btn-mini.delete:hover { background: #FDECEA; border-color: #f2c6c3; color: #B3261E; }

.status-pill { display: inline-flex; padding: 3px 11px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; }
.status-success { background: var(--teal-tint); color: var(--teal); }
.status-failed { background: #FDECEA; color: #B3261E; }

.empty-state, .loading-state { text-align: center; padding: 36px 20px; color: var(--text-muted); font-size: 0.85rem; display: none; }
.loading-state { display: block; }

@media (max-width: 780px) {
    .two-col-grid { grid-template-columns: 1fr; }
    .data-table { display: block; overflow-x: auto; white-space: nowrap; }
}
</style>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
let csrfToken = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

/* ── Load KPIs + history ── */
async function loadHistory() {
    const historyBody = document.getElementById('historyBody');
    const historyEmpty = document.getElementById('historyEmpty');
    const historyLoading = document.getElementById('historyLoading');

    historyLoading.style.display = 'block';
    historyEmpty.style.display = 'none';
    historyBody.innerHTML = '';

    try {
        const response = await fetch('<?= base_url("api/admin_backup_history.php") ?>');
        const data = await response.json();
        historyLoading.style.display = 'none';
        if (!data.success) return;

        const setKpi = (id, value) => { const el = document.getElementById(id); el.textContent = value; el.classList.remove('kpi-skeleton'); };
        setKpi('kpiTotalBackups', data.total_success);
        setKpi('kpiThisWeek', data.this_week);
        setKpi('kpiLastBackup', data.last_backup_at ? data.last_backup_at.split(' ')[0] : 'Never');

        document.getElementById('staleWarning').style.display = data.this_week === 0 ? 'flex' : 'none';

        if (data.logs.length === 0) {
            historyEmpty.style.display = 'block';
            return;
        }

        data.logs.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="filename-mono">${escapeHtml(log.filename)}</td>
                <td>${escapeHtml(log.file_size)}</td>
                <td>${escapeHtml(log.created_by_name || 'Unknown')}</td>
                <td>${escapeHtml(log.created_at)}</td>
                <td><span class="status-pill status-${log.status}">${log.status === 'success' ? 'Success' : 'Failed'}</span></td>
            `;
            historyBody.appendChild(row);
        });
    } catch (err) {
        historyLoading.style.display = 'none';
    }
}

/* ── Load physical backup files ── */
async function loadFiles() {
    const filesBody = document.getElementById('filesBody');
    const filesEmpty = document.getElementById('filesEmpty');
    const filesLoading = document.getElementById('filesLoading');

    filesLoading.style.display = 'block';
    filesEmpty.style.display = 'none';
    filesBody.innerHTML = '';

    try {
        const response = await fetch('<?= base_url("api/admin_backup_files.php") ?>');
        const data = await response.json();
        filesLoading.style.display = 'none';

        const setKpi = (id, value) => { const el = document.getElementById(id); el.textContent = value; el.classList.remove('kpi-skeleton'); };
        setKpi('kpiFileCount', data.files ? data.files.length : 0);

        if (!data.success || data.files.length === 0) {
            filesEmpty.style.display = 'block';
            return;
        }

        data.files.forEach(f => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="filename-mono">${escapeHtml(f.name)}</td>
                <td>${escapeHtml(f.size)}</td>
                <td>${escapeHtml(f.modified)}</td>
                <td>
                    <div class="row-actions-inline">
                        <a class="btn-mini download" href="<?= base_url('api/admin_backup_download.php') ?>?file=${encodeURIComponent(f.name)}">Download</a>
                        <button type="button" class="btn-mini restore" data-file="${escapeHtml(f.name)}">Restore</button>
                        <button type="button" class="btn-mini delete" data-file="${escapeHtml(f.name)}">Delete</button>
                    </div>
                </td>
            `;
            filesBody.appendChild(row);
        });

        document.querySelectorAll('.btn-mini.restore').forEach(btn => {
            btn.addEventListener('click', () => restoreFromFile(btn.dataset.file));
        });
        document.querySelectorAll('.btn-mini.delete').forEach(btn => {
            btn.addEventListener('click', () => deleteFile(btn.dataset.file));
        });
    } catch (err) {
        filesLoading.style.display = 'none';
    }
}

loadHistory();
loadFiles();

/* ── Create backup ── */
document.getElementById('btnCreateBackup').addEventListener('click', async () => {
    const result = await Swal.fire({
        title: 'Create a backup now?',
        text: 'This will export the full database to a .sql file on the server.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, back up now',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    const btn = document.getElementById('btnCreateBackup');
    btn.disabled = true;
    btn.textContent = 'Backing up…';

    Swal.fire({ title: 'Creating backup…', text: 'This may take a moment.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        const response = await fetch('<?= base_url("api/admin_backup_create.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) csrfToken = data.csrf_token;

        Swal.close();
        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Backup created (${data.file_size})`, showConfirmButton: false, timer: 3000, timerProgressBar: true });
            loadHistory();
            loadFiles();
        } else {
            Swal.fire({ icon: 'error', title: 'Backup failed', text: data.message });
        }
    } catch (err) {
        Swal.close();
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    } finally {
        btn.disabled = false;
        btn.textContent = 'Backup Now';
    }
});

/* ── Upload zone ── */
const uploadZone = document.getElementById('uploadZone');
const backupFileInput = document.getElementById('backupFileInput');
uploadZone.addEventListener('click', () => backupFileInput.click());
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    if (e.dataTransfer.files[0]) {
        backupFileInput.files = e.dataTransfer.files;
        showFileChosen();
    }
});
backupFileInput.addEventListener('change', showFileChosen);
function showFileChosen() {
    const chosen = document.getElementById('fileChosen');
    if (backupFileInput.files[0]) {
        chosen.textContent = 'Selected: ' + backupFileInput.files[0].name;
        chosen.style.display = 'block';
    }
}

/* ── Restore from upload ── */
document.getElementById('btnRestoreUpload').addEventListener('click', async () => {
    if (!backupFileInput.files[0]) {
        Swal.fire({ icon: 'warning', title: 'No file selected', text: 'Choose a .sql file first.' });
        return;
    }

    const result = await Swal.fire({
        title: 'Restore database?',
        html: '<strong style="color:#B3261E">This will overwrite ALL current data.</strong><br>This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, restore',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    await runRestore({ backup_file: backupFileInput.files[0] });
});

async function restoreFromFile(filename) {
    const result = await Swal.fire({
        title: 'Restore from this backup?',
        html: `Restoring <strong>${escapeHtml(filename)}</strong> will <span style="color:#B3261E">overwrite ALL current data</span>. This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, restore',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    await runRestore({ restore_filename: filename });
}

async function runRestore(payload) {
    Swal.fire({ title: 'Restoring…', text: 'This may take a moment.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        if (payload.backup_file) formData.append('backup_file', payload.backup_file);
        if (payload.restore_filename) formData.append('restore_filename', payload.restore_filename);

        const response = await fetch('<?= base_url("api/admin_backup_restore.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) csrfToken = data.csrf_token;

        Swal.close();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Restored', text: data.message });
            document.getElementById('fileChosen').style.display = 'none';
            backupFileInput.value = '';
            loadHistory();
        } else {
            Swal.fire({ icon: 'error', title: 'Restore failed', text: data.message });
        }
    } catch (err) {
        Swal.close();
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    }
}

/* ── Delete backup file ── */
async function deleteFile(filename) {
    const result = await Swal.fire({
        title: 'Delete this backup?',
        html: `Remove <strong>${escapeHtml(filename)}</strong> from the server? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('filename', filename);
        const response = await fetch('<?= base_url("api/admin_backup_delete.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) csrfToken = data.csrf_token;

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Backup deleted', showConfirmButton: false, timer: 2500, timerProgressBar: true });
            loadFiles();
        } else {
            Swal.fire({ icon: 'error', title: 'Could not delete', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    }
}
</script>