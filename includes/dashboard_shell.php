<?php
// Needed for the async logout fetch() call below.
generate_csrf_token();

// Clinic name for the printed report header. Loaded defensively: if the
// config file is missing, pages still work and a default name is used.
if (is_file(__DIR__ . '/../config/clinic_config.php')) {
    require_once __DIR__ . '/../config/clinic_config.php';
}
$clinicName = defined('CLINIC_NAME') ? CLINIC_NAME : 'EHR System';

// If a page didn't set a role label, derive it from the logged-in role so
// the role pill (and the printed report) is never blank.
if (empty($role_label)) {
    $role_label = match ($_SESSION['role'] ?? '') {
        'admin'  => 'Administrator',
        'doctor' => 'Doctor',
        'staff'  => 'Staff / Nurse',
        default  => '',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> — EHR System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --indigo:         #4F3FBF;
    --indigo-dark:    #3E30A0;
    --indigo-tint:    #EFECFC;
    --lavender:       #DCD4F7;
    --teal:           #0F3D3E;
    --teal-tint:      #E7F0EF;
    --peach:          #FBD9B8;
    --peach-tint:     #FEF3E7;
    --surface:        #ffffff;
    --bg:             #FAFAFC;
    --border:         #E6E3F2;
    --text-primary:   #1B1730;
    --text-secondary: #675F82;
    --text-muted:     #9A93B3;
    --radius:         14px;
    --radius-sm:      10px;
    --nav-h:          64px;
    --sidebar-w:       224px;
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text-primary);
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
}

.topnav {
    position: fixed; top: 0; left: 0; right: 0; height: var(--nav-h);
    background: var(--surface); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; padding: 0 26px; gap: 16px; z-index: 100;
}
.topnav-brand { display: flex; align-items: center; gap: 10px; }
.topnav-brand-icon { width: 32px; height: 32px; flex-shrink: 0; }
.topnav-brand-text { font-weight: 800; color: var(--text-primary); font-size: 1.02rem; letter-spacing: -0.02em; }

.topnav-spacer { flex: 1; }
.topnav-links { display: flex; align-items: center; gap: 4px; margin-right: 8px; }
.topnav-links a {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 13px; border-radius: 999px;
    font-size: 0.83rem; font-weight: 600; color: var(--text-secondary);
    text-decoration: none; transition: background 0.15s, color 0.15s;
}
.topnav-links a:hover { background: var(--bg); color: var(--text-primary); }
.topnav-links a.active { background: var(--indigo-tint); color: var(--indigo-dark); }

.role-pill {
    font-size: 0.72rem; font-weight: 700; letter-spacing: 0.02em;
    padding: 5px 13px; border-radius: 999px;
    background: var(--indigo-tint); color: var(--indigo-dark);
}
.user-block { display: flex; align-items: center; gap: 10px; text-decoration: none; padding: 4px 8px 4px 4px; border-radius: 999px; transition: background 0.15s; }
.user-block:hover { background: var(--bg); }
.user-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: var(--teal); color: white;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.8rem; flex-shrink: 0;
}
.user-avatar-img {
    width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
    border: 1px solid var(--border);
}
.user-name { font-size: 0.86rem; font-weight: 600; color: var(--text-primary); }

