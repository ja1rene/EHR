<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('doctor');

$page_title = "Doctor Dashboard";
$page_subtitle = "Welcome back, " . $_SESSION['full_name'] . ".";
$role_label = "Doctor";
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
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTodayTotal">—</div>
            <div class="kpi-label">Appointments Today</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTodayCompleted">—</div>
            <div class="kpi-label">Completed Today</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: #B45309; --kpi-icon-bg: var(--peach-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTodayScheduled">—</div>
            <div class="kpi-label">Remaining Today</div>
        </div>

        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg>
            </div>
            <div class="kpi-number kpi-skeleton" id="kpiTotalSeen">—</div>
            <div class="kpi-label">Total Patients Seen</div>
        </div>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Today's Schedule</div>
    <div class="schedule-list-card" id="scheduleCard">
        <div class="schedule-empty" id="scheduleLoading">Loading today's schedule…</div>
    </div>
</div>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
function formatTime(t) {
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    const suffix = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 === 0 ? 12 : hour % 12;
    return `${hour12}:${m} ${suffix}`;
}
function formatStatus(s) {
    const map = { scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled', no_show: 'No-show' };
    return map[s] || s;
}
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

async function loadDashboardStats() {
    const scheduleCard = document.getElementById('scheduleCard');

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

        setKpi('kpiTodayTotal', s.today_total);
        setKpi('kpiTodayCompleted', s.today_completed);
        setKpi('kpiTodayScheduled', s.today_scheduled);
        setKpi('kpiTotalSeen', s.total_patients_seen);

        scheduleCard.innerHTML = '';
        if (!data.today_schedule || data.today_schedule.length === 0) {
            scheduleCard.innerHTML = '<div class="schedule-empty">No appointments scheduled for today.</div>';
            return;
        }

        data.today_schedule.forEach(a => {
            const row = document.createElement('a');
            row.className = 'schedule-row';
            row.href = '<?= base_url("doctor/patient_view.php") ?>?id=' + a.patient_id;
            row.style.textDecoration = 'none';
            row.style.color = 'inherit';
            row.innerHTML = `
                <span class="schedule-time">${formatTime(a.appointment_time)}</span>
                <span class="schedule-patient">
                    <span class="schedule-patient-name">${escapeHtml(a.last_name)}, ${escapeHtml(a.first_name)}</span>
                    <span class="schedule-patient-reason">${escapeHtml(a.reason || a.patient_code)}</span>
                </span>
                <span class="schedule-status schedule-status-${a.status}">${formatStatus(a.status)}</span>
            `;
            scheduleCard.appendChild(row);
        });
    } catch (err) {
        scheduleCard.innerHTML = '<div class="schedule-empty">Could not load today&#39;s schedule.</div>';
    }
}
loadDashboardStats();
</script>