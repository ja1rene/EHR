<?php
require_once __DIR__ . "/includes/auth.php";
if (is_file(__DIR__ . '/config/clinic_config.php')) {
    require_once __DIR__ . '/config/clinic_config.php';
}
// Clinical content: staff and doctors only. Admins are deliberately excluded.
require_role(['staff', 'doctor']);

$clinicName = defined('CLINIC_NAME') ? CLINIC_NAME : 'EHR System';
$role       = $_SESSION['role'];
$roleLabel  = $role === 'doctor' ? 'Doctor' : 'Staff / Nurse';

/**
 * Text in the database was already HTML-escaped when it was saved
 * (clean_input), so decode it first and escape ONCE for output.
 * Escaping it a second time would show things like &#039; on the page.
 */
function h($value): string {
    return htmlspecialchars(html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
}

/** 36.50 -> "36.5", 60.0 -> "60" */
function fmtNum($value): string {
    $s = number_format((float)$value, 1, '.', '');
    return rtrim(rtrim($s, '0'), '.');
}

$id = (int)($_GET['id'] ?? 0);
$patient = null;

if ($id > 0) {
    // Staff can open archived records; doctors only see active patients
    // (same rule as the patient profile).
    $statusClause = ($role === 'staff') ? '' : " AND status = 'active'";
    $stmt = $conn->prepare(
        "SELECT patient_id, patient_code, first_name, middle_name, last_name, date_of_birth, sex,
                civil_status, address, contact_number, emergency_contact_name,
                emergency_contact_number, blood_type, allergies, medical_conditions, status
         FROM patients WHERE patient_id = ?" . $statusClause
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
}

$backUrl = base_url($role . '/patients.php');

if (!$patient) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient not found</title>
    <style>body{font-family:'Segoe UI',Arial,sans-serif;background:#FAFAFC;color:#1B1730;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;text-align:center}
    a{display:inline-block;margin-top:16px;background:#4F3FBF;color:#fff;text-decoration:none;padding:10px 22px;border-radius:999px;font-weight:700;font-size:.88rem}</style></head>
    <body><div><h1 style="font-size:1.2rem">Patient not found</h1>
    <p style="color:#675F82;margin-top:8px">This record doesn't exist, or you don't have permission to view it.</p>
    <a href="<?= htmlspecialchars($backUrl) ?>">Back to patients</a></div></body></html>
    <?php
    exit();
}

// Recent diagnoses (free text on each consultation, newest first).
$dxStmt = $conn->prepare(
    "SELECT c.created_at, c.chief_complaint, c.diagnosis, u.full_name AS doctor_name
     FROM consultations c JOIN users u ON c.doctor_id = u.user_id
     WHERE c.patient_id = ? AND c.diagnosis IS NOT NULL AND c.diagnosis != ''
     ORDER BY c.created_at DESC LIMIT 5"
);
$dxStmt->bind_param("i", $id);
$dxStmt->execute();
$diagnoses = $dxStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$dxStmt->close();

// Recent medications: the last 3 prescriptions, flattened into one list.
$rxStmt = $conn->prepare(
    "SELECT p.prescription_id, p.created_at, u.full_name AS doctor_name
     FROM prescriptions p JOIN users u ON p.doctor_id = u.user_id
     WHERE p.patient_id = ? ORDER BY p.created_at DESC LIMIT 3"
);
$rxStmt->bind_param("i", $id);
$rxStmt->execute();
$prescriptions = $rxStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$rxStmt->close();

$medications = [];
foreach ($prescriptions as $rx) {
    $itemStmt = $conn->prepare(
        "SELECT medicine_name, dosage, frequency, duration, instructions
         FROM prescription_items WHERE prescription_id = ?"
    );
    $itemStmt->bind_param("i", $rx['prescription_id']);
    $itemStmt->execute();
    foreach ($itemStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $item) {
        $item['prescribed_at'] = $rx['created_at'];
        $item['doctor_name']   = $rx['doctor_name'];
        $medications[] = $item;
    }
    $itemStmt->close();
}

// Latest vitals on file.
$vStmt = $conn->prepare(
    "SELECT blood_pressure, temperature_c, pulse_rate, respiratory_rate, oxygen_saturation,
            height_cm, weight_kg, recorded_at
     FROM vitals WHERE patient_id = ? ORDER BY recorded_at DESC LIMIT 1"
);
$vStmt->bind_param("i", $id);
$vStmt->execute();
$vitals = $vStmt->get_result()->fetch_assoc() ?: null;
$vStmt->close();

$vitalParts = [];
if ($vitals) {
    if (!empty($vitals['blood_pressure']))                $vitalParts[] = 'BP ' . $vitals['blood_pressure'];
    if ($vitals['temperature_c'] !== null)                $vitalParts[] = 'Temp ' . fmtNum($vitals['temperature_c']) . ' °C';
    if ($vitals['pulse_rate'] !== null)                   $vitalParts[] = 'Pulse ' . (int)$vitals['pulse_rate'] . ' bpm';
    if ($vitals['respiratory_rate'] !== null)             $vitalParts[] = 'Resp ' . (int)$vitals['respiratory_rate'] . '/min';
    if ($vitals['oxygen_saturation'] !== null)            $vitalParts[] = 'O₂ ' . (int)$vitals['oxygen_saturation'] . '%';
    if ($vitals['height_cm'] !== null)                    $vitalParts[] = 'Height ' . fmtNum($vitals['height_cm']) . ' cm';
    if ($vitals['weight_kg'] !== null)                    $vitalParts[] = 'Weight ' . fmtNum($vitals['weight_kg']) . ' kg';
}

$age = (new DateTime($patient['date_of_birth']))->diff(new DateTime('today'))->y;
$fullName = trim($patient['last_name'] . ', ' . $patient['first_name'] . ' ' . ($patient['middle_name'] ?? ''));
$niceDob  = date('F j, Y', strtotime($patient['date_of_birth']));
$emergency = trim(($patient['emergency_contact_name'] ?? '') . ' ' . (($patient['emergency_contact_number'] ?? '') !== '' ? '(' . $patient['emergency_contact_number'] . ')' : ''));
$hasAllergies  = trim((string)$patient['allergies']) !== '';
$hasConditions = trim((string)$patient['medical_conditions']) !== '';

// Producing a clinical summary is worth an audit entry.
log_action('patient_summary_viewed', 'Patients', (string)$id, "Opened printable summary for {$patient['patient_code']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Summary - <?= h($patient['patient_code']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --indigo: #4F3FBF; --indigo-dark: #3E30A0; --indigo-tint: #EFECFC;
    --teal: #0F3D3E; --teal-tint: #E7F0EF;
    --surface: #ffffff; --bg: #F3F2F8; --border: #E6E3F2;
    --text: #1B1730; --muted: #675F82; --faint: #9A93B3;
}
body { font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif; background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; }

/* ── On-screen toolbar (never printed) ── */
.toolbar { max-width: 794px; margin: 22px auto 16px; padding: 0 14px; }
.toolbar-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
    padding: 16px 18px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
}
.tb-field { flex: 1; min-width: 190px; }
.tb-field label { display: block; font-size: 0.74rem; font-weight: 700; color: var(--muted); margin-bottom: 5px; }
.tb-field input, .tb-field textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.85rem; font-family: inherit; background: #FAFAFC; outline: none; resize: vertical;
}
.tb-field input:focus, .tb-field textarea:focus { border-color: var(--indigo); background: #fff; }
.tb-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 18px; border-radius: 999px; font-size: 0.84rem; font-weight: 700; cursor: pointer; text-decoration: none; font-family: inherit; white-space: nowrap; }
.btn-primary { background: var(--indigo); color: #fff; border: none; }
.btn-primary:hover { background: var(--indigo-dark); }
.btn-secondary { background: #fff; color: var(--muted); border: 1.5px solid var(--border); }
.btn-secondary:hover { background: var(--indigo-tint); color: var(--indigo-dark); border-color: var(--indigo); }
.tb-hint { width: 100%; font-size: 0.74rem; color: var(--faint); }

/* ── The sheet ── */
.paper {
    width: 794px; max-width: calc(100% - 28px); margin: 0 auto 40px; background: #fff;
    border: 1px solid var(--border); border-radius: 6px; box-shadow: 0 6px 24px rgba(27,23,48,0.08);
    padding: 34px 38px 28px; font-size: 12px; line-height: 1.45; word-break: break-word;
}
.doc-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
.doc-brand { display: flex; align-items: center; gap: 10px; }
.doc-clinic { font-family: 'Fraunces', serif; font-size: 19px; font-weight: 600; }
.doc-sub { font-size: 9.5px; color: var(--muted); letter-spacing: 0.08em; text-transform: uppercase; margin-top: 1px; }
.doc-title-block { text-align: right; }
.doc-title { font-family: 'Fraunces', serif; font-size: 22px; font-weight: 600; line-height: 1.1; }
.doc-subtitle { font-size: 10px; font-weight: 700; color: var(--indigo); letter-spacing: 0.12em; text-transform: uppercase; margin-top: 4px; }
.doc-rule { height: 2px; background: var(--indigo); margin: 14px 0 16px; }

.section { margin-bottom: 14px; }
.section-label { font-size: 9.5px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: var(--indigo); margin-bottom: 6px; padding-bottom: 3px; border-bottom: 1px solid var(--border); }

.info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px 16px; }
.info .k { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
.info .v { display: block; font-size: 12.5px; font-weight: 600; margin-top: 1px; }
.span2 { grid-column: span 2; }
.span4 { grid-column: span 4; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.alert-box { border: 1.5px solid #f2c6c3; background: #FDECEA; border-radius: 6px; padding: 8px 11px; }
.alert-box .box-label { color: #B3261E; }
.neutral-box { border: 1px solid var(--border); background: #FAFAFC; border-radius: 6px; padding: 8px 11px; }
.box-label { font-size: 9.5px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 3px; color: var(--muted); }
.box-text { font-size: 12.5px; font-weight: 600; white-space: pre-wrap; }
.box-text.none { font-weight: 500; color: var(--faint); }

.doc-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
.doc-table th { text-align: left; font-size: 9px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: var(--muted); padding: 4px 6px; border-bottom: 1.5px solid var(--border); }
.doc-table td { padding: 5px 6px; border-bottom: 1px solid var(--border); vertical-align: top; }
.doc-table tr:last-child td { border-bottom: none; }
.empty-line { font-size: 11.5px; color: var(--faint); }
.vitals-line { font-size: 12px; font-weight: 600; }
.vitals-date { font-size: 10.5px; color: var(--muted); font-weight: 500; margin-top: 2px; }

.ref-row { display: flex; align-items: flex-end; gap: 10px; margin-bottom: 8px; }
.ref-row .k { font-size: 10.5px; font-weight: 700; color: var(--muted); white-space: nowrap; }
.ref-row .line { flex: 1; border-bottom: 1px solid #8a849e; min-height: 20px; padding: 0 4px 1px; font-weight: 600; white-space: pre-wrap; }
.ref-row .line.multi { min-height: 46px; }

.sign-row { display: grid; grid-template-columns: 1.4fr 1fr; gap: 28px; margin-top: 26px; }
.sign-box { border-top: 1px solid #8a849e; padding-top: 4px; font-size: 10px; color: var(--muted); }

.doc-footer { margin-top: 18px; padding-top: 8px; border-top: 1px solid var(--border); font-size: 9.5px; color: var(--muted); display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.tag-archived { display: inline-block; background: #FEF3E7; color: #B45309; font-size: 9px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; padding: 1px 7px; border-radius: 999px; margin-left: 6px; vertical-align: middle; }

@media (max-width: 640px) {
    .paper { padding: 22px 18px; }
    .info-grid { grid-template-columns: 1fr 1fr; }
    .span2 { grid-column: span 2; }
    .span4 { grid-column: span 2; }
    .two-col, .sign-row { grid-template-columns: 1fr; }
    .doc-header { flex-direction: column; }
    .doc-title-block { text-align: left; }
}

@page { size: A4; margin: 12mm; }
@media print {
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { background: #fff; }
    .toolbar { display: none !important; }
    .paper { width: auto; max-width: none; margin: 0; border: none; border-radius: 0; box-shadow: none; padding: 0; }
    .section, .two-col, .sign-row, .alert-box, .neutral-box { break-inside: avoid; }
    .info-grid { grid-template-columns: repeat(4, 1fr); }
    .two-col { grid-template-columns: 1fr 1fr; }
    .sign-row { grid-template-columns: 1.4fr 1fr; }
}
</style>
</head>
<body>

<div class="toolbar">
    <div class="toolbar-card">
        <div class="tb-field">
            <label for="refTo">Referred to (optional)</label>
            <input type="text" id="refTo" maxlength="120" placeholder="e.g. Dr. Reyes — Cardiology, Provincial Hospital">
        </div>
        <div class="tb-field">
            <label for="refReason">Reason / notes (optional)</label>
            <textarea id="refReason" rows="2" maxlength="400" placeholder="e.g. Persistent elevated blood pressure; for specialist evaluation"></textarea>
        </div>
        <div class="tb-actions">
            <a class="btn btn-secondary" href="<?= htmlspecialchars(base_url($role . '/patient_view.php')) ?>?id=<?= (int)$patient['patient_id'] ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to profile
            </a>
            <button type="button" class="btn btn-primary" id="btnPrint">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print / Save as PDF
            </button>
        </div>
        <div class="tb-hint">The referral details are typed here only to appear on the printout — they are not saved. In the print window, choose "Save as PDF" as the destination.</div>
    </div>
</div>

<div class="paper">

    <div class="doc-header">
        <div class="doc-brand">
            <svg width="38" height="38" viewBox="0 0 40 40" fill="none">
                <rect x="2" y="2" width="36" height="36" rx="11" fill="#4F3FBF"/>
                <path d="M13 20h4l2.5-7 5 14L27 20h4" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
            <div>
                <div class="doc-clinic"><?= h($clinicName) ?></div>
                <div class="doc-sub">Clinic Records</div>
            </div>
        </div>
        <div class="doc-title-block">
            <div class="doc-title">Patient Summary</div>
            <div class="doc-subtitle">Referral Slip</div>
        </div>
    </div>
    <div class="doc-rule"></div>

    <div class="section">
        <div class="section-label">Patient</div>
        <div class="info-grid">
            <div class="info span2"><span class="k">Name</span><span class="v"><?= h($fullName) ?><?php if ($patient['status'] === 'archived'): ?><span class="tag-archived">Archived</span><?php endif; ?></span></div>
            <div class="info"><span class="k">Patient ID</span><span class="v"><?= h($patient['patient_code']) ?></span></div>
            <div class="info"><span class="k">Blood type</span><span class="v"><?= h($patient['blood_type'] === 'unknown' ? 'Unknown' : $patient['blood_type']) ?></span></div>

            <div class="info span2"><span class="k">Date of birth</span><span class="v"><?= h($niceDob) ?> (<?= (int)$age ?> yrs)</span></div>
            <div class="info"><span class="k">Sex</span><span class="v"><?= h(ucfirst($patient['sex'])) ?></span></div>
            <div class="info"><span class="k">Civil status</span><span class="v"><?= $patient['civil_status'] ? h(ucfirst($patient['civil_status'])) : '—' ?></span></div>

            <div class="info span2"><span class="k">Contact number</span><span class="v"><?= $patient['contact_number'] ? h($patient['contact_number']) : '—' ?></span></div>
            <div class="info span2"><span class="k">Emergency contact</span><span class="v"><?= $emergency !== '' ? h($emergency) : '—' ?></span></div>

            <div class="info span4"><span class="k">Address</span><span class="v"><?= $patient['address'] ? h($patient['address']) : '—' ?></span></div>
        </div>
    </div>

    <div class="section two-col">
        <div class="<?= $hasAllergies ? 'alert-box' : 'neutral-box' ?>">
            <div class="box-label">Allergies</div>
            <?php if ($hasAllergies): ?>
                <div class="box-text"><?= h($patient['allergies']) ?></div>
            <?php else: ?>
                <div class="box-text none">None recorded</div>
            <?php endif; ?>
        </div>
        <div class="neutral-box">
            <div class="box-label">Known medical conditions</div>
            <?php if ($hasConditions): ?>
                <div class="box-text"><?= h($patient['medical_conditions']) ?></div>
            <?php else: ?>
                <div class="box-text none">None recorded</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-label">Recent diagnoses</div>
        <?php if (empty($diagnoses)): ?>
            <div class="empty-line">No diagnoses recorded.</div>
        <?php else: ?>
        <table class="doc-table">
            <thead><tr><th style="width:17%">Date</th><th>Diagnosis</th><th style="width:28%">Chief complaint</th><th style="width:22%">Doctor</th></tr></thead>
            <tbody>
            <?php foreach ($diagnoses as $dx): ?>
                <tr>
                    <td><?= h(date('M j, Y', strtotime($dx['created_at']))) ?></td>
                    <td><strong><?= h($dx['diagnosis']) ?></strong></td>
                    <td><?= $dx['chief_complaint'] ? h($dx['chief_complaint']) : '—' ?></td>
                    <td><?= h($dx['doctor_name']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-label">Recent medications</div>
        <?php if (empty($medications)): ?>
            <div class="empty-line">No prescriptions recorded.</div>
        <?php else: ?>
        <table class="doc-table">
            <thead><tr><th>Medicine</th><th style="width:14%">Dosage</th><th style="width:16%">Frequency</th><th style="width:13%">Duration</th><th style="width:17%">Prescribed</th></tr></thead>
            <tbody>
            <?php foreach ($medications as $med): ?>
                <tr>
                    <td>
                        <strong><?= h($med['medicine_name']) ?></strong>
                        <?php if (!empty($med['instructions'])): ?><br><span style="color:#675F82"><?= h($med['instructions']) ?></span><?php endif; ?>
                    </td>
                    <td><?= $med['dosage'] ? h($med['dosage']) : '—' ?></td>
                    <td><?= $med['frequency'] ? h($med['frequency']) : '—' ?></td>
                    <td><?= $med['duration'] ? h($med['duration']) : '—' ?></td>
                    <td><?= h(date('M j, Y', strtotime($med['prescribed_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-label">Latest vitals</div>
        <?php if (empty($vitalParts)): ?>
            <div class="empty-line">No vitals recorded.</div>
        <?php else: ?>
            <div class="vitals-line"><?= h(implode('   ·   ', $vitalParts)) ?></div>
            <div class="vitals-date">Recorded <?= h(date('M j, Y g:i A', strtotime($vitals['recorded_at']))) ?></div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-label">Referral</div>
        <div class="ref-row"><span class="k">Referred to</span><span class="line" id="printRefTo"></span></div>
        <div class="ref-row"><span class="k">Reason / notes</span><span class="line multi" id="printRefReason"></span></div>
    </div>

    <div class="sign-row">
        <div class="sign-box">Referring clinician (name &amp; signature)</div>
        <div class="sign-box">Date</div>
    </div>

    <div class="doc-footer">
        <span>Prepared by <?= h($_SESSION['full_name']) ?> (<?= h($roleLabel) ?>) on <?= h(date('F j, Y \a\t g:i A')) ?></span>
        <span>Confidential — for authorized clinical use only.</span>
    </div>

</div>

<script>
// Mirror the toolbar fields onto the sheet. textContent (not innerHTML), so
// whatever is typed can never be interpreted as markup.
const refTo = document.getElementById('refTo');
const refReason = document.getElementById('refReason');
const printRefTo = document.getElementById('printRefTo');
const printRefReason = document.getElementById('printRefReason');

refTo.addEventListener('input', () => { printRefTo.textContent = refTo.value; });
refReason.addEventListener('input', () => { printRefReason.textContent = refReason.value; });

document.getElementById('btnPrint').addEventListener('click', () => window.print());
</script>
</body>
</html>