.btn-logout {
    display: flex; align-items: center; gap: 7px;
    background: none; border: 1.5px solid var(--border); border-radius: 999px;
    padding: 8px 15px; font-size: 0.82rem; font-weight: 600; color: var(--text-secondary);
    cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; text-decoration: none;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.btn-logout:hover { background: #fdecea; border-color: #f2c6c3; color: #b3261e; }

/* ── Mobile nav (hamburger + dropdown panel) ── */
.nav-hamburger {
    display: none;
    align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 999px;
    background: none; border: 1.5px solid var(--border); cursor: pointer;
    color: var(--text-secondary); flex-shrink: 0;
}
.nav-hamburger:hover { background: var(--bg); }
.mobile-nav-panel {
    display: none;
    position: fixed; top: var(--nav-h); left: 0; right: 0; z-index: 99;
    background: var(--surface); border-bottom: 1px solid var(--border);
    padding: 10px 16px 16px; box-shadow: 0 8px 20px rgba(27,23,48,0.08);
}
.mobile-nav-panel.open { display: block; }
.mobile-nav-panel a {
    display: block; padding: 11px 12px; border-radius: 8px;
    font-size: 0.9rem; font-weight: 600; color: var(--text-secondary); text-decoration: none;
}
.mobile-nav-panel a:hover, .mobile-nav-panel a.active { background: var(--indigo-tint); color: var(--indigo-dark); }
.mobile-nav-panel .role-pill { display: inline-block; margin: 8px 12px 2px; }

@media (max-width: 860px) {
    .topnav-links { display: none; }
    .role-pill { display: none; }
    .nav-hamburger { display: flex; }
    .topnav { padding: 0 16px; gap: 10px; }
}
@media (max-width: 480px) {
    .topnav-brand-text { display: none; }
    .btn-logout span { display: none; }
    .btn-logout { padding: 9px; }
}

.spinner {
    width: 13px; height: 13px;
    border: 2px solid rgba(103,95,130,0.3);
    border-top-color: var(--indigo);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

.main { margin-top: var(--nav-h); padding: 40px 44px; max-width: 1180px; }

.page-heading { margin-bottom: 30px; }
.page-back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: none; border: 1.5px solid var(--border); border-radius: 999px;
    padding: 6px 14px 6px 12px; margin-bottom: 12px;
    font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);
    cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.page-back-btn:hover { background: var(--indigo-tint); border-color: var(--indigo); color: var(--indigo-dark); }
.page-heading h1 {
    font-family: 'Fraunces', serif;
    font-size: 1.7rem; font-weight: 600; margin-bottom: 6px; letter-spacing: -0.01em;
}
.page-heading p { font-size: 0.88rem; color: var(--text-secondary); }

.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 26px;
    box-shadow: 0 1px 2px rgba(27, 23, 48, 0.04);
}

.placeholder-note {
    display: flex; gap: 11px; align-items: flex-start;
    background: var(--teal-tint); border: 1px solid #cfe4e2; border-radius: var(--radius-sm);
    padding: 14px 16px; font-size: 0.83rem; color: var(--teal); margin-top: 18px;
    line-height: 1.5;
}
.placeholder-note svg { flex-shrink: 0; margin-top: 1px; color: var(--teal); }

.swal2-popup { font-family: 'Plus Jakarta Sans', sans-serif !important; border-radius: 18px !important; }
.swal2-confirm { background: var(--indigo) !important; border-radius: 999px !important; padding: 10px 22px !important; }
.swal2-cancel { border-radius: 999px !important; padding: 10px 22px !important; }
.swal2-title { font-family: 'Fraunces', serif !important; }

/* ── Shared KPI dashboard widgets — used by both staff/dashboard.php
   and doctor/dashboard.php so the two stay visually consistent. ── */
.kpi-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px; margin-bottom: 28px;
}
.kpi-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 20px; position: relative; overflow: hidden;
}
.kpi-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: var(--kpi-accent, var(--indigo));
}
.kpi-icon {
    width: 34px; height: 34px; border-radius: 9px; margin-bottom: 12px;
    display: flex; align-items: center; justify-content: center;
    background: var(--kpi-icon-bg, var(--indigo-tint)); color: var(--kpi-accent, var(--indigo));
}
.kpi-number { font-family: 'Fraunces', serif; font-size: 1.9rem; font-weight: 600; line-height: 1; margin-bottom: 4px; }
.kpi-label { font-size: 0.8rem; color: var(--text-secondary); font-weight: 500; }
.kpi-skeleton { opacity: 0.4; }

.section-block { margin-bottom: 28px; }
.section-block-title {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--text-muted); margin-bottom: 12px;
}

