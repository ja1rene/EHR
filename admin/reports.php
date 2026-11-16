<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

$page_title = "System Reports";
$report_export = true;
$role_label = "Administrator";

$nav_links = [
    ['label' => 'Dashboard',  'url' => 'dashboard.php'],
    ['label' => 'Users',      'url' => 'users.php'],
    ['label' => 'Audit Logs', 'url' => 'audit_logs.php'],
    ['label' => 'Reports',    'url' => 'reports.php', 'active' => true],
    ['label' => 'Backup & Recovery', 'url' => 'backup.php'],
];

ob_start();
?>
<div class="report-note">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
    System-wide totals only — no individual patient records or clinical details are shown here, consistent with the Administrator role's access boundaries.
</div>

<div class="section-block">
    <div class="section-block-title">System Totals</div>
    <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiPatients">—</div>
            <div class="kpi-label">Total Patients</div>
        </div>
        <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiAppointments">—</div>
            <div class="kpi-label">Total Appointments</div>
        </div>
        <div class="kpi-card" style="--kpi-accent: #B45309; --kpi-icon-bg: var(--peach-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiConsultations">—</div>
            <div class="kpi-label">Total Consultations</div>
        </div>
        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiPrescriptions">—</div>
            <div class="kpi-label">Total Prescriptions</div>
        </div>
        <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6M15 2v6M9 14h6"/><circle cx="12" cy="14" r="8"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiLabResults">—</div>
            <div class="kpi-label">Total Lab Results</div>
        </div>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Appointment Status (All-Time)</div>
    <div class="card" id="statusChartCard"></div>
</div>

<div class="section-block">
    <div class="section-block-title">Consultations per Doctor</div>
    <div class="card" id="doctorChartCard"></div>
</div>

<div class="section-block">
    <div class="section-block-title">Audit Activity — Last 7 Days</div>
    <div class="card" id="auditChartCard"></div>
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
function formatStatus(s) {
    const map = { scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled', no_show: 'No-show' };
    return map[s] || s;
}
const statusColors = { scheduled: '#4F3FBF', completed: '#0F3D3E', cancelled: '#B3261E', no_show: '#B45309' };

async function loadReport() {
    try {
        const response = await fetch('<?= base_url("api/reports_admin.php") ?>');
        const data = await response.json();
        if (!data.success) return;

        const setKpi = (id, value) => { const el = document.getElementById(id); el.textContent = value; el.classList.remove('kpi-skeleton'); };
        setKpi('kpiPatients', data.total_patients);
        setKpi('kpiAppointments', data.total_appointments);
        setKpi('kpiConsultations', data.total_consultations);
        setKpi('kpiPrescriptions', data.total_prescriptions);
        setKpi('kpiLabResults', data.total_lab_results);

        const maxStatus = Math.max(...Object.values(data.status_breakdown), 1);
        document.getElementById('statusChartCard').innerHTML = Object.entries(data.status_breakdown).map(([status, cnt]) => `
            <div class="bar-chart-row">
                <span class="bar-chart-label">${formatStatus(status)}</span>
                <div class="bar-chart-track"><div class="bar-chart-fill" style="width:${(cnt/maxStatus*100)}%;background:${statusColors[status]};"></div></div>
                <span class="bar-chart-value">${cnt}</span>
            </div>
        `).join('');

        const doctorCard = document.getElementById('doctorChartCard');
        if (!data.consults_per_doctor || data.consults_per_doctor.length === 0) {
            doctorCard.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:0.85rem;padding:24px;">No doctors on file yet.</div>';
        } else {
            const maxDoc = Math.max(...data.consults_per_doctor.map(d => d.cnt), 1);
            doctorCard.innerHTML = data.consults_per_doctor.map(d => `
                <div class="bar-chart-row">
                    <span class="bar-chart-label">${escapeHtml(d.full_name)}</span>
                    <div class="bar-chart-track"><div class="bar-chart-fill" style="width:${(d.cnt/maxDoc*100)}%;background:#0F3D3E;"></div></div>
                    <span class="bar-chart-value">${d.cnt}</span>
                </div>
            `).join('');
        }

        const auditCard = document.getElementById('auditChartCard');
        if (!data.audit_trend || data.audit_trend.length === 0) {
            auditCard.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:0.85rem;padding:24px;">No activity in the last 7 days.</div>';
        } else {
            const maxAudit = Math.max(...data.audit_trend.map(d => d.cnt), 1);
            auditCard.innerHTML = data.audit_trend.map(d => `
                <div class="bar-chart-row">
                    <span class="bar-chart-label">${d.day}</span>
                    <div class="bar-chart-track"><div class="bar-chart-fill" style="width:${(d.cnt/maxAudit*100)}%;background:#4F3FBF;"></div></div>
                    <span class="bar-chart-value">${d.cnt}</span>
                </div>
            `).join('');
        }
    } catch (err) {
        // fail quietly
    }
}
loadReport();
</script>