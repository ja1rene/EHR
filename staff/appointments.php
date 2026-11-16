<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff');

generate_csrf_token();

$page_title = "Appointments";
$role_label = "Staff / Nurse";
$nav_links = [
    ['label' => 'Dashboard',     'url' => 'dashboard.php'],
    ['label' => 'Patients',      'url' => 'patients.php'],
    ['label' => 'Appointments',  'url' => 'appointments.php', 'active' => true],
    ['label' => 'Reports',      'url' => 'reports.php'],
];

ob_start();
?>
<div class="card">
    <div class="toolbar">
        <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Search by patient name or ID…">
        </div>
        <input type="date" id="dateFilter" class="date-filter">
        <select id="statusFilter" class="status-filter">
            <option value="">All statuses</option>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
            <option value="no_show">No-show</option>
        </select>
        <button type="button" class="btn-primary" id="btnAdd">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Schedule Appointment
        </button>
    </div>

    <table class="data-table" id="apptTable">
        <thead>
            <tr>
                <th>Date / Time</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Reason</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="apptBody"></tbody>
    </table>
    <div id="emptyState" class="empty-state" style="display:none;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <p>No appointments found.</p>
    </div>
    <div id="loadingState" class="loading-state">Loading appointments…</div>
</div>

<!-- ═══ Schedule Modal ═══ -->
<div class="modal-overlay" id="addModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseAdd" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>Schedule Appointment</h2>
        <p>Fields marked * are required.</p>
    </div>

    <div class="alert alert-error" id="addAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="addAlertText"></span>
    </div>

    <form id="addForm" novalidate>
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="field">
            <label>Patient *</label>
            <input type="text" id="patientSearch" placeholder="Type to search patient…" autocomplete="off">
            <input type="hidden" name="patient_id" id="patientIdField">
            <div id="patientSuggestions" class="suggestions"></div>
            <div id="selectedPatient" class="selected-chip" style="display:none;"></div>
        </div>

        <div class="field">
            <label>Doctor *</label>
            <select name="doctor_id" id="doctorSelect" required>
                <option value="">Loading doctors…</option>
            </select>
        </div>

        <div class="form-row">
            <div class="field"><label>Date *</label><input type="date" name="appointment_date" id="apptDate" required></div>
            <div class="field"><label>Time *</label><input type="time" name="appointment_time" id="apptTime" required></div>
                <div class="field">
                    <label>Duration</label>
                    <select name="duration_minutes">
                        <option value="15">15 min</option>
                        <option value="30" selected>30 min</option>
                        <option value="45">45 min</option>
                        <option value="60">1 hour</option>
                        <option value="90">1.5 hours</option>
                        <option value="120">2 hours</option>
                    </select>
                </div>
        </div>

        <div class="field">
            <label>Reason for visit</label>
            <textarea name="reason" rows="2" placeholder="e.g. Follow-up checkup"></textarea>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <span id="submitBtnLabel">Schedule Appointment</span>
        </button>
    </form>
</div>
</div>

<!-- ═══ Record Vitals Modal ═══ -->
<div class="modal-overlay" id="vitalsModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseVitals" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>Record Vitals</h2>
        <p id="vitalsPatientLabel">—</p>
    </div>

    <div class="alert alert-error" id="vitalsAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="vitalsAlertText"></span>
    </div>

    <form id="vitalsForm">
        <input type="hidden" name="patient_id" id="vitalsPatientId">
        <input type="hidden" name="appointment_id" id="vitalsApptId">
        <input type="hidden" name="csrf_token" id="vitalsCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="form-row">
            <div class="field"><label>Blood Pressure</label><input type="text" name="blood_pressure" placeholder="120/80"></div>
            <div class="field"><label>Temperature (°C)</label><input type="number" step="0.1" name="temperature_c" placeholder="36.5"></div>
        </div>
        <div class="form-row">
            <div class="field"><label>Pulse (bpm)</label><input type="number" name="pulse_rate" placeholder="72"></div>
            <div class="field"><label>Resp. Rate</label><input type="number" name="respiratory_rate" placeholder="16"></div>
            <div class="field"><label>O₂ Sat (%)</label><input type="number" name="oxygen_saturation" placeholder="98"></div>
        </div>
        <div class="form-row">
            <div class="field"><label>Height (cm)</label><input type="number" step="0.1" name="height_cm" placeholder="165"></div>
            <div class="field"><label>Weight (kg)</label><input type="number" step="0.1" name="weight_kg" placeholder="60"></div>
        </div>

        <button type="submit" class="btn-submit" id="vitalsSubmitBtn">
            <span id="vitalsSubmitLabel">Save Vitals</span>
        </button>
    </form>
