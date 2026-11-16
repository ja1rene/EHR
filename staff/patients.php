<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('staff');

generate_csrf_token();

$page_title = "Patients";
$role_label = "Staff / Nurse";
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

        <div class="view-toggle" role="group" aria-label="Patient list view">
            <button type="button" class="toggle-btn active" data-view="active">Active</button>
            <button type="button" class="toggle-btn" data-view="archived">Archived</button>
        </div>

        <button type="button" class="btn-primary" id="btnAddPatient">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Register Patient
        </button>
    </div>

    <div class="report-note" id="archivedNote" style="display:none;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Archived patients are read-only. Open one to view their history, or restore them to make changes again.
    </div>

    <div id="tableWrap">
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
            <tbody id="patientsBody">
                <!-- filled by JS -->
            </tbody>
        </table>
        <div id="emptyState" class="empty-state" style="display:none;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg>
            <p id="emptyText">No patients found.</p>
        </div>
        <div id="loadingState" class="loading-state">Loading patients…</div>
    </div>
</div>

<!-- ═══ Register Patient Modal ═══ -->
<div class="modal-overlay" id="addModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseAdd" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>Register New Patient</h2>
        <p>Fields marked * are required.</p>
    </div>

    <div class="alert alert-error" id="addAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="addAlertText"></span>
    </div>

    <form id="addForm" novalidate>
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="form-row">
            <div class="field"><label>First name *</label><input type="text" name="first_name" required></div>
            <div class="field"><label>Middle name</label><input type="text" name="middle_name"></div>
            <div class="field"><label>Last name *</label><input type="text" name="last_name" required></div>
        </div>

        <div class="form-row">
            <div class="field"><label>Date of birth *</label><input type="date" name="date_of_birth" required></div>
            <div class="field">
                <label>Sex *</label>
                <select name="sex" required>
                    <option value="">Select…</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="field">
                <label>Blood type</label>
                <select name="blood_type">
                    <option value="unknown">Unknown</option>
                    <option>A+</option><option>A-</option>
                    <option>B+</option><option>B-</option>
                    <option>AB+</option><option>AB-</option>
                    <option>O+</option><option>O-</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="field"><label>Contact number</label><input type="text" name="contact_number" placeholder="09XXXXXXXXX"></div>
            <div class="field wide"><label>Address</label><input type="text" name="address"></div>
        </div>

        <div class="form-row">
            <div class="field"><label>Emergency contact name</label><input type="text" name="emergency_contact_name"></div>
            <div class="field"><label>Emergency contact number</label><input type="text" name="emergency_contact_number"></div>
        </div>

        <div class="field">
            <label>Allergies</label>
            <textarea name="allergies" rows="2" placeholder="e.g. Penicillin — leave blank if none"></textarea>
        </div>
        <div class="field">
            <label>Known medical conditions</label>
            <textarea name="medical_conditions" rows="2" placeholder="e.g. Hypertension — leave blank if none"></textarea>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <span id="submitBtnLabel">Register Patient</span>
        </button>
    </form>
</div>
</div>

<style>
.patients-toolbar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.search-wrap { position: relative; flex: 1; min-width: 220px; }
.search-wrap svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
.search-wrap input {
    width: 100%; padding: 10px 14px 10px 38px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-size: 0.87rem; font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg); outline: none;
}
.search-wrap input:focus { border-color: var(--indigo); background: var(--surface); }

.view-toggle { display: flex; border: 1.5px solid var(--border); border-radius: 999px; padding: 3px; background: var(--bg); }
.toggle-btn {
    border: none; background: none; padding: 7px 16px; border-radius: 999px;
    font-size: 0.82rem; font-weight: 700; color: var(--text-secondary); cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.15s, color 0.15s;
}
.toggle-btn.active { background: var(--indigo-tint); color: var(--indigo-dark); }

.btn-primary {
    display: flex; align-items: center; gap: 7px;
    background: var(--indigo); color: white; border: none;
    padding: 10px 18px; border-radius: 999px;
    font-size: 0.86rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; white-space: nowrap;
    transition: background 0.15s;
}
.btn-primary:hover { background: var(--indigo-dark); }

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
.clickable-row.is-archived .patient-name { color: var(--text-secondary); }
.row-action { width: 1%; white-space: nowrap; text-align: right; }
.row-actions-inline { display: flex; align-items: center; gap: 8px; justify-content: flex-end; color: var(--text-muted); }
.clickable-row:hover .row-actions-inline > svg { color: var(--indigo); }
.row-icon-btn {
    width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--border); background: var(--surface);
    color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.row-icon-btn.archive:hover { background: var(--peach-tint); border-color: #f0dcb8; color: #B45309; }
.btn-mini {
    padding: 6px 13px; border-radius: 999px; border: 1px solid var(--border); background: var(--surface);
    font-size: 0.76rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--teal); border-color: #cfe4e2;
}
.btn-mini:hover { background: var(--teal-tint); }
.patient-name { font-weight: 600; color: var(--text-primary); }
.patient-code { font-size: 0.75rem; color: var(--text-muted); font-family: monospace; }

