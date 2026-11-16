<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

$page_title = "Admin Dashboard";
$page_subtitle = "Welcome back, " . $_SESSION['full_name'] . ".";
$role_label = "Administrator";
$nav_links = [
    ['label' => 'Dashboard',   'url' => 'dashboard.php', 'active' => true],
    ['label' => 'Users',       'url' => 'users.php'],
    ['label' => 'Audit Logs',  'url' => 'audit_logs.php'],
    ['label' => 'Reports',     'url' => 'reports.php'],
    ['label' => 'Backup & Recovery', 'url' => 'backup.php'],
];

ob_start();
?>
<div class="section-block">
    <div class="section-block-title">System Overview</div>
    <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTotalUsers">—</div>
            <div class="kpi-label">Total User Accounts</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTotalDoctors">—</div>
            <div class="kpi-label">Active Doctors</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTotalStaff">—</div>
            <div class="kpi-label">Active Staff</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #B45309; --kpi-icon-bg: var(--peach-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiInactive">—</div>
            <div class="kpi-label">Inactive Accounts</div>
        </div>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Quick Actions</div>
    <div class="quick-actions-grid">
        <a href="users.php" class="quick-action-card">
            <div class="quick-action-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            <div class="quick-action-label">Create User</div>
        </a>
        <a href="users.php" class="quick-action-card">
            <div class="quick-action-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
            <div class="quick-action-label">Manage Users</div>
        </a>
        <a href="audit_logs.php" class="quick-action-card">
            <div class="quick-action-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
            <div class="quick-action-label">View Audit Logs</div>
        </a>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Recent System Activity (Today: <span id="todayEventsCount">—</span> events)</div>
    <div class="schedule-list-card" id="activityCard">
        <div class="schedule-empty" id="activityLoading">Loading recent activity…</div>
    </div>
</div>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

async function loadDashboardStats() {
    const activityCard = document.getElementById('activityCard');

    try {
        const response = await fetch('<?= base_url("api/dashboard_stats.php") ?>');
        const data = await response.json();
        if (!data.success) return;

        const s = data.stats;
        const setKpi = (id, value) => {
            const el = document.getElementById(id);
            el.textContent = value;
            el.classList.remove('kpi-skeleton');
        };

        setKpi('kpiTotalUsers', s.total_users);
        setKpi('kpiTotalDoctors', s.total_doctors);
        setKpi('kpiTotalStaff', s.total_staff);
        setKpi('kpiInactive', s.inactive_accounts);
        document.getElementById('todayEventsCount').textContent = s.today_events;

        activityCard.innerHTML = '';
        if (!data.recent_activity || data.recent_activity.length === 0) {
            activityCard.innerHTML = '<div class="schedule-empty">No recent activity.</div>';
            return;
        }

        data.recent_activity.forEach(log => {
            const row = document.createElement('div');
            row.className = 'schedule-row';
            row.innerHTML = `
                <span class="schedule-time" style="width:auto;min-width:60px;">${log.created_at.split(' ')[1] || log.created_at}</span>
                <span class="schedule-patient">
                    <span class="schedule-patient-name">${escapeHtml(log.user_name || 'System')} — ${escapeHtml(log.action)}</span>
                    <span class="schedule-patient-reason">${escapeHtml(log.module)}${log.description ? ' · ' + escapeHtml(log.description) : ''}</span>
                </span>
            `;
            activityCard.appendChild(row);
        });
    } catch (err) {
        activityCard.innerHTML = '<div class="schedule-empty">Could not load recent activity.</div>';
    }
}
loadDashboardStats();
</script>