</div>
</div>

<style>
.toolbar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-wrap svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
.search-wrap input { width: 100%; padding: 10px 14px 10px 38px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.87rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; }
.search-wrap input:focus { border-color: var(--indigo); background: var(--surface); }
.date-filter, .status-filter {
    padding: 10px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none;
}
.btn-primary {
    display: flex; align-items: center; gap: 7px; background: var(--indigo); color: white; border: none;
    padding: 10px 18px; border-radius: 999px; font-size: 0.86rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; white-space: nowrap; transition: background 0.15s;
}
.btn-primary:hover { background: var(--indigo-dark); }

.data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.data-table th { text-align: left; padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border-bottom: 1.5px solid var(--border); }
.data-table td { padding: 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.patient-name { font-weight: 600; }
.patient-code { font-size: 0.72rem; color: var(--text-muted); font-family: monospace; display: block; }

.status-badge { display: inline-flex; padding: 3px 11px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; }
.status-scheduled { background: var(--indigo-tint); color: var(--indigo-dark); }
.status-completed { background: var(--teal-tint); color: var(--teal); }
.status-cancelled { background: #FDECEA; color: #B3261E; }
.status-no_show   { background: var(--peach-tint); color: #B45309; }

.row-actions { display: flex; gap: 6px; justify-content: flex-end; }
.action-btn {
    width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border); background: var(--surface);
    color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.action-btn:hover.cancel { background: #FDECEA; border-color: #f2c6c3; color: #B3261E; }
.action-btn:hover.vitals { background: var(--teal-tint); border-color: #cfe4e2; color: var(--teal); }

.empty-state, .loading-state { text-align: center; padding: 48px 20px; color: var(--text-muted); font-size: 0.85rem; display: none; }
.empty-state svg { margin: 0 auto 12px; opacity: 0.5; display: block; }
.loading-state { display: block; }

.modal-overlay {
    position: fixed; inset: 0; z-index: 200; background: rgba(27,23,48,0.35); backdrop-filter: blur(6px);
    display: none; align-items: flex-start; justify-content: center; padding: 40px 20px; opacity: 0;
    transition: opacity 0.2s ease; overflow-y: auto;
}
.modal-overlay.open { display: flex; }
.modal-overlay.show { opacity: 1; }
.modal-card {
    position: relative; width: 100%; max-width: 480px; background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 34px 36px 30px; box-shadow: 0 30px 70px -20px rgba(27,23,48,0.35);
    transform: translateY(14px) scale(0.98); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-overlay.show .modal-card { transform: translateY(0) scale(1); opacity: 1; }
.modal-close { position: absolute; top: 16px; right: 16px; width: 30px; height: 30px; border-radius: 50%; border: none; background: var(--bg); color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; }
.modal-close:hover { background: var(--indigo-tint); color: var(--indigo-dark); }
.modal-heading { margin-bottom: 20px; }
.modal-heading h2 { font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 4px; }
.modal-heading p { font-size: 0.82rem; color: var(--text-secondary); }

.alert { display: none; align-items: flex-start; gap: 9px; padding: 11px 13px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 0.82rem; font-weight: 500; border: 1px solid transparent; }
.alert.show { display: flex; }
.alert-error { background: #FDECEA; color: #B3261E; border-color: #f2c6c3; }

.form-row { display: flex; gap: 14px; }
.field { position: relative; margin-bottom: 16px; flex: 1; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 6px; }
.field input, .field select, .field textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.86rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; resize: vertical;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color: var(--indigo); background: var(--surface); }

.suggestions {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 10;
    background: var(--surface); border: 1.5px solid var(--border); border-radius: 8px;
    margin-top: 4px; max-height: 180px; overflow-y: auto; display: none; box-shadow: 0 8px 20px rgba(27,23,48,0.1);
}
.suggestions.show { display: block; }
.suggestion-item { padding: 9px 12px; font-size: 0.84rem; cursor: pointer; }
.suggestion-item:hover { background: var(--indigo-tint); }
.selected-chip {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    background: var(--indigo-tint); color: var(--indigo-dark); border-radius: 8px; padding: 8px 12px;
    font-size: 0.85rem; font-weight: 600; margin-top: 4px;
}
.selected-chip button { background: none; border: none; color: var(--indigo-dark); cursor: pointer; font-size: 1rem; line-height: 1; }

.btn-submit {
    width: 100%; padding: 12px; background: var(--indigo); color: white; border: none; border-radius: 999px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; margin-top: 6px;
}
.btn-submit:hover { background: var(--indigo-dark); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .data-table { display: block; overflow-x: auto; white-space: nowrap; }
    .modal-card { padding: 26px 20px 22px; }
    .form-row { flex-direction: column; gap: 0; }
}
</style>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
const apptBody     = document.getElementById('apptBody');
const emptyState   = document.getElementById('emptyState');
const loadingState = document.getElementById('loadingState');
const searchInput  = document.getElementById('searchInput');
const dateFilter   = document.getElementById('dateFilter');
const statusFilter = document.getElementById('statusFilter');
let searchDebounce = null;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}
function formatStatus(s) {
    const map = { scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled', no_show: 'No-show' };
    return map[s] || s;
}
function formatTime(t) {
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    const suffix = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 === 0 ? 12 : hour % 12;
    return `${hour12}:${m} ${suffix}`;
}

async function loadAppointments() {
    loadingState.style.display = 'block';
    emptyState.style.display = 'none';
    apptBody.innerHTML = '';

    const params = new URLSearchParams();
    if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
    if (dateFilter.value) params.set('date', dateFilter.value);
    if (statusFilter.value) params.set('status', statusFilter.value);

    try {
        const response = await fetch('<?= base_url("api/appointments_list.php") ?>?' + params.toString());
        const data = await response.json();
        loadingState.style.display = 'none';

        if (!data.success || data.appointments.length === 0) {
            emptyState.style.display = 'block';
            return;
        }

        data.appointments.forEach(a => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${a.appointment_date}<br><span style="color:var(--text-muted);font-size:0.78rem;">${formatTime(a.appointment_time)}</span></td>
                <td><span class="patient-name">${escapeHtml(a.patient_last)}, ${escapeHtml(a.patient_first)}</span><span class="patient-code">${escapeHtml(a.patient_code)}</span></td>
                <td>${escapeHtml(a.doctor_name)}</td>
                <td>${escapeHtml(a.reason || '—')}</td>
                <td><span class="status-badge status-${a.status}">${formatStatus(a.status)}</span></td>
                <td>
                    <div class="row-actions">
                        ${a.status === 'scheduled' ? `<button type="button" class="action-btn vitals" data-id="${a.appointment_id}" data-patient="${a.patient_id}" data-patient-label="${escapeHtml(a.patient_last)}, ${escapeHtml(a.patient_first)}" title="Record vitals">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </button>` : ''}
                        ${a.status === 'scheduled' ? `<button type="button" class="action-btn cancel" data-id="${a.appointment_id}" title="Cancel appointment">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>` : ''}
                    </div>
                </td>
            `;
            apptBody.appendChild(row);
        });

        document.querySelectorAll('.action-btn.cancel').forEach(btn => {
            btn.addEventListener('click', () => cancelAppointment(btn.dataset.id));
        });
        document.querySelectorAll('.action-btn.vitals').forEach(btn => {
            btn.addEventListener('click', () => openVitalsModal(btn.dataset.id, btn.dataset.patient, btn.dataset.patientLabel));
        });
    } catch (err) {
        loadingState.style.display = 'none';
        emptyState.style.display = 'block';
    }
}

async function cancelAppointment(id) {
    const result = await Swal.fire({
        title: 'Cancel this appointment?',
        text: 'The patient and doctor should be notified separately.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'Keep appointment',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('appointment_id', id);
    formData.append('status', 'cancelled');
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    try {
        const response = await fetch('<?= base_url("api/appointments_update_status.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('csrfToken').value = data.csrf_token;

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Appointment cancelled', showConfirmButton: false, timer: 2500, timerProgressBar: true });
            loadAppointments();
        } else {
            Swal.fire({ icon: 'error', title: 'Could not cancel', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    }
}

searchInput.addEventListener('input', () => { clearTimeout(searchDebounce); searchDebounce = setTimeout(loadAppointments, 350); });
dateFilter.addEventListener('change', loadAppointments);
statusFilter.addEventListener('change', loadAppointments);
loadAppointments();

/* ── Schedule modal ── */
const addModal = document.getElementById('addModal');
function openAddModal() { addModal.classList.add('open'); requestAnimationFrame(() => addModal.classList.add('show')); document.body.style.overflow = 'hidden'; loadDoctors(); }
function closeAddModal() { addModal.classList.remove('show'); document.body.style.overflow = ''; setTimeout(() => addModal.classList.remove('open'), 200); }
document.getElementById('btnAdd').addEventListener('click', openAddModal);
document.getElementById('btnCloseAdd').addEventListener('click', closeAddModal);
addModal.addEventListener('click', (e) => { if (e.target === addModal) closeAddModal(); });

async function loadDoctors() {
    const select = document.getElementById('doctorSelect');
    try {
        const response = await fetch('<?= base_url("api/doctors_list.php") ?>');
        const data = await response.json();
        select.innerHTML = '<option value="">Select a doctor…</option>';
        (data.doctors || []).forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.user_id;
            opt.textContent = d.full_name;
            select.appendChild(opt);
        });
    } catch (err) {
        select.innerHTML = '<option value="">Could not load doctors</option>';
    }
}

/* ── Patient search-and-select (avoids a giant dropdown of every patient) ── */
const patientSearch      = document.getElementById('patientSearch');
const patientSuggestions = document.getElementById('patientSuggestions');
const patientIdField     = document.getElementById('patientIdField');
const selectedPatientBox = document.getElementById('selectedPatient');
let patientSearchDebounce = null;

patientSearch.addEventListener('input', function () {
    clearTimeout(patientSearchDebounce);
    const q = patientSearch.value.trim();
    if (q.length < 2) { patientSuggestions.classList.remove('show'); return; }

    patientSearchDebounce = setTimeout(async () => {
        try {
            const response = await fetch('<?= base_url("api/patients_list.php") ?>?search=' + encodeURIComponent(q));
            const data = await response.json();
            patientSuggestions.innerHTML = '';
            (data.patients || []).slice(0, 8).forEach(p => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = `${p.last_name}, ${p.first_name} — ${p.patient_code}`;
                item.addEventListener('click', () => {
                    patientIdField.value = p.patient_id;
                    selectedPatientBox.innerHTML = `<span>${p.last_name}, ${p.first_name} (${p.patient_code})</span><button type="button" id="clearPatient">&times;</button>`;
                    selectedPatientBox.style.display = 'flex';
                    patientSearch.style.display = 'none';
                    patientSuggestions.classList.remove('show');
                    document.getElementById('clearPatient').addEventListener('click', () => {
                        patientIdField.value = '';
                        selectedPatientBox.style.display = 'none';
                        patientSearch.style.display = 'block';
                        patientSearch.value = '';
                    });
                });
                patientSuggestions.appendChild(item);
            });
            patientSuggestions.classList.toggle('show', data.patients && data.patients.length > 0);
        } catch (err) {
            patientSuggestions.classList.remove('show');
        }
    }, 300);
});

/* ── Asynchronous schedule submission ── */
const addForm      = document.getElementById('addForm');
const addAlert       = document.getElementById('addAlert');
const addAlertText  = document.getElementById('addAlertText');
const submitBtn      = document.getElementById('submitBtn');
const submitLabel   = document.getElementById('submitBtnLabel');

addForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    addAlert.classList.remove('show');

    if (!patientIdField.value) {
        addAlertText.textContent = 'Please select a patient from the search results.';
        addAlert.classList.add('show');
        return;
    }

    submitBtn.disabled = true;
    submitLabel.innerHTML = '<span class="spinner"></span> Scheduling…';

    try {
        const formData = new FormData(addForm);
        const response = await fetch('<?= base_url("api/appointments_add.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('csrfToken').value = data.csrf_token;

        if (data.success) {
            closeAddModal();
            addForm.reset();
            patientIdField.value = '';
            selectedPatientBox.style.display = 'none';
            patientSearch.style.display = 'block';
            Swal.fire({
                toast: true, position: 'top-end',
                icon: data.notified ? 'success' : 'warning',
                title: data.notified ? 'Appointment scheduled. Doctor notified by email.'
                                    : 'Appointment scheduled, but the doctor could not be emailed.',
                showConfirmButton: false, timer: 3500, timerProgressBar: true
            });
            loadAppointments();
        } else {
            addAlertText.textContent = data.message || 'Could not schedule appointment.';
            addAlert.classList.add('show');
        }
    } catch (err) {
        addAlertText.textContent = 'Could not reach the server. Please try again.';
        addAlert.classList.add('show');
    } finally {
        submitBtn.disabled = false;
        submitLabel.textContent = 'Schedule Appointment';
    }
});

/* ── Record Vitals modal ── */
const vitalsModal = document.getElementById('vitalsModal');
function openVitalsModal(apptId, patientId, patientLabel) {
    document.getElementById('vitalsApptId').value = apptId;
    document.getElementById('vitalsPatientId').value = patientId;
    document.getElementById('vitalsPatientLabel').textContent = 'For: ' + patientLabel;
    document.getElementById('vitalsForm').reset();
    document.getElementById('vitalsApptId').value = apptId; // reset() clears hidden fields too
    document.getElementById('vitalsPatientId').value = patientId;
    document.getElementById('vitalsAlert').classList.remove('show');
    vitalsModal.classList.add('open');
    requestAnimationFrame(() => vitalsModal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}
function closeVitalsModal() {
    vitalsModal.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => vitalsModal.classList.remove('open'), 200);
}
document.getElementById('btnCloseVitals').addEventListener('click', closeVitalsModal);
vitalsModal.addEventListener('click', (e) => { if (e.target === vitalsModal) closeVitalsModal(); });

const vitalsForm       = document.getElementById('vitalsForm');
const vitalsAlert        = document.getElementById('vitalsAlert');
const vitalsAlertText   = document.getElementById('vitalsAlertText');
const vitalsSubmitBtn    = document.getElementById('vitalsSubmitBtn');
const vitalsSubmitLabel = document.getElementById('vitalsSubmitLabel');

vitalsForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    vitalsAlert.classList.remove('show');
    vitalsSubmitBtn.disabled = true;
    vitalsSubmitLabel.innerHTML = '<span class="spinner"></span> Saving…';

    try {
        const formData = new FormData(vitalsForm);
        const response = await fetch('<?= base_url("api/vitals_add.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('vitalsCsrfToken').value = data.csrf_token;

        if (data.success) {
            closeVitalsModal();
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Vitals recorded', showConfirmButton: false, timer: 2500, timerProgressBar: true });
        } else {
            vitalsAlertText.textContent = data.message || 'Could not save vitals.';
            vitalsAlert.classList.add('show');
        }
    } catch (err) {
        vitalsAlertText.textContent = 'Could not reach the server. Please try again.';
        vitalsAlert.classList.add('show');
    } finally {
        vitalsSubmitBtn.disabled = false;
        vitalsSubmitLabel.textContent = 'Save Vitals';
    }
});
</script>