<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

$page_title = "Audit Logs";
$role_label = "Administrator";
$nav_links = [
    ['label' => 'Dashboard',  'url' => 'dashboard.php'],
    ['label' => 'Users',      'url' => 'users.php'],
    ['label' => 'Audit Logs', 'url' => 'audit_logs.php', 'active' => true],
    ['label' => 'Reports',    'url' => 'reports.php'],
    ['label' => 'Backup & Recovery', 'url' => 'backup.php'],
];

ob_start();
?>
<div class="card">
    <div class="toolbar">
        <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Search by user or action…">
        </div>
        <select id="moduleFilter" class="status-filter">
            <option value="">All modules</option>
        </select>
        <input type="date" id="dateFilter" class="status-filter">
    </div>

    <table class="data-table" id="logsTable">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Module</th>
                <th>Description</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody id="logsBody"></tbody>
    </table>
    <div id="emptyState" class="empty-state" style="display:none;">
        <p>No audit log entries found.</p>
    </div>
    <div id="loadingState" class="loading-state">Loading audit logs…</div>
    <div class="log-limit-note" id="limitNote" style="display:none;">Showing the most recent 300 matching entries.</div>
</div>

<style>
.toolbar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-wrap svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
.search-wrap input { width: 100%; padding: 10px 14px 10px 38px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.87rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; }
.search-wrap input:focus { border-color: var(--indigo); background: var(--surface); }
.status-filter { padding: 10px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; }

.data-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.data-table th { text-align: left; padding: 10px 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border-bottom: 1.5px solid var(--border); }
.data-table td { padding: 11px 12px; border-bottom: 1px solid var(--border); vertical-align: top; }
.data-table tr:last-child td { border-bottom: none; }
.log-timestamp { white-space: nowrap; color: var(--text-secondary); font-size: 0.78rem; }
.log-action-tag { display: inline-flex; padding: 2px 9px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: var(--indigo-tint); color: var(--indigo-dark); white-space: nowrap; }
.log-module-tag { font-size: 0.76rem; color: var(--text-muted); font-weight: 600; }
.log-desc { color: var(--text-secondary); font-size: 0.8rem; }
.log-ip { font-family: monospace; font-size: 0.76rem; color: var(--text-muted); }

.empty-state, .loading-state { text-align: center; padding: 48px 20px; color: var(--text-muted); font-size: 0.85rem; display: none; }
.loading-state { display: block; }
.log-limit-note { text-align: center; padding: 12px; font-size: 0.76rem; color: var(--text-muted); display: none; }

@media (max-width: 780px) {
    .data-table { display: block; overflow-x: auto; white-space: nowrap; }
}
</style>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
const logsBody     = document.getElementById('logsBody');
const emptyState   = document.getElementById('emptyState');
const loadingState = document.getElementById('loadingState');
const limitNote    = document.getElementById('limitNote');
const searchInput  = document.getElementById('searchInput');
const moduleFilter = document.getElementById('moduleFilter');
const dateFilter   = document.getElementById('dateFilter');
let searchDebounce = null;
let modulesPopulated = false;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

async function loadLogs() {
    loadingState.style.display = 'block';
    emptyState.style.display = 'none';
    limitNote.style.display = 'none';
    logsBody.innerHTML = '';

    const params = new URLSearchParams();
    if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
    if (moduleFilter.value) params.set('module', moduleFilter.value);
    if (dateFilter.value) params.set('date', dateFilter.value);

    try {
        const response = await fetch('<?= base_url("api/admin_audit_logs.php") ?>?' + params.toString());
        const data = await response.json();
        loadingState.style.display = 'none';

        if (!modulesPopulated && data.modules) {
            data.modules.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = m;
                moduleFilter.appendChild(opt);
            });
            modulesPopulated = true;
        }

        if (!data.success || data.logs.length === 0) {
            emptyState.style.display = 'block';
            return;
        }

        data.logs.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="log-timestamp">${log.created_at}</td>
                <td>${escapeHtml(log.user_name || 'System')}</td>
                <td><span class="log-action-tag">${escapeHtml(log.action)}</span></td>
                <td><span class="log-module-tag">${escapeHtml(log.module)}</span></td>
                <td class="log-desc">${escapeHtml(log.description || '—')}</td>
                <td class="log-ip">${escapeHtml(log.ip_address || '—')}</td>
            `;
            logsBody.appendChild(row);
        });

        if (data.logs.length >= 300) {
            limitNote.style.display = 'block';
        }
    } catch (err) {
        loadingState.style.display = 'none';
        emptyState.style.display = 'block';
    }
}

searchInput.addEventListener('input', () => { clearTimeout(searchDebounce); searchDebounce = setTimeout(loadLogs, 350); });
moduleFilter.addEventListener('change', loadLogs);
dateFilter.addEventListener('change', loadLogs);
loadLogs();
</script>