.empty-state, .loading-state {
    text-align: center; padding: 48px 20px; color: var(--text-muted); font-size: 0.85rem; display: none;
}
.empty-state svg { margin: 0 auto 12px; opacity: 0.5; display: block; }
.loading-state { display: block; }

/* Modal — reuses login.php's overlay pattern */
.modal-overlay {
    position: fixed; inset: 0; z-index: 200;
    background: rgba(27,23,48,0.35); backdrop-filter: blur(6px);
    display: none; align-items: flex-start; justify-content: center;
    padding: 40px 20px; opacity: 0; transition: opacity 0.2s ease; overflow-y: auto;
}
.modal-overlay.open { display: flex; }
.modal-overlay.show { opacity: 1; }
.modal-card {
    position: relative; width: 100%; max-width: 620px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 34px 36px 30px;
    box-shadow: 0 30px 70px -20px rgba(27,23,48,0.35);
    transform: translateY(14px) scale(0.98); opacity: 0;
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-overlay.show .modal-card { transform: translateY(0) scale(1); opacity: 1; }
.modal-close {
    position: absolute; top: 16px; right: 16px; width: 30px; height: 30px; border-radius: 50%;
    border: none; background: var(--bg); color: var(--text-muted);
    display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.modal-close:hover { background: var(--indigo-tint); color: var(--indigo-dark); }
.modal-heading { margin-bottom: 20px; }
.modal-heading h2 { font-family: 'Fraunces', serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 4px; }
.modal-heading p { font-size: 0.82rem; color: var(--text-secondary); }

.alert {
    display: none; align-items: flex-start; gap: 9px;
    padding: 11px 13px; border-radius: var(--radius-sm);
    margin-bottom: 16px; font-size: 0.82rem; font-weight: 500; border: 1px solid transparent;
}
.alert.show { display: flex; }
.alert-error { background: #FDECEA; color: #B3261E; border-color: #f2c6c3; }

.form-row { display: flex; gap: 14px; margin-bottom: 4px; flex-wrap: wrap; }
.field { flex: 1; min-width: 140px; margin-bottom: 14px; }
.field.wide { flex: 2; min-width: 220px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 6px; }
.field input, .field select, .field textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border);
    border-radius: 8px; font-size: 0.86rem; font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg); outline: none; resize: vertical;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color: var(--indigo); background: var(--surface); }