.quick-actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
.quick-action-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 18px; text-align: center; text-decoration: none; color: var(--text-primary);
    transition: box-shadow 0.15s, transform 0.15s, border-color 0.15s;
}
.quick-action-card:hover { box-shadow: 0 8px 20px rgba(27,23,48,0.08); transform: translateY(-2px); border-color: var(--indigo); }
.quick-action-icon {
    width: 40px; height: 40px; border-radius: 10px; background: var(--indigo-tint); color: var(--indigo);
    display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;
}
.quick-action-label { font-size: 0.83rem; font-weight: 600; }

.schedule-list-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.schedule-row { display: flex; align-items: center; gap: 14px; padding: 14px 20px; border-bottom: 1px solid var(--border); }
.schedule-row:last-child { border-bottom: none; }
.schedule-time { font-weight: 700; font-size: 0.85rem; width: 76px; flex-shrink: 0; color: var(--indigo-dark); }
.schedule-patient { flex: 1; }
.schedule-patient-name { font-weight: 600; font-size: 0.87rem; }
.schedule-patient-reason { font-size: 0.78rem; color: var(--text-muted); }
.schedule-status { font-size: 0.72rem; font-weight: 700; padding: 3px 11px; border-radius: 999px; flex-shrink: 0; }
.schedule-status-scheduled { background: var(--indigo-tint); color: var(--indigo-dark); }
.schedule-status-completed { background: var(--teal-tint); color: var(--teal); }
.schedule-status-cancelled { background: #FDECEA; color: #B3261E; }
.schedule-status-no_show { background: var(--peach-tint); color: #B45309; }
.schedule-empty { padding: 32px 20px; text-align: center; color: var(--text-muted); font-size: 0.85rem; }

/* ── Shared password strength meter (assets/js/password-strength.js) ── */
.pw-strength-wrap { margin-top: 8px; margin-bottom: 4px; }
.pw-strength-bar { height: 5px; background: var(--bg); border-radius: 3px; overflow: hidden; }
.pw-strength-fill { height: 100%; width: 0%; border-radius: 3px; transition: width 0.2s ease, background 0.2s ease; background: var(--border); }
.pw-strength-fill.pw-weak   { background: #DC2626; }
.pw-strength-fill.pw-fair   { background: #D97706; }
.pw-strength-fill.pw-good   { background: #2563EB; }
.pw-strength-fill.pw-strong { background: #059669; }
.pw-strength-label { font-size: 0.74rem; font-weight: 700; margin-top: 4px; }
.pw-strength-label.pw-weak   { color: #DC2626; }
.pw-strength-label.pw-fair   { color: #D97706; }
.pw-strength-label.pw-good   { color: #2563EB; }
.pw-strength-label.pw-strong { color: #059669; }
.pw-strength-checklist { list-style: none; margin-top: 8px; display: flex; flex-direction: column; gap: 3px; }
.pw-strength-checklist li {
    font-size: 0.75rem; color: var(--text-muted); padding-left: 18px; position: relative;
}
.pw-strength-checklist li::before {
    content: ''; position: absolute; left: 0; top: 3px; width: 12px; height: 12px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--surface);
}
.pw-strength-checklist li.met { color: var(--teal); }
.pw-strength-checklist li.met::before {
    background: var(--teal); border-color: var(--teal);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
    background-size: 8px; background-repeat: no-repeat; background-position: center;
}

/* ── Shared report widgets — bar chart + date-range toolbar,
   used by staff/doctor/admin reports.php so they stay consistent. ── */
.report-toolbar { display: flex; align-items: flex-end; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.report-toolbar .field { margin-bottom: 0; }
.report-toolbar label { display: block; font-size: 0.76rem; font-weight: 600; margin-bottom: 5px; color: var(--text-secondary); }
.report-toolbar input[type="date"] {
    padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none;
}
.report-toolbar input[type="date"]:focus { border-color: var(--indigo); background: var(--surface); }

.bar-chart-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.bar-chart-row:last-child { margin-bottom: 0; }
.bar-chart-label { width: 120px; font-size: 0.82rem; color: var(--text-secondary); flex-shrink: 0; }
.bar-chart-track { flex: 1; height: 11px; background: var(--bg); border-radius: 6px; overflow: hidden; }
.bar-chart-fill { height: 100%; border-radius: 6px; transition: width 0.3s ease; }
.bar-chart-value { width: 34px; text-align: right; font-size: 0.82rem; font-weight: 700; flex-shrink: 0; }

.report-note {
    display: flex; gap: 9px; align-items: flex-start;
    background: var(--peach-tint); color: #B45309; border-radius: 8px;
    padding: 11px 13px; font-size: 0.79rem; line-height: 1.4; margin-bottom: 16px;
}
.report-note svg { flex-shrink: 0; margin-top: 1px; }

@media (max-width: 640px) {
    .main { padding: 28px 20px; }
    .user-name { display: none; }
}

/* ── Report export (PDF via the browser's print dialog) ──
   The button only renders on pages that set $report_export = true. */
.page-heading-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.btn-export {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--surface); color: var(--indigo-dark);
    border: 1.5px solid var(--indigo); border-radius: 999px;
    padding: 9px 18px; font-size: 0.84rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; white-space: nowrap;
    transition: background 0.15s;
}
.btn-export:hover { background: var(--indigo-tint); }

/* Print-only pieces: hidden on screen, shown only when printing. */
.print-report-header, .print-report-footer { display: none; }
.prh-brand { display: flex; align-items: center; gap: 10px; }
.prh-clinic { font-family: 'Fraunces', serif; font-size: 19px; font-weight: 600; color: #1B1730; }
.prh-sub { font-size: 9.5px; color: #675F82; letter-spacing: 0.08em; text-transform: uppercase; margin-top: 1px; }
.prh-title {
    font-family: 'Fraunces', serif; font-size: 24px; font-weight: 600; color: #1B1730;
    margin: 18px 0 10px; padding-bottom: 8px; border-bottom: 2px solid #4F3FBF;
}
.prh-meta { border-collapse: collapse; font-size: 11.5px; margin-bottom: 4px; }
.prh-meta td { padding: 2px 18px 2px 0; color: #1B1730; }
.prh-meta td:first-child { color: #675F82; font-weight: 700; }

@page { size: A4; margin: 16mm 14mm 20mm; }

@media print {
    /* Browsers drop backgrounds when printing unless told otherwise —
       without this the bar charts and badges would print blank. */
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    body { background: #ffffff; color: #1B1730; font-size: 12px; }

    /* Hide everything that only makes sense on screen. */
    .topnav, .mobile-nav-panel, .page-heading, .report-toolbar,
    .btn-export, .swal2-container { display: none !important; }

    .main { margin: 0; padding: 0; max-width: none; }

    .print-report-header { display: block; margin-bottom: 20px; }

    /* Fixed elements repeat on every printed page, so this confidentiality
       line appears at the bottom of each page of the report. */
    .print-report-footer {
        display: block; position: fixed; bottom: 0; left: 0; right: 0;
        font-size: 9px; color: #675F82; text-align: center;
        border-top: 1px solid #E6E3F2; padding-top: 6px;
    }

    .card, .kpi-card, .schedule-list-card { box-shadow: none; }
    .card, .kpi-card { break-inside: avoid; }
    .section-block-title { break-after: avoid; }
    .report-note { break-inside: avoid; }

    /* Long tables: repeat the header row on each page, never split a row. */
    thead { display: table-header-group; }
    tr { break-inside: avoid; }

    a { color: inherit; text-decoration: none; }
}
</style>
</head>
<body>

<nav class="topnav">
    <div class="topnav-brand">
        <div class="topnav-brand-icon">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                <rect x="2" y="2" width="36" height="36" rx="11" fill="#4F3FBF"/>
                <path d="M13 20h4l2.5-7 5 14L27 20h4" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <span class="topnav-brand-text">EHR System</span>
    </div>
    <div class="topnav-spacer"></div>
    <?php if (!empty($nav_links)): ?>
    <div class="topnav-links">
        <?php foreach ($nav_links as $link): ?>
            <a href="<?= htmlspecialchars($link['url']) ?>" class="<?= !empty($link['active']) ? 'active' : '' ?>"><?= htmlspecialchars($link['label']) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <span class="role-pill"><?= htmlspecialchars($role_label) ?></span>

    <?php if (!empty($nav_links)): ?>
    <button type="button" class="nav-hamburger" id="btnMobileNav" aria-label="Open menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <?php endif; ?>

    <a href="<?= base_url('account.php') ?>" class="user-block" title="My Account">
        <?php if (!empty($_SESSION['avatar_path'])): ?>
            <img src="<?= base_url(htmlspecialchars($_SESSION['avatar_path'])) ?>" alt="" class="user-avatar-img">
        <?php else: ?>
            <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($_SESSION['full_name'], 0, 1))) ?></div>
        <?php endif; ?>
        <span class="user-name"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    </a>
    <a href="#" class="btn-logout" id="btnLogout" onclick="confirmLogout(event)">
        <svg id="logoutIcon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span id="logoutLabel">Log Out</span>
    </a>
    <input type="hidden" id="logoutCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
</nav>

<?php if (!empty($nav_links)): ?>
<div class="mobile-nav-panel" id="mobileNavPanel">
    <?php foreach ($nav_links as $link): ?>
        <a href="<?= htmlspecialchars($link['url']) ?>" class="<?= !empty($link['active']) ? 'active' : '' ?>"><?= htmlspecialchars($link['label']) ?></a>
    <?php endforeach; ?>
    <span class="role-pill"><?= htmlspecialchars($role_label) ?></span>
</div>
<?php endif; ?>

<main class="main">
    <?php if (!empty($report_export)): ?>
    <!-- Print-only report header: invisible on screen, shown on the exported PDF. -->
    <div class="print-report-header">
        <div class="prh-brand">
            <svg width="38" height="38" viewBox="0 0 40 40" fill="none">
                <rect x="2" y="2" width="36" height="36" rx="11" fill="#4F3FBF"/>
                <path d="M13 20h4l2.5-7 5 14L27 20h4" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
            <div>
                <div class="prh-clinic"><?= htmlspecialchars($clinicName) ?></div>
                <div class="prh-sub">Clinic Records &amp; Reports</div>
            </div>
        </div>
        <div class="prh-title"><?= htmlspecialchars($page_title) ?></div>
        <table class="prh-meta">
            <tr><td>Period</td><td id="printPeriod">—</td></tr>
            <tr><td>Generated</td><td id="printGenerated">—</td></tr>
            <tr><td>Prepared by</td><td><?= htmlspecialchars($_SESSION['full_name']) ?><?= $role_label !== '' ? ' — ' . htmlspecialchars($role_label) : '' ?></td></tr>
        </table>
    </div>
    <?php endif; ?>

    <div class="page-heading">
        <?php if (!empty($show_back)): ?>
        <button type="button" class="page-back-btn" onclick="history.back()" aria-label="Go back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back
        </button>
        <?php endif; ?>
        <div class="page-heading-row">
            <div class="page-heading-text">
                <h1><?= htmlspecialchars($page_title) ?></h1>
                <?php if (!empty($page_subtitle)): ?>
                <p><?= htmlspecialchars($page_subtitle) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($report_export)): ?>
            <button type="button" class="btn-export" id="btnExportPdf" title="Opens the print window — choose &quot;Save as PDF&quot; as the destination">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export PDF
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?= $page_content ?>

    <?php if (!empty($report_export)): ?>
    <div class="print-report-footer">Confidential — contains patient and clinic information. For authorized clinic use only.</div>
    <?php endif; ?>
</main>

<script>
/* ── Report export: fills in the print header, then opens the print dialog
      (the user picks "Save as PDF" as the destination). Only does anything
      on pages that set $report_export = true. ── */
const btnExportPdf = document.getElementById('btnExportPdf');

function formatLongDate(isoDate) {
    // Adding a time makes 'YYYY-MM-DD' parse as a LOCAL date (otherwise it
    // can shift by a day depending on the timezone).
    return new Date(isoDate + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function fillPrintMeta() {
    const periodEl    = document.getElementById('printPeriod');
    const generatedEl = document.getElementById('printGenerated');
    if (!periodEl || !generatedEl) return;

    const from = document.getElementById('dateFrom');
    const to   = document.getElementById('dateTo');
    const now  = new Date();

    if (from && to && from.value && to.value) {
        periodEl.textContent = formatLongDate(from.value) + ' – ' + formatLongDate(to.value);
    } else {
        // Reports with no date filter (e.g. the admin system totals).
        periodEl.textContent = 'All time (as of ' + now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + ')';
    }
    generatedEl.textContent = now.toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

if (btnExportPdf) {
    // The browser uses the page title as the default PDF file name, so give
    // it a clean one while printing, e.g. "Reports - 2026-10-20".
    const originalTitle = document.title;
    window.addEventListener('beforeprint', () => {
        fillPrintMeta(); // runs for the button AND for Ctrl+P, so it's never stale
        document.title = originalTitle.replace(' — EHR System', '') + ' - ' + new Date().toISOString().slice(0, 10);
    });
    window.addEventListener('afterprint', () => { document.title = originalTitle; });

    btnExportPdf.addEventListener('click', () => {
        // Don't export a half-loaded report (numbers still showing "—").
        if (document.querySelector('.kpi-skeleton')) {
            Swal.fire({ icon: 'info', title: 'Still loading', text: 'Please wait for the report to finish loading, then try again.' });
            return;
        }
        fillPrintMeta();
        window.print();
    });
}

/* ── Mobile nav hamburger toggle ── */
const btnMobileNav = document.getElementById('btnMobileNav');
const mobileNavPanel = document.getElementById('mobileNavPanel');
if (btnMobileNav && mobileNavPanel) {
    btnMobileNav.addEventListener('click', (e) => {
        e.stopPropagation();
        mobileNavPanel.classList.toggle('open');
    });
    // Close when a link inside it is tapped, or when tapping anywhere outside.
    mobileNavPanel.addEventListener('click', (e) => {
        if (e.target.tagName === 'A') mobileNavPanel.classList.remove('open');
    });
    document.addEventListener('click', (e) => {
        if (mobileNavPanel.classList.contains('open') &&
            !mobileNavPanel.contains(e.target) &&
            !btnMobileNav.contains(e.target)) {
            mobileNavPanel.classList.remove('open');
        }
    });
}

/* =====================================================================
   ASYNCHRONOUS LOGOUT
   ---------------------------------------------------------------------
   Same pattern as the login page: fetch() + async/await instead of a
   normal link navigation. The confirmation dialog and the button's
   own state stay interactive while the request is in flight — the
   page doesn't freeze waiting for the server to destroy the session.
   ===================================================================== */
async function confirmLogout(e) {
    e.preventDefault();

    const result = await Swal.fire({
        title: 'Log out?',
        text: 'You will be returned to the sign-in page.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    const btn   = document.getElementById('btnLogout');
    const label = document.getElementById('logoutLabel');
    const icon  = document.getElementById('logoutIcon');

    btn.style.pointerEvents = 'none';
    label.textContent = 'Logging out';
    icon.outerHTML = '<span class="spinner" id="logoutIcon" style="border-color: rgba(179,38,30,0.3); border-top-color: #b3261e;"></span>';

    try {
        const formData = new FormData();
        formData.append('csrf_token', document.getElementById('logoutCsrfToken').value);

        const response = await fetch('<?= base_url("api/logout.php") ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            window.location.href = '<?= base_url("") ?>' + data.redirect;
        } else {
            // Fall back to the plain non-JS logout link if something
            // unexpected happens, rather than leaving the user stuck.
            window.location.href = '<?= base_url("logout.php") ?>';
        }
    } catch (err) {
        window.location.href = '<?= base_url("logout.php") ?>';
    }
}
</script>
</body>
</html>