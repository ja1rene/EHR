<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff');

$page_title = "Staff Dashboard";
$page_subtitle = "Welcome back, " . $_SESSION['full_name'] . ".";
$role_label = "Staff / Nurse";
$nav_links = [
    ['label' => 'Dashboard', 'url' => 'dashboard.php', 'active' => true],
    ['label' => 'Patients',  'url' => 'patients.php'],
    ['label' => 'Appointments', 'url' => 'appointments.php'],
    ['label' => 'Reports',      'url' => 'reports.php'],
];

ob_start();
?>
<div class="section-block">
    <div class="section-block-title">Today at a Glance</div>
    <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTotalPatients">—</div>
            <div class="kpi-label">Total Active Patients</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTodayTotal">—</div>
            <div class="kpi-label">Today's Appointments</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #B45309; --kpi-icon-bg: var(--peach-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTodayScheduled">—</div>
            <div class="kpi-label">Still Scheduled Today</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiNewThisWeek">—</div>
            <div class="kpi-label">New Patients This Week</div>
        </div>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Quick Actions</div>
    <div class="quick-actions-grid">
        <a href="patients.php" class="quick-action-card">
            <div class="quick-action-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
            <div class="quick-action-label">Register Patient</div>
        </a>
        <a href="appointments.php" class="quick-action-card">
            <div class="quick-action-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <div class="quick-action-label">Schedule Appointment</div>
        </a>
        <a href="patients.php" class="quick-action-card">
            <div class="quick-action-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
            <div class="quick-action-label">Search Patients</div>
        </a>
        <a href="appointments.php" class="quick-action-card">
            <div class="quick-action-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
            <div class="quick-action-label">Record Vitals</div>
        </a>
    </div>
</div>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
async function loadDashboardStats() {
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

        setKpi('kpiTotalPatients', s.total_patients);
        setKpi('kpiTodayTotal', s.today_total);
        setKpi('kpiTodayScheduled', s.today_scheduled);
        setKpi('kpiNewThisWeek', s.new_this_week);
    } catch (err) {
        // KPIs are non-critical — fail quietly rather than blocking the page.
    }
}
loadDashboardStats();
</script>