<?php
generate_csrf_token();
$patientId = (int)($_GET['id'] ?? 0);

ob_start();
?>
<div id="loadingCard" class="card loading-card">Loading patient record…</div>
<div id="notFoundCard" class="card" style="display:none;">
    <p style="color: var(--text-secondary);">This patient record could not be found, or you may not have permission to view it.</p>
</div>

<div id="profileWrap" style="display:none;">
    <div class="profile-header card">
        <div class="profile-id">
            <a href="patients.php" class="btn-back" aria-label="Back to Patients">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="profile-avatar" id="profileAvatar">--</div>
            <div>
                <div class="profile-name" id="profileName">—</div>
                <div class="profile-meta" id="profileMeta">—</div>
            </div>
        </div>
        <?php if ($editable): ?>
        <button type="button" class="btn-primary" id="btnEdit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Details
        </button>
        <?php endif; ?>
    </div>

    <div class="alert alert-error" id="saveAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="saveAlertText"></span>
    </div>

    <form id="profileForm">
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="patient_id" id="patientIdField" value="<?= $patientId ?>">

        <div class="card section-card">
            <div class="section-title">Personal Information</div>
            <div class="form-row">
                <div class="field"><label>First name</label><input type="text" name="first_name" id="f_first_name" disabled></div>
                <div class="field"><label>Middle name</label><input type="text" name="middle_name" id="f_middle_name" disabled></div>
                <div class="field"><label>Last name</label><input type="text" name="last_name" id="f_last_name" disabled></div>
            </div>
            <div class="form-row">
                <div class="field"><label>Date of birth</label><input type="date" name="date_of_birth" id="f_dob" disabled></div>
                <div class="field">
                    <label>Sex</label>
                    <select name="sex" id="f_sex" disabled>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="field">
                    <label>Civil status</label>
                    <select name="civil_status" id="f_civil" disabled>
                        <option value="">—</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="widowed">Widowed</option>
                        <option value="separated">Separated</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="field"><label>Contact number</label><input type="text" name="contact_number" id="f_contact" disabled></div>
                <div class="field wide"><label>Address</label><input type="text" name="address" id="f_address" disabled></div>
            </div>
        </div>

        <div class="card section-card">
            <div class="section-title">Emergency Contact</div>
            <div class="form-row">
                <div class="field"><label>Name</label><input type="text" name="emergency_contact_name" id="f_ec_name" disabled></div>
                <div class="field"><label>Number</label><input type="text" name="emergency_contact_number" id="f_ec_number" disabled></div>
            </div>
        </div>

        <div class="card section-card">
            <div class="section-title">Medical Information</div>
            <div class="form-row">
                <div class="field">
                    <label>Blood type</label>
                    <select name="blood_type" id="f_blood_type" disabled>
                        <option value="unknown">Unknown</option>
                        <option>A+</option><option>A-</option>
                        <option>B+</option><option>B-</option>
                        <option>AB+</option><option>AB-</option>
                        <option>O+</option><option>O-</option>
                    </select>
                </div>
            </div>
            <div class="field"><label>Allergies</label><textarea name="allergies" id="f_allergies" rows="2" disabled></textarea></div>
            <div class="field"><label>Known medical conditions</label><textarea name="medical_conditions" id="f_conditions" rows="2" disabled></textarea></div>
        </div>

        <?php if ($editable): ?>
        <div class="form-actions" id="formActions" style="display:none;">
            <button type="button" class="btn-secondary" id="btnCancelEdit">Cancel</button>
            <button type="submit" class="btn-primary" id="btnSave">
                <span id="btnSaveLabel">Save Changes</span>
            </button>
        </div>
        <?php endif; ?>
    </form>

    <div class="card section-card" id="labResultsCard">
        <div class="section-header-row">
            <div class="section-title" style="margin-bottom:0;">Laboratory Results</div>
            <?php if ($canAddLabs): ?>
            <button type="button" class="btn-primary" id="btnAddLab">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Lab Result
            </button>
            <?php endif; ?>
        </div>
        <div id="labLoading" class="history-empty">Loading lab results…</div>
        <div id="labEmpty" class="history-empty" style="display:none;">No lab results recorded yet.</div>
        <div id="labList"></div>
    </div>

    <div class="card section-card" id="vitalsCard">
        <div class="section-title">Vitals History</div>
        <div id="vitalsLoading" class="history-empty">Loading vitals…</div>
        <div id="vitalsEmpty" class="history-empty" style="display:none;">No vitals recorded yet.</div>
        <table class="vitals-table" id="vitalsTable" style="display:none;">
            <thead>
                <tr><th>Date</th><th>BP</th><th>Temp</th><th>Pulse</th><th>Resp.</th><th>O₂ Sat</th><th>Height</th><th>Weight</th><th>Recorded By</th></tr>
            </thead>
            <tbody id="vitalsBody"></tbody>
        </table>
    </div>

    <div class="card section-card" id="consultHistoryCard">
        <div class="section-title">Consultation History</div>
        <div id="consultLoading" class="history-empty">Loading consultation history…</div>
        <div id="consultEmpty" class="history-empty" style="display:none;">No consultations recorded yet.</div>
        <div id="consultList"></div>
    </div>

    <div class="card section-card" id="prescriptionsCard">
        <div class="section-header-row">
            <div class="section-title" style="margin-bottom:0;">Prescriptions</div>
            <?php if ($canPrescribe): ?>
            <button type="button" class="btn-primary" id="btnNewRx">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Prescription
            </button>
            <?php endif; ?>
        </div>
        <div id="rxLoading" class="history-empty">Loading prescriptions…</div>
        <div id="rxEmpty" class="history-empty" style="display:none;">No prescriptions recorded yet.</div>
        <div id="rxList"></div>
    </div>