.field input.has-error, .field select.has-error { border-color: #B3261E; }

.btn-submit {
    width: 100%; padding: 12px; background: var(--indigo); color: white; border: none;
    border-radius: 999px; font-size: 0.9rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; margin-top: 6px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-submit:hover { background: var(--indigo-dark); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.spinner {
    width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .patients-table { display: block; overflow-x: auto; white-space: nowrap; }
    .modal-card { padding: 26px 20px 22px; }
    .view-toggle { order: 3; }
}
</style>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/../includes/dashboard_shell.php";
?>

<script>
const tableBody    = document.getElementById('patientsBody');
const emptyState   = document.getElementById('emptyState');
const emptyText    = document.getElementById('emptyText');
const loadingState = document.getElementById('loadingState');
const searchInput  = document.getElementById('searchInput');
const csrfField    = document.getElementById('csrfToken');
let searchDebounce = null;
let currentView    = 'active'; // 'active' or 'archived'

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

const iconPencil  = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
const iconArchive = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>';

/* ── Asynchronous patient list load — page load, search, and view switch ── */
async function loadPatients(query = '') {
    loadingState.style.display = 'block';
    emptyState.style.display = 'none';
    tableBody.innerHTML = '';

    try {
        const params = new URLSearchParams({ search: query, status: currentView });
        const response = await fetch('<?= base_url("api/patients_list.php") ?>?' + params.toString());
        const data = await response.json();

        loadingState.style.display = 'none';

        if (!data.success || data.patients.length === 0) {
            emptyText.textContent = currentView === 'archived' ? 'No archived patients.' : 'No patients found.';
            emptyState.style.display = 'block';
            return;
        }

        data.patients.forEach(p => {
            const row = document.createElement('tr');
            row.classList.add('clickable-row');
            if (currentView === 'archived') row.classList.add('is-archived');
            // Stored via dataset (not innerHTML) so names can't inject markup.
            row.dataset.id = p.patient_id;
            row.dataset.name = `${p.last_name}, ${p.first_name}`;

            const actions = currentView === 'archived'
                ? `<button type="button" class="btn-mini" data-action="restore">Restore</button>`
                : `${iconPencil}<button type="button" class="row-icon-btn archive" data-action="archive" title="Archive patient" aria-label="Archive patient">${iconArchive}</button>`;

            row.innerHTML = `
                <td><span class="patient-code">${escapeHtml(p.patient_code)}</span></td>
                <td><span class="patient-name">${escapeHtml(p.last_name)}, ${escapeHtml(p.first_name)}</span></td>
                <td>${calcAge(p.date_of_birth)} yrs / ${p.sex === 'male' ? 'M' : 'F'}</td>
                <td>${escapeHtml(p.contact_number || '—')}</td>
                <td>${escapeHtml(p.blood_type)}</td>
                <td>${escapeHtml(p.created_at)}</td>
                <td class="row-action"><div class="row-actions-inline">${actions}</div></td>
            `;
            tableBody.appendChild(row);
        });
    } catch (err) {
        loadingState.style.display = 'none';
        emptyText.textContent = 'Could not load patients.';
        emptyState.style.display = 'block';
    }
}

/* ── Event delegation: ONE listener on the table body handles every row
      click and every archive/restore button, including rows that are
      re-rendered on each search or view switch. ── */
tableBody.addEventListener('click', (e) => {
    const actionBtn = e.target.closest('[data-action]');
    if (actionBtn) {
        const row = actionBtn.closest('tr');
        setPatientStatus(row.dataset.id, actionBtn.dataset.action, row.dataset.name);
        return;
    }
    const row = e.target.closest('tr[data-id]');
    if (row) {
        window.location.href = '<?= base_url("staff/patient_view.php") ?>?id=' + row.dataset.id;
    }
});

/* ── Archive / restore ── */
async function setPatientStatus(id, action, name) {
    const isArchive = action === 'archive';

    const result = await Swal.fire({
        title: isArchive ? 'Archive this patient?' : 'Restore this patient?',
        html: isArchive
            ? `<strong>${escapeHtml(name)}</strong> will be removed from the active list and can no longer be scheduled or edited. Their records are kept, and you can restore them anytime.`
            : `<strong>${escapeHtml(name)}</strong> will return to the active patient list.`,
        icon: isArchive ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: isArchive ? 'Yes, archive' : 'Yes, restore',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    try {
        const formData = new FormData();
        formData.append('patient_id', id);
        formData.append('action', action);
        formData.append('csrf_token', csrfField.value);

        const response = await fetch('<?= base_url("api/patients_archive.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) csrfField.value = data.csrf_token;

        if (data.success) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: isArchive ? 'Patient archived' : 'Patient restored',
                showConfirmButton: false, timer: 2500, timerProgressBar: true
            });
            loadPatients(searchInput.value.trim());
        } else {
            Swal.fire({ icon: 'error', title: isArchive ? 'Could not archive' : 'Could not restore', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    }
}

/* ── Active / Archived toggle ── */
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.dataset.view === currentView) return;
        currentView = btn.dataset.view;
        document.querySelectorAll('.toggle-btn').forEach(b => b.classList.toggle('active', b === btn));
        document.getElementById('archivedNote').style.display = currentView === 'archived' ? 'flex' : 'none';
        document.getElementById('btnAddPatient').style.display = currentView === 'archived' ? 'none' : 'flex';
        loadPatients(searchInput.value.trim());
    });
});

// Live search — debounced so we don't fire a request on every keystroke
searchInput.addEventListener('input', function () {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => loadPatients(searchInput.value.trim()), 350);
});

loadPatients();

/* ── Register modal open/close ── */
const addModal = document.getElementById('addModal');
function openAddModal() {
    addModal.classList.add('open');
    requestAnimationFrame(() => addModal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}
function closeAddModal() {
    addModal.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => addModal.classList.remove('open'), 200);
}
document.getElementById('btnAddPatient').addEventListener('click', openAddModal);
document.getElementById('btnCloseAdd').addEventListener('click', closeAddModal);
addModal.addEventListener('click', (e) => { if (e.target === addModal) closeAddModal(); });

/* ── Asynchronous patient registration ── */
const addForm      = document.getElementById('addForm');
const addAlert     = document.getElementById('addAlert');
const addAlertText = document.getElementById('addAlertText');
const submitBtn    = document.getElementById('submitBtn');
const submitLabel  = document.getElementById('submitBtnLabel');

addForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    addAlert.classList.remove('show');

    submitBtn.disabled = true;
    submitLabel.innerHTML = '<span class="spinner"></span> Saving…';

    try {
        const formData = new FormData(addForm);
        const response = await fetch('<?= base_url("api/patients_add.php") ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        // Refresh the token on EVERY response (success too) — the server
        // rotates it each time, so a stale one would fail the next request.
        if (data.csrf_token) csrfField.value = data.csrf_token;

        if (data.success) {
            closeAddModal();
            addForm.reset();
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Patient registered — ' + data.patient_code,
                showConfirmButton: false, timer: 3000, timerProgressBar: true
            });
            loadPatients(searchInput.value.trim());
        } else {
            addAlertText.textContent = data.message || 'Could not save patient.';
            addAlert.classList.add('show');
        }
    } catch (err) {
        addAlertText.textContent = 'Could not reach the server. Please try again.';
        addAlert.classList.add('show');
    } finally {
        submitBtn.disabled = false;
        submitLabel.textContent = 'Register Patient';
    }
});
</script>