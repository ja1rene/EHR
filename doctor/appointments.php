<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('doctor');

generate_csrf_token();

$page_title = "My Appointments";
$role_label = "Doctor";
$nav_links = [
    ['label' => 'Dashboard',    'url' => 'dashboard.php'],
    ['label' => 'Patients',     'url' => 'patients.php'],
    ['label' => 'Appointments', 'url' => 'appointments.php', 'active' => true],
    ['label' => 'Reports',      'url' => 'reports.php'],

];

ob_start();
?>
<div class="card">
    <div class="toolbar">
        <input type="date" id="dateFilter" class="date-filter">
        <select id="statusFilter" class="status-filter">
            <option value="">All statuses</option>
            <option value="scheduled" selected>Scheduled</option>
            <option value="completed">Completed</option>
            <option value="no_show">No-show</option>
        </select>
    </div>

    <table class="data-table" id="apptTable">
        <thead>
            <tr>
                <th>Date / Time</th>
                <th>Patient</th>
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

<!-- ═══ Start Consultation Modal ═══ -->
<div class="modal-overlay" id="consultModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseConsult" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>Start Consultation</h2>
        <p id="consultPatientLabel">—</p>
    </div>

    <div id="vitalsReference" class="vitals-reference" style="display:none;"></div>

    <div class="alert alert-error" id="consultAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="consultAlertText"></span>
    </div>

    <form id="consultForm">
        <input type="hidden" name="appointment_id" id="consultApptId">
        <input type="hidden" name="csrf_token" id="consultCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="field"><label>Chief Complaint *</label><textarea name="chief_complaint" rows="2" required placeholder="What is the patient here for?"></textarea></div>
        <div class="field"><label>Symptoms</label><textarea name="symptoms" rows="2"></textarea></div>
        <div class="field"><label>Physical Examination</label><textarea name="physical_exam" rows="2"></textarea></div>
        <div class="field"><label>Diagnosis</label><textarea name="diagnosis" rows="2"></textarea></div>
        <div class="field"><label>Treatment Plan</label><textarea name="treatment_plan" rows="2"></textarea></div>
        <div class="field"><label>Doctor's Notes</label><textarea name="doctor_notes" rows="2"></textarea></div>

        <div class="note-box">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Once saved, this consultation cannot be edited — it becomes part of the patient's permanent record. The appointment will also be marked completed.
        </div>

        <button type="submit" class="btn-submit" id="consultSubmitBtn">
            <span id="consultSubmitLabel">Save Consultation</span>
        </button>
    </form>
</div>
</div>

<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

<style>
.toolbar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.date-filter, .status-filter { padding: 10px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; }

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
.btn-mini {
    padding: 6px 12px; border-radius: 999px; border: 1px solid var(--border); background: var(--surface);
    font-size: 0.76rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; white-space: nowrap;
}
.btn-mini.complete { color: var(--teal); border-color: #cfe4e2; }
.btn-mini.complete:hover { background: var(--teal-tint); }
.btn-mini.noshow { color: #B45309; border-color: #f0dcb8; }
.btn-mini.noshow:hover { background: var(--peach-tint); }

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
    position: relative; width: 100%; max-width: 560px; background: var(--surface); border: 1px solid var(--border);
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

.field { margin-bottom: 14px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 6px; }
.field textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.86rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; resize: vertical;
}
.field textarea:focus { border-color: var(--indigo); background: var(--surface); }

.note-box {
    display: flex; gap: 9px; align-items: flex-start;
    background: var(--indigo-tint); color: var(--indigo-dark); border-radius: 8px;
    padding: 11px 13px; font-size: 0.79rem; line-height: 1.4; margin-bottom: 16px;
}
.note-box svg { flex-shrink: 0; margin-top: 1px; }

.vitals-reference {
    background: var(--teal-tint); border: 1px solid #cfe4e2; border-radius: 8px;
    padding: 12px 14px; margin-bottom: 18px; font-size: 0.82rem; color: var(--teal);
}
.vitals-reference .vr-title { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
.vitals-reference .vr-grid { display: flex; flex-wrap: wrap; gap: 10px 18px; }
.vitals-reference .vr-item { font-size: 0.83rem; }
.vitals-reference .vr-item strong { display: block; font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }

.btn-submit {
    width: 100%; padding: 12px; background: var(--indigo); color: white; border: none; border-radius: 999px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;
}
.btn-submit:hover { background: var(--indigo-dark); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .data-table { display: block; overflow-x: auto; white-space: nowrap; }
    .modal-card { padding: 26px 20px 22px; }
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
const dateFilter   = document.getElementById('dateFilter');
const statusFilter = document.getElementById('statusFilter');

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
            const actions = a.status === 'scheduled'
                ? `<button type="button" class="btn-mini complete" data-id="${a.appointment_id}" data-patient="${escapeHtml(a.patient_last)}, ${escapeHtml(a.patient_first)}">Start Consultation</button>
                   <button type="button" class="btn-mini noshow" data-id="${a.appointment_id}" data-status="no_show">No-show</button>`
                : '';
            row.innerHTML = `
                <td>${a.appointment_date}<br><span style="color:var(--text-muted);font-size:0.78rem;">${formatTime(a.appointment_time)}</span></td>
                <td><span class="patient-name">${escapeHtml(a.patient_last)}, ${escapeHtml(a.patient_first)}</span><span class="patient-code">${escapeHtml(a.patient_code)}</span></td>
                <td>${escapeHtml(a.reason || '—')}</td>
                <td><span class="status-badge status-${a.status}">${formatStatus(a.status)}</span></td>
                <td><div class="row-actions">${actions}</div></td>
            `;
            apptBody.appendChild(row);
        });

        document.querySelectorAll('.btn-mini.noshow').forEach(btn => {
            btn.addEventListener('click', () => updateStatus(btn.dataset.id, btn.dataset.status));
        });
        document.querySelectorAll('.btn-mini.complete').forEach(btn => {
            btn.addEventListener('click', () => openConsultationModal(btn.dataset.id, btn.dataset.patient));
        });
    } catch (err) {
        loadingState.style.display = 'none';
        emptyState.style.display = 'block';
    }
}