</div>

<?php if ($canPrescribe): ?>
<!-- ═══ New Prescription Modal ═══ -->
<div class="modal-overlay" id="rxModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseRx" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>New Prescription</h2>
        <p>Add one or more medicines below.</p>
    </div>

    <div class="alert alert-error" id="rxAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="rxAlertText"></span>
    </div>

    <form id="rxForm">
        <input type="hidden" name="patient_id" value="<?= $patientId ?>">
        <input type="hidden" name="csrf_token" id="rxCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div id="medicineRows"></div>

        <button type="button" class="btn-add-row" id="btnAddRow">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Another Medicine
        </button>

        <div class="field" style="margin-top:16px;">
            <label>Notes (optional)</label>
            <textarea name="notes" rows="2"></textarea>
        </div>

        <button type="submit" class="btn-submit" id="rxSubmitBtn">
            <span id="rxSubmitLabel">Save Prescription</span>
        </button>
    </form>
</div>
</div>
<?php endif; ?>

<?php if ($canAddLabs): ?>
<!-- ═══ Add Lab Result Modal ═══ -->
<div class="modal-overlay" id="labModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseLab" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>Add Lab Result</h2>
        <p>Record a laboratory test and its result.</p>
    </div>

    <div class="alert alert-error" id="labAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="labAlertText"></span>
    </div>

    <form id="labForm">
        <input type="hidden" name="patient_id" value="<?= $patientId ?>">
        <input type="hidden" name="csrf_token" id="labCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="field"><label>Test Name *</label><input type="text" name="test_name" placeholder="e.g. Complete Blood Count" required></div>

        <div class="form-row-lab">
            <div class="field"><label>Test Date *</label><input type="date" name="test_date" required></div>
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <option value="pending">Pending</option>
                    <option value="completed" selected>Completed</option>
                </select>
            </div>
        </div>

        <div class="form-row-lab">
            <div class="field"><label>Result</label><input type="text" name="result_value" placeholder="e.g. 13.5 g/dL"></div>
            <div class="field"><label>Reference Range</label><input type="text" name="reference_range" placeholder="e.g. 12–16 g/dL"></div>
        </div>

        <div class="field"><label>Remarks</label><textarea name="remarks" rows="2" placeholder="Optional notes"></textarea></div>

        <button type="submit" class="btn-submit" id="labSubmitBtn">
            <span id="labSubmitLabel">Save Lab Result</span>
        </button>
    </form>
</div>
</div>
<?php endif; ?>

<style>
.loading-card { text-align: center; color: var(--text-muted); padding: 48px; }
.profile-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 14px; }
.profile-id { display: flex; align-items: center; gap: 14px; }
.btn-back {
    width: 38px; height: 38px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-secondary); flex-shrink: 0; text-decoration: none;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.btn-back:hover { background: var(--indigo-tint); border-color: var(--indigo); color: var(--indigo-dark); }
