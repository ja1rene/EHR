<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('doctor');

$page_title = "My Reports";
$report_export = true;  
$nav_links = [
    ['label' => 'Dashboard', 'url' => 'dashboard.php'],
    ['label' => 'Patients',  'url' => 'patients.php'],
    ['label' => 'Appointments', 'url' => 'appointments.php'],
    ['label' => 'Reports', 'url' => 'reports.php', 'active' => true],
];

ob_start();
?>
<div class="card">
    <div class="report-toolbar">
        <div class="field"><label>From</label><input type="date" id="dateFrom"></div>
        <div class="field"><label>To</label><input type="date" id="dateTo"></div>
    </div>

    <div class="kpi-grid" id="kpiGrid">
        <div class="kpi-card" style="--kpi-accent: var(--indigo); --kpi-icon-bg: var(--indigo-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiTotalConsults">—</div>
            <div class="kpi-label">Consultations</div>
        </div>
        <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiDistinctPatients">—</div>
            <div class="kpi-label">Distinct Patients Seen</div>
        </div>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Appointment Outcomes</div>
    <div class="card" id="statusChartCard"></div>
</div>

<div class="section-block">
    <div class="section-block-title">Most Common Diagnoses</div>
    <div class="report-note">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Grouped by exact diagnosis text, not a standardized code list — differently worded entries for the same condition are counted separately.
    </div>
    <div class="card" id="diagnosisCard">
        <div class="loading-state" id="diagnosisLoading">Loading…</div>
    </div>
</div>

<style>
.loading-state, .empty-note { text-align: center; padding: 32px 20px; color: var(--text-muted); font-size: 0.85rem; }
</style>
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

const dateFrom = document.getElementById('dateFrom');
const dateTo   = document.getElementById('dateTo');
const today = new Date();
dateTo.value = today.toISOString().slice(0, 10);
dateFrom.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);

async function loadReport() {
    const statusCard = document.getElementById('statusChartCard');
    const diagnosisCard = document.getElementById('diagnosisCard');

    try {
        const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value });
        const response = await fetch('<?= base_url("api/reports_doctor.php") ?>?' + params.toString());
        const data = await response.json();
        if (!data.success) return;

        const setKpi = (id, value) => { const el = document.getElementById(id); el.textContent = value; el.classList.remove('kpi-skeleton'); };
        setKpi('kpiTotalConsults', data.total_consultations);
        setKpi('kpiDistinctPatients', data.distinct_patients);

        const maxCount = Math.max(...Object.values(data.status_breakdown), 1);
        statusCard.innerHTML = Object.entries(data.status_breakdown).map(([status, cnt]) => `
            <div class="bar-chart-row">
                <span class="bar-chart-label">${formatStatus(status)}</span>
                <div class="bar-chart-track"><div class="bar-chart-fill" style="width:${(cnt/maxCount*100)}%;background:${statusColors[status]};"></div></div>
                <span class="bar-chart-value">${cnt}</span>
            </div>
        `).join('');

        if (data.diagnosis_frequency.length === 0) {
            diagnosisCard.innerHTML = '<div class="empty-note">No diagnoses recorded in this range.</div>';
        } else {
            const maxDiag = Math.max(...data.diagnosis_frequency.map(d => d.cnt), 1);
            diagnosisCard.innerHTML = data.diagnosis_frequency.map(d => `
                <div class="bar-chart-row">
                    <span class="bar-chart-label" title="${escapeHtml(d.diagnosis)}">${escapeHtml(d.diagnosis.length > 22 ? d.diagnosis.slice(0, 22) + '…' : d.diagnosis)}</span>
                    <div class="bar-chart-track"><div class="bar-chart-fill" style="width:${(d.cnt/maxDiag*100)}%;background:#4F3FBF;"></div></div>
                    <span class="bar-chart-value">${d.cnt}</span>
                </div>
            `).join('');
        }
    } catch (err) {
        // fail quietly — reports are non-critical
    }
}

dateFrom.addEventListener('change', loadReport);
dateTo.addEventListener('change', loadReport);
loadReport();
</script>