async function updateStatus(id, status) {
    const label = status === 'completed' ? 'mark this appointment completed' : 'mark this appointment as a no-show';
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `This will ${label}.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, confirm',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('appointment_id', id);
    formData.append('status', status);
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    try {
        const response = await fetch('<?= base_url("api/appointments_update_status.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('csrfToken').value = data.csrf_token;

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Status updated', showConfirmButton: false, timer: 2000, timerProgressBar: true });
            loadAppointments();
        } else {
            Swal.fire({ icon: 'error', title: 'Could not update', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    }
}

dateFilter.addEventListener('change', loadAppointments);
statusFilter.addEventListener('change', loadAppointments);
loadAppointments();

/* ── Start Consultation modal ── */
const consultModal = document.getElementById('consultModal');
function openConsultationModal(appointmentId, patientLabel) {
    document.getElementById('consultApptId').value = appointmentId;
    document.getElementById('consultPatientLabel').textContent = 'For: ' + patientLabel;
    document.getElementById('consultForm').reset();
    document.getElementById('consultApptId').value = appointmentId; // reset() clears hidden fields too
    document.getElementById('consultAlert').classList.remove('show');
    consultModal.classList.add('open');
    requestAnimationFrame(() => consultModal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    loadVitalsReference(appointmentId);
}

async function loadVitalsReference(appointmentId) {
    const box = document.getElementById('vitalsReference');
    box.style.display = 'none';
    try {
        const response = await fetch('<?= base_url("api/vitals_latest_for_appointment.php") ?>?appointment_id=' + appointmentId);
        const data = await response.json();
        if (!data.success) return;

        const v = data.vitals;
        const items = [];
        if (v.blood_pressure) items.push(['BP', v.blood_pressure]);
        if (v.temperature_c) items.push(['Temp', v.temperature_c + '°C']);
        if (v.pulse_rate) items.push(['Pulse', v.pulse_rate + ' bpm']);
        if (v.respiratory_rate) items.push(['Resp.', v.respiratory_rate + '/min']);
        if (v.oxygen_saturation) items.push(['O₂ Sat', v.oxygen_saturation + '%']);
        if (v.height_cm) items.push(['Height', v.height_cm + ' cm']);
        if (v.weight_kg) items.push(['Weight', v.weight_kg + ' kg']);
        if (items.length === 0) return;

        const label = data.source === 'this_appointment' ? 'Vitals recorded for this visit' : 'Most recent vitals on file (previous visit)';
        box.innerHTML = `<div class="vr-title">${label}</div><div class="vr-grid">` +
            items.map(([k, val]) => `<div class="vr-item"><strong>${k}</strong>${val}</div>`).join('') + `</div>`;
        box.style.display = 'block';
    } catch (err) {
        // Silently skip — this is reference info, not required for the form to work.
    }
}
function closeConsultModal() {
    consultModal.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => consultModal.classList.remove('open'), 200);
}
document.getElementById('btnCloseConsult').addEventListener('click', closeConsultModal);
consultModal.addEventListener('click', (e) => { if (e.target === consultModal) closeConsultModal(); });

const consultForm      = document.getElementById('consultForm');
const consultAlert       = document.getElementById('consultAlert');
const consultAlertText  = document.getElementById('consultAlertText');
const consultSubmitBtn   = document.getElementById('consultSubmitBtn');
const consultSubmitLabel = document.getElementById('consultSubmitLabel');

consultForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    consultAlert.classList.remove('show');

    const result = await Swal.fire({
        title: 'Save this consultation?',
        text: 'This cannot be edited once saved, and the appointment will be marked completed.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it',
        cancelButtonText: 'Keep editing',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    consultSubmitBtn.disabled = true;
    consultSubmitLabel.innerHTML = '<span class="spinner"></span> Saving…';

    try {
        const formData = new FormData(consultForm);
        const response = await fetch('<?= base_url("api/consultations_add.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('consultCsrfToken').value = data.csrf_token;

        if (data.success) {
            closeConsultModal();
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Consultation saved', showConfirmButton: false, timer: 2500, timerProgressBar: true });
            loadAppointments();
        } else {
            consultAlertText.textContent = data.message || 'Could not save consultation.';
            consultAlert.classList.add('show');
        }
    } catch (err) {
        consultAlertText.textContent = 'Could not reach the server. Please try again.';
        consultAlert.classList.add('show');
    } finally {
        consultSubmitBtn.disabled = false;
        consultSubmitLabel.textContent = 'Save Consultation';
    }
});
</script>