.profile-avatar {
    width: 52px; height: 52px; border-radius: 50%; background: var(--teal); color: white;
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0;
}
.profile-name { font-family: 'Fraunces', serif; font-size: 1.2rem; font-weight: 600; }
.profile-meta { font-size: 0.82rem; color: var(--text-secondary); margin-top: 2px; }

.btn-primary {
    display: flex; align-items: center; gap: 7px;
    background: var(--indigo); color: white; border: none;
    padding: 10px 18px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.15s;
}
.btn-primary:hover { background: var(--indigo-dark); }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-secondary {
    background: var(--bg); color: var(--text-secondary); border: 1.5px solid var(--border);
    padding: 10px 18px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.section-card { margin-bottom: 16px; }
.section-title { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--indigo); margin-bottom: 16px; }

.form-row { display: flex; gap: 14px; margin-bottom: 4px; flex-wrap: wrap; }
.field { flex: 1; min-width: 140px; margin-bottom: 14px; }
.field.wide { flex: 2; min-width: 220px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 6px; }
.field input, .field select, .field textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border);
    border-radius: 8px; font-size: 0.86rem; font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--surface); outline: none; resize: vertical;
}
.field input:disabled, .field select:disabled, .field textarea:disabled {
    background: var(--bg); color: var(--text-secondary); cursor: default;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color: var(--indigo); }

.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }

.alert {
    display: none; align-items: flex-start; gap: 9px;
    padding: 11px 13px; border-radius: 8px;
    margin-bottom: 16px; font-size: 0.82rem; font-weight: 500; border: 1px solid transparent;
}
.alert.show { display: flex; }
.alert-error { background: #FDECEA; color: #B3261E; border-color: #f2c6c3; }

.spinner {
    width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .profile-header { flex-direction: column; align-items: flex-start; }
}

.history-empty { color: var(--text-muted); font-size: 0.84rem; padding: 8px 0; }
.consult-entry { border-top: 1px solid var(--border); padding: 16px 0; }
.consult-entry:first-child { border-top: none; padding-top: 0; }
.consult-entry-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; flex-wrap: wrap; gap: 6px; }
.consult-entry-date { font-weight: 700; font-size: 0.86rem; }
.consult-entry-doctor { font-size: 0.78rem; color: var(--text-muted); }
.consult-field { font-size: 0.84rem; margin-bottom: 6px; }
.consult-field strong { color: var(--text-secondary); font-weight: 600; }

.vitals-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.vitals-table th { text-align: left; padding: 8px 10px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); border-bottom: 1.5px solid var(--border); }
.vitals-table td { padding: 9px 10px; border-bottom: 1px solid var(--border); }
.vitals-table tr:last-child td { border-bottom: none; }

.form-row-lab { display: flex; gap: 14px; }
.form-row-lab .field { flex: 1; }

.lab-entry { border-top: 1px solid var(--border); padding: 14px 0; }
.lab-entry:first-child { border-top: none; padding-top: 0; }
.lab-entry-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px; flex-wrap: wrap; gap: 6px; }
.lab-entry-name { font-weight: 700; font-size: 0.87rem; }
.lab-entry-date { font-size: 0.78rem; color: var(--text-muted); }
.lab-status-badge { display: inline-flex; padding: 2px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; margin-left: 8px; }
.lab-status-pending { background: var(--peach-tint); color: #B45309; }
.lab-status-completed { background: var(--teal-tint); color: var(--teal); }
.lab-entry-detail { font-size: 0.83rem; color: var(--text-secondary); margin-bottom: 3px; }
.lab-entry-detail strong { color: var(--text-primary); font-weight: 600; }

.section-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.btn-primary {
    display: flex; align-items: center; gap: 7px; background: var(--indigo); color: white; border: none;
    padding: 9px 16px; border-radius: 999px; font-size: 0.82rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.15s;
}
.btn-primary:hover { background: var(--indigo-dark); }

.rx-entry { border-top: 1px solid var(--border); padding: 16px 0; }
.rx-entry:first-child { border-top: none; padding-top: 0; }
.rx-entry-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 10px; flex-wrap: wrap; gap: 6px; }
.rx-entry-date { font-weight: 700; font-size: 0.86rem; }
.rx-entry-doctor { font-size: 0.78rem; color: var(--text-muted); }
.rx-item {
    background: var(--bg); border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; font-size: 0.83rem;
}
.rx-item-name { font-weight: 700; margin-bottom: 2px; }
.rx-item-detail { color: var(--text-secondary); font-size: 0.78rem; }
.rx-notes { font-size: 0.82rem; color: var(--text-secondary); margin-top: 8px; font-style: italic; }

