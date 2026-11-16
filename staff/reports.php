<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff');

$page_title = "Reports";
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
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiTotalAppts">—</div>
            <div class="kpi-label">Total Appointments</div>
        </div>
        <div class="kpi-card" style="--kpi-accent: var(--teal); --kpi-icon-bg: var(--teal-tint);">
            <div class="kpi-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg></div>
            <div class="kpi-number kpi-skeleton" id="kpiNewPatients">—</div>
            <div class="kpi-label">New Patients Registered</div>
        </div>
    </div>
</div>

<div class="section-block">
    <div class="section-block-title">Appointment Status Breakdown</div>
    <div class="card" id="statusChartCard"></div>
</div>

<div class="section-block">
    <div class="section-block-title">Newly Registered Patients</div>
    <div class="card">
        <table class="data-table" id="patientsTable">
            <thead><tr><th>Patient ID</th><th>Name</th><th>Registered</th></tr></thead>
            <tbody id="patientsBody"></tbody>
        </table>
        <div id="emptyState" class="empty-state" style="display:none;"><p>No new patients registered in this range.</p></div>
        <div id="loadingState" class="loading-state">Loading report…</div>
    </div>
</div>

<style>
.data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.data-table th { text-align: left; padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border-bottom: 1.5px solid var(--border); }
.data-table td { padding: 11px 12px; border-bottom: 1px solid var(--border); }
.data-table tr:last-child td { border-bottom: none; }
.empty-state, .loading-state { text-align: center; padding: 40px 20px; color: var(--text-muted); font-size: 0.85rem; display: none; }
.loading-state { display: block; }
@media (max-width: 640px) { .data-table { display: block; overflow-x: auto; white-space: nowrap; } }
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

// Default to this month.
const today = new Date();
dateTo.value = today.toISOString().slice(0, 10);
dateFrom.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);

async function loadReport() {
    const statusCard = document.getElementById('statusChartCard');
    const patientsBody = document.getElementById('patientsBody');
    const emptyState = document.getElementById('emptyState');
    const loadingState = document.getElementById('loadingState');

    loadingState.style.display = 'block';
    emptyState.style.display = 'none';
    patientsBody.innerHTML = '';

    try {
        const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value });
        const response = await fetch('<?= base_url("api/reports_staff.php") ?>?' + params.toString());
        const data = await response.json();
        loadingState.style.display = 'none';

        if (!data.success) return;

        const setKpi = (id, value) => { const el = document.getElementById(id); el.textContent = value; el.classList.remove('kpi-skeleton'); };
        setKpi('kpiTotalAppts', data.total_appointments);
        setKpi('kpiNewPatients', data.new_patients_count);

        const maxCount = Math.max(...Object.values(data.status_breakdown), 1);
        statusCard.innerHTML = Object.entries(data.status_breakdown).map(([status, cnt]) => `
            <div class="bar-chart-row">
                <span class="bar-chart-label">${formatStatus(status)}</span>
                <div class="bar-chart-track"><div class="bar-chart-fill" style="width:${(cnt/maxCount*100)}%;background:${statusColors[status]};"></div></div>
                <span class="bar-chart-value">${cnt}</span>
            </div>
        `).join('');

        if (data.new_patients.length === 0) {
            emptyState.style.display = 'block';
        } else {
            data.new_patients.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${escapeHtml(p.patient_code)}</td><td>${escapeHtml(p.last_name)}, ${escapeHtml(p.first_name)}</td><td>${p.created_at}</td>`;
                patientsBody.appendChild(row);
            });
        }
    } catch (err) {
        loadingState.style.display = 'none';
    }
}

dateFrom.addEventListener('change', loadReport);
dateTo.addEventListener('change', loadReport);
loadReport();
</script>