<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('doctor');

$page_title = "Patients";
$role_label = "Doctor";
$nav_links = [
    ['label' => 'Dashboard', 'url' => 'dashboard.php'],
    ['label' => 'Patients',  'url' => 'patients.php', 'active' => true],
    ['label' => 'Appointments', 'url' => 'appointments.php'],
    ['label' => 'Reports',      'url' => 'reports.php'],

];

ob_start();
?>
<div class="card">
    <div class="patients-toolbar">
        <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Search by name or patient ID…">
        </div>
    </div>

    <table class="patients-table" id="patientsTable">
        <thead>
            <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>Age / Sex</th>
                <th>Contact</th>
                <th>Blood Type</th>
                <th>Registered</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="patientsBody"></tbody>
    </table>
    <div id="emptyState" class="empty-state" style="display:none;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg>
        <p>No patients found.</p>
    </div>
    <div id="loadingState" class="loading-state">Loading patients…</div>
</div>

<style>
.patients-toolbar { display: flex; gap: 12px; margin-bottom: 20px; }
.search-wrap { position: relative; flex: 1; min-width: 220px; }
.search-wrap svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
.search-wrap input {
    width: 100%; padding: 10px 14px 10px 38px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-size: 0.87rem; font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg); outline: none;
}
.search-wrap input:focus { border-color: var(--indigo); background: var(--surface); }

.patients-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.patients-table th {
    text-align: left; padding: 10px 12px; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);
    border-bottom: 1.5px solid var(--border);
}
.patients-table td { padding: 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
.patients-table tr:last-child td { border-bottom: none; }
.clickable-row { cursor: pointer; transition: background 0.1s; }
.clickable-row:hover { background: var(--bg); }
.row-action { color: var(--text-muted); width: 30px; text-align: right; }
.clickable-row:hover .row-action { color: var(--indigo); }
.patient-name { font-weight: 600; color: var(--text-primary); }
.patient-code { font-size: 0.75rem; color: var(--text-muted); font-family: monospace; }

.empty-state, .loading-state { text-align: center; padding: 48px 20px; color: var(--text-muted); font-size: 0.85rem; display: none; }
.empty-state svg { margin: 0 auto 12px; opacity: 0.5; display: block; }
.loading-state { display: block; }

@media (max-width: 640px) {
    .patients-table { display: block; overflow-x: auto; white-space: nowrap; }
}
</style>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
const tableBody    = document.getElementById('patientsBody');
const emptyState   = document.getElementById('emptyState');
const loadingState = document.getElementById('loadingState');
const searchInput  = document.getElementById('searchInput');
let searchDebounce = null;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}
function calcAge(dob) {
    const birth = new Date(dob);
    const diff = new Date(Date.now() - birth.getTime());
    return Math.abs(diff.getUTCFullYear() - 1970);
}

async function loadPatients(query = '') {
    loadingState.style.display = 'block';
    emptyState.style.display = 'none';
    tableBody.innerHTML = '';

    try {
        const response = await fetch('<?= base_url("api/patients_list.php") ?>?search=' + encodeURIComponent(query));
        const data = await response.json();
        loadingState.style.display = 'none';

        if (!data.success || data.patients.length === 0) {
            emptyState.style.display = 'block';
            return;
        }

        data.patients.forEach(p => {
            const row = document.createElement('tr');
            row.classList.add('clickable-row');
            row.addEventListener('click', () => {
                window.location.href = '<?= base_url("doctor/patient_view.php") ?>?id=' + p.patient_id;
            });
            row.innerHTML = `
                <td><span class="patient-code">${escapeHtml(p.patient_code)}</span></td>
                <td><span class="patient-name">${escapeHtml(p.last_name)}, ${escapeHtml(p.first_name)}</span></td>
                <td>${calcAge(p.date_of_birth)} yrs / ${p.sex === 'male' ? 'M' : 'F'}</td>
                <td>${escapeHtml(p.contact_number || '—')}</td>
                <td>${escapeHtml(p.blood_type)}</td>
                <td>${escapeHtml(p.created_at)}</td>
                <td class="row-action">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </td>
            `;
            tableBody.appendChild(row);
        });
    } catch (err) {
        loadingState.style.display = 'none';
        emptyState.style.display = 'block';
    }
}

searchInput.addEventListener('input', function () {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => loadPatients(searchInput.value.trim()), 350);
});

loadPatients();
</script>