/* Modal (shared pattern with other modals in this design system) */
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

.medicine-row {
    display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr auto; gap: 8px;
    margin-bottom: 10px; align-items: start;
}
.medicine-row input { width: 100%; padding: 8px 10px; border: 1.5px solid var(--border); border-radius: 7px; font-size: 0.82rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; }
.medicine-row input:focus { border-color: var(--indigo); background: var(--surface); }
.btn-remove-row {
    width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--border); background: var(--surface);
    color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.btn-remove-row:hover { background: #FDECEA; border-color: #f2c6c3; color: #B3261E; }
.medicine-row-labels { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr auto; gap: 8px; margin-bottom: 6px; }
.medicine-row-labels span { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; }

.btn-add-row {
    display: flex; align-items: center; gap: 6px; background: none; border: 1.5px dashed var(--border);
    color: var(--indigo); padding: 9px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 700;
    cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; width: 100%; justify-content: center;
}
.btn-add-row:hover { background: var(--indigo-tint); }

.field textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.86rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; resize: vertical;
}

.btn-submit {
    width: 100%; padding: 12px; background: var(--indigo); color: white; border: none; border-radius: 999px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; margin-top: 14px;
}
.btn-submit:hover { background: var(--indigo-dark); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.spinner2 { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: spin2 0.7s linear infinite; display: inline-block; }
@keyframes spin2 { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .medicine-row, .medicine-row-labels { grid-template-columns: 1fr; }
    .modal-card { padding: 26px 20px 22px; }
}
</style>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/dashboard_shell.php";
?>

<script>
const patientId = <?= $patientId ?>;
const editable  = <?= $editable ? 'true' : 'false' ?>;

// Defense-in-depth: values here were already escaped server-side via
// clean_input() before storage, so this is a second layer, not the only
// one — but every other page in the system does this, and this file
// shouldn't be the one exception.
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

const loadingCard  = document.getElementById('loadingCard');
const notFoundCard = document.getElementById('notFoundCard');
const profileWrap  = document.getElementById('profileWrap');

const fields = ['first_name','middle_name','last_name','date_of_birth','sex','civil_status',
                 'contact_number','address','emergency_contact_name','emergency_contact_number',
                 'blood_type','allergies','medical_conditions'];

// Explicit field-name → element-id map. Using an object here instead of
// pattern-guessing the id from the field name — the previous approach
// silently missed civil_status, contact_number, and medical_conditions
// because they don't follow the same abbreviation pattern as the others.
const fieldElementIds = {
    first_name: 'f_first_name',
    middle_name: 'f_middle_name',
    last_name: 'f_last_name',
    date_of_birth: 'f_dob',
    sex: 'f_sex',
    civil_status: 'f_civil',
    contact_number: 'f_contact',
    address: 'f_address',
    emergency_contact_name: 'f_ec_name',
    emergency_contact_number: 'f_ec_number',
    blood_type: 'f_blood_type',
    allergies: 'f_allergies',
    medical_conditions: 'f_conditions'
};

function calcAge(dob) {
    const birth = new Date(dob);
    const diff = new Date(Date.now() - birth.getTime());
    return Math.abs(diff.getUTCFullYear() - 1970);
}

/* ── Asynchronous load of the patient record ── */
async function loadPatient() {
    if (!patientId) {
        loadingCard.style.display = 'none';
        notFoundCard.style.display = 'block';
        return;
    }
    try {
        const response = await fetch('<?= base_url("api/patients_get.php") ?>?id=' + patientId);
        const data = await response.json();

        loadingCard.style.display = 'none';

        if (!data.success) {
            notFoundCard.style.display = 'block';
            return;
        }

        const p = data.patient;
        if (p.status === 'archived') {
            const banner = document.createElement('div');
            banner.className = 'alert show';
            banner.style.cssText = 'margin-bottom:16px;background:#FEF3E7;color:#B45309;border-color:#f0dcb8;';
            banner.textContent = 'This patient is archived. Their record is read-only. Restore them from the Patients list to make changes.';
            profileWrap.prepend(banner);
            ['btnEdit', 'btnAddLab', 'btnNewRx'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
        }
        fields.forEach(f => {
            const el = document.getElementById(fieldElementIds[f]);
            if (el) el.value = p[f] ?? '';
        });

        document.getElementById('profileName').textContent = `${p.last_name}, ${p.first_name}`;
        document.getElementById('profileMeta').textContent =
            `${p.patient_code} · ${calcAge(p.date_of_birth)} yrs · ${p.sex === 'male' ? 'Male' : 'Female'}`;
        document.getElementById('profileAvatar').textContent =
            (p.first_name[0] || '?').toUpperCase() + (p.last_name[0] || '').toUpperCase();

        profileWrap.style.display = 'block';

        if (!document.getElementById('btnPrintSummary')) {
            const summaryLink = document.createElement('a');
            summaryLink.id = 'btnPrintSummary';
            summaryLink.href = '<?= base_url("patient_summary.php") ?>?id=' + patientId;
            summaryLink.target = '_blank';
            summaryLink.rel = 'noopener';
            summaryLink.className = 'btn-secondary';
            summaryLink.style.cssText = 'display:inline-flex;align-items:center;gap:7px;text-decoration:none;';
            summaryLink.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg> Print summary';

            const profileHeader = document.querySelector('.profile-header');
            const headerActions = document.createElement('div');
            headerActions.style.cssText = 'display:flex;gap:10px;align-items:center;flex-wrap:wrap;';
            const existingEditBtn = document.getElementById('btnEdit');
            headerActions.appendChild(summaryLink);
            if (existingEditBtn) headerActions.appendChild(existingEditBtn); // moving it keeps its click handler
            profileHeader.appendChild(headerActions);
        }
    } catch (err) {
        loadingCard.style.display = 'none';
        notFoundCard.style.display = 'block';
    }
}
loadPatient();

/* ── Asynchronous consultation history load ── */
async function loadConsultationHistory() {
    const loadingEl = document.getElementById('consultLoading');
    const emptyEl   = document.getElementById('consultEmpty');
    const listEl    = document.getElementById('consultList');

    try {
        const response = await fetch('<?= base_url("api/consultations_list.php") ?>?patient_id=' + patientId);
        const data = await response.json();
        loadingEl.style.display = 'none';

        if (!data.success || data.consultations.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }

        data.consultations.forEach(c => {
            const entry = document.createElement('div');
            entry.className = 'consult-entry';
            entry.innerHTML = `
                <div class="consult-entry-header">
                    <span class="consult-entry-date">${escapeHtml(c.created_at)}</span>
                    <span class="consult-entry-doctor">${escapeHtml(c.doctor_name)}</span>
                </div>
                ${c.chief_complaint ? `<div class="consult-field"><strong>Chief Complaint:</strong> ${escapeHtml(c.chief_complaint)}</div>` : ''}
                ${c.diagnosis ? `<div class="consult-field"><strong>Diagnosis:</strong> ${escapeHtml(c.diagnosis)}</div>` : ''}
                ${c.treatment_plan ? `<div class="consult-field"><strong>Treatment Plan:</strong> ${escapeHtml(c.treatment_plan)}</div>` : ''}
            `;
            listEl.appendChild(entry);
        });
    } catch (err) {
        loadingEl.style.display = 'none';
        emptyEl.textContent = 'Could not load consultation history.';
        emptyEl.style.display = 'block';
    }
}
if (patientId) loadConsultationHistory();

/* ── Asynchronous vitals history load (both roles) ── */
async function loadVitalsHistory() {
    const loadingEl = document.getElementById('vitalsLoading');
    const emptyEl   = document.getElementById('vitalsEmpty');
    const table     = document.getElementById('vitalsTable');
    const body      = document.getElementById('vitalsBody');

    try {
        const response = await fetch('<?= base_url("api/vitals_list.php") ?>?patient_id=' + patientId);
        const data = await response.json();
        loadingEl.style.display = 'none';

        if (!data.success || data.vitals.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }

        data.vitals.forEach(v => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${v.recorded_at}</td>
                <td>${v.blood_pressure || '—'}</td>
                <td>${v.temperature_c ? v.temperature_c + '°C' : '—'}</td>
                <td>${v.pulse_rate ? v.pulse_rate + ' bpm' : '—'}</td>
                <td>${v.respiratory_rate ? v.respiratory_rate + '/min' : '—'}</td>
                <td>${v.oxygen_saturation ? v.oxygen_saturation + '%' : '—'}</td>
                <td>${v.height_cm ? v.height_cm + ' cm' : '—'}</td>
                <td>${v.weight_kg ? v.weight_kg + ' kg' : '—'}</td>
                <td>${v.recorded_by_name}</td>
            `;
            body.appendChild(row);
        });
        table.style.display = 'table';
    } catch (err) {
        loadingEl.style.display = 'none';
        emptyEl.textContent = 'Could not load vitals history.';
        emptyEl.style.display = 'block';
    }
}
if (patientId) loadVitalsHistory();

/* ── Asynchronous lab results history load (both roles) ── */
async function loadLabResults() {
    const loadingEl = document.getElementById('labLoading');
    const emptyEl   = document.getElementById('labEmpty');
    const listEl    = document.getElementById('labList');

    try {
        const response = await fetch('<?= base_url("api/lab_results_list.php") ?>?patient_id=' + patientId);
        const data = await response.json();
        loadingEl.style.display = 'none';

        if (!data.success || data.lab_results.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }

        data.lab_results.forEach(lr => {
            const entry = document.createElement('div');
            entry.className = 'lab-entry';
            entry.innerHTML = `
                <div class="lab-entry-header">
                    <span class="lab-entry-name">${escapeHtml(lr.test_name)}<span class="lab-status-badge lab-status-${lr.status}">${lr.status === 'completed' ? 'Completed' : 'Pending'}</span></span>
                    <span class="lab-entry-date">${escapeHtml(lr.test_date)} · ${escapeHtml(lr.recorded_by_name)}</span>
                </div>
                ${lr.result_value ? `<div class="lab-entry-detail"><strong>Result:</strong> ${escapeHtml(lr.result_value)}${lr.reference_range ? ' (ref: ' + escapeHtml(lr.reference_range) + ')' : ''}</div>` : ''}
                ${lr.remarks ? `<div class="lab-entry-detail">${escapeHtml(lr.remarks)}</div>` : ''}
            `;
            listEl.appendChild(entry);
        });
    } catch (err) {
        loadingEl.style.display = 'none';
        emptyEl.textContent = 'Could not load lab results.';
        emptyEl.style.display = 'block';
    }
}
if (patientId) loadLabResults();

<?php if ($canAddLabs): ?>
/* ── Add Lab Result modal (staff only) ── */
const labModal = document.getElementById('labModal');
function openLabModal() {
    document.getElementById('labForm').reset();
    document.getElementById('labAlert').classList.remove('show');
    labModal.classList.add('open');
    requestAnimationFrame(() => labModal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}
function closeLabModal() {
    labModal.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => labModal.classList.remove('open'), 200);
}
document.getElementById('btnAddLab').addEventListener('click', openLabModal);
document.getElementById('btnCloseLab').addEventListener('click', closeLabModal);
labModal.addEventListener('click', (e) => { if (e.target === labModal) closeLabModal(); });

const labForm       = document.getElementById('labForm');
const labAlert        = document.getElementById('labAlert');
const labAlertText   = document.getElementById('labAlertText');
const labSubmitBtn    = document.getElementById('labSubmitBtn');
const labSubmitLabel = document.getElementById('labSubmitLabel');

labForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    labAlert.classList.remove('show');
    labSubmitBtn.disabled = true;
    labSubmitLabel.innerHTML = '<span class="spinner2"></span> Saving…';

    try {
        const formData = new FormData(labForm);
        const response = await fetch('<?= base_url("api/lab_results_add.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('labCsrfToken').value = data.csrf_token;

        if (data.success) {
            closeLabModal();
            document.getElementById('labList').innerHTML = '';
            document.getElementById('labEmpty').style.display = 'none';
            loadLabResults();
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Lab result saved', showConfirmButton: false, timer: 2500, timerProgressBar: true });
        } else {
            labAlertText.textContent = data.message || 'Could not save lab result.';
            labAlert.classList.add('show');
        }
    } catch (err) {
        labAlertText.textContent = 'Could not reach the server. Please try again.';
        labAlert.classList.add('show');
    } finally {
        labSubmitBtn.disabled = false;
        labSubmitLabel.textContent = 'Save Lab Result';
    }
});
<?php endif; ?>

/* ── Asynchronous prescription history load (both roles) ── */
async function loadPrescriptions() {
    const loadingEl = document.getElementById('rxLoading');
    const emptyEl   = document.getElementById('rxEmpty');
    const listEl    = document.getElementById('rxList');

    try {
        const response = await fetch('<?= base_url("api/prescriptions_list.php") ?>?patient_id=' + patientId);
        const data = await response.json();
        loadingEl.style.display = 'none';

        if (!data.success || data.prescriptions.length === 0) {
            emptyEl.style.display = 'block';
            return;
        }

        data.prescriptions.forEach(rx => {
            const entry = document.createElement('div');
            entry.className = 'rx-entry';
            let itemsHtml = rx.items.map(it => `
                <div class="rx-item">
                    <div class="rx-item-name">${escapeHtml(it.medicine_name)}</div>
                    <div class="rx-item-detail">${[it.dosage, it.frequency, it.duration].filter(Boolean).map(escapeHtml).join(' · ')}</div>
                    ${it.instructions ? `<div class="rx-item-detail">${escapeHtml(it.instructions)}</div>` : ''}
                </div>
            `).join('');
            entry.innerHTML = `
                <div class="rx-entry-header">
                    <span class="rx-entry-date">${escapeHtml(rx.created_at)}</span>
                    <span class="rx-entry-doctor">${escapeHtml(rx.doctor_name)}</span>
                </div>
                ${itemsHtml}
                ${rx.notes ? `<div class="rx-notes">${escapeHtml(rx.notes)}</div>` : ''}
            `;
            listEl.appendChild(entry);
        });
    } catch (err) {
        loadingEl.style.display = 'none';
        emptyEl.textContent = 'Could not load prescriptions.';
        emptyEl.style.display = 'block';
    }
}
if (patientId) loadPrescriptions();

<?php if ($canPrescribe): ?>
/* ── Dynamic medicine rows + new prescription modal (doctor only) ── */
const medicineRows = document.getElementById('medicineRows');
let rowCounter = 0;

function addMedicineRow() {
    rowCounter++;
    const row = document.createElement('div');
    row.className = 'medicine-row';
    if (rowCounter === 1) {
        const labels = document.createElement('div');
        labels.className = 'medicine-row-labels';
        labels.innerHTML = `<span>Medicine</span><span>Dosage</span><span>Frequency</span><span>Duration</span><span></span>`;
        medicineRows.appendChild(labels);
    }
    row.innerHTML = `
        <input type="text" name="medicine_name[]" placeholder="e.g. Amoxicillin">
        <input type="text" name="dosage[]" placeholder="500mg">
        <input type="text" name="frequency[]" placeholder="3x daily">
        <input type="text" name="duration[]" placeholder="7 days">
        <button type="button" class="btn-remove-row" title="Remove">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    `;
    // A second row for instructions, spanning full width beneath.
    const instrRow = document.createElement('input');
    instrRow.type = 'text';
    instrRow.name = 'instructions[]';
    instrRow.placeholder = 'Instructions (e.g. Take after meals)';
    instrRow.style.cssText = 'width:100%;margin-bottom:14px;padding:8px 10px;border:1.5px solid var(--border);border-radius:7px;font-size:0.82rem;font-family:\'Plus Jakarta Sans\',sans-serif;background:var(--bg);outline:none;';

    // Event delegation via direct listener is fine here since rows are
    // added one at a time by this same function, not bulk-rendered.
    row.querySelector('.btn-remove-row').addEventListener('click', () => {
        row.remove();
        instrRow.remove();
    });

    medicineRows.appendChild(row);
    medicineRows.appendChild(instrRow);
}

// Start with one row by default when the modal opens.
function resetMedicineRows() {
    medicineRows.innerHTML = '';
    rowCounter = 0;
    addMedicineRow();
}

document.getElementById('btnAddRow').addEventListener('click', addMedicineRow);

const rxModal = document.getElementById('rxModal');
function openRxModal() {
    resetMedicineRows();
    document.getElementById('rxForm').reset();
    document.getElementById('rxAlert').classList.remove('show');
    rxModal.classList.add('open');
    requestAnimationFrame(() => rxModal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}
function closeRxModal() {
    rxModal.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => rxModal.classList.remove('open'), 200);
}
document.getElementById('btnNewRx').addEventListener('click', openRxModal);
document.getElementById('btnCloseRx').addEventListener('click', closeRxModal);
rxModal.addEventListener('click', (e) => { if (e.target === rxModal) closeRxModal(); });

const rxForm       = document.getElementById('rxForm');
const rxAlert        = document.getElementById('rxAlert');
const rxAlertText   = document.getElementById('rxAlertText');
const rxSubmitBtn    = document.getElementById('rxSubmitBtn');
const rxSubmitLabel = document.getElementById('rxSubmitLabel');

rxForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    rxAlert.classList.remove('show');

    const result = await Swal.fire({
        title: 'Save this prescription?',
        text: 'This will be added to the patient\'s record.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    rxSubmitBtn.disabled = true;
    rxSubmitLabel.innerHTML = '<span class="spinner2"></span> Saving…';

    try {
        const formData = new FormData(rxForm);
        const response = await fetch('<?= base_url("api/prescriptions_add.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('rxCsrfToken').value = data.csrf_token;

        if (data.success) {
            closeRxModal();
            document.getElementById('rxList').innerHTML = '';
            document.getElementById('rxEmpty').style.display = 'none';
            loadPrescriptions();
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Prescription saved', showConfirmButton: false, timer: 2500, timerProgressBar: true });
        } else {
            rxAlertText.textContent = data.message || 'Could not save prescription.';
            rxAlert.classList.add('show');
        }
    } catch (err) {
        rxAlertText.textContent = 'Could not reach the server. Please try again.';
        rxAlert.classList.add('show');
    } finally {
        rxSubmitBtn.disabled = false;
        rxSubmitLabel.textContent = 'Save Prescription';
    }
});
<?php endif; ?>

<?php if ($editable): ?>
/* ── Edit mode toggle + asynchronous save (staff only) ── */
const btnEdit       = document.getElementById('btnEdit');
const btnCancelEdit = document.getElementById('btnCancelEdit');
const formActions   = document.getElementById('formActions');
const saveAlert       = document.getElementById('saveAlert');
const saveAlertText  = document.getElementById('saveAlertText');
const profileForm     = document.getElementById('profileForm');
const btnSave        = document.getElementById('btnSave');
const btnSaveLabel   = document.getElementById('btnSaveLabel');

function setEditMode(on) {
    fields.forEach(f => {
        const el = document.getElementById(fieldElementIds[f]);
        if (el) el.disabled = !on;
    });
    formActions.style.display = on ? 'flex' : 'none';
    btnEdit.style.display = on ? 'none' : 'flex';
}

btnEdit.addEventListener('click', () => setEditMode(true));
btnCancelEdit.addEventListener('click', () => { setEditMode(false); loadPatient(); });

profileForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    saveAlert.classList.remove('show');

    const confirmResult = await Swal.fire({
        title: 'Save changes?',
        text: 'This will update the patient\'s record.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save changes',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!confirmResult.isConfirmed) return;

    btnSave.disabled = true;
    btnSaveLabel.innerHTML = '<span class="spinner"></span> Saving…';

    try {
        const formData = new FormData(profileForm);
        const response = await fetch('<?= base_url("api/patients_update.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.csrf_token) {
            document.getElementById('csrfToken').value = data.csrf_token;
        }

        if (data.success) {
            setEditMode(false);
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Patient record updated', showConfirmButton: false,
                timer: 2500, timerProgressBar: true
            });
        } else {
            saveAlertText.textContent = data.message || 'Could not save changes.';
            saveAlert.classList.add('show');
        }
    } catch (err) {
        saveAlertText.textContent = 'Could not reach the server. Please try again.';
        saveAlert.classList.add('show');
    } finally {
        btnSave.disabled = false;
        btnSaveLabel.textContent = 'Save Changes';
    }
});
<?php endif; ?>
</script>