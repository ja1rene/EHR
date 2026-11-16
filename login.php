<?php
require_once "includes/auth.php"; // db.php + security.php + base_url()/log helpers

generate_csrf_token();

$inactiveMsg = "";
$timeoutMsg  = "";

if (isset($_GET['inactive'])) {
    $inactiveMsg = "Your account is inactive. Please contact the system administrator.";
}
if (isset($_GET['timeout'])) {
    $timeoutMsg = "You were logged out due to inactivity. Please log in again.";
}

// Already logged in? Send straight to the right dashboard.
if (isset($_SESSION["user_id"])) {
    $dest = match ($_SESSION["role"] ?? null) {
        'admin'  => 'admin/dashboard.php',
        'doctor' => 'doctor/dashboard.php',
        'staff'  => 'staff/dashboard.php',
        default  => 'login.php',
    };
    header("Location: $dest");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — EHR System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
    --surface:        #ffffff;
    --bg:             #FBFAFF;
    --border:         #E6E3F2;
    --text-primary:   #1B1730;
    --text-secondary: #675F82;
    --text-muted:     #9A93B3;
    --red:            #B3261E;
    --red-bg:         #FDECEA;
    --amber:          #8A5A00;
    --amber-bg:       #FDF3E0;
    --radius:         16px;
    --radius-sm:      10px;
    --nav-h:          76px;
}

html, body { height: 100%; }

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text-primary);
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
    position: relative;
    overflow-x: hidden;
}

.blob-field { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
.blob { position: absolute; opacity: 0.5; max-width: 40vw; }
.blob svg { width: 100%; height: 100%; display: block; }
.blob-a { width: 480px; top: -140px; right: -180px; animation: drift-a 22s ease-in-out infinite; }
.blob-b { width: 360px; bottom: -120px; left: -140px; animation: drift-b 26s ease-in-out infinite; }
.blob-c { width: 220px; top: 40%; right: 4%; opacity: 0.32; animation: drift-c 18s ease-in-out infinite; }
.blob-d { width: 200px; top: 18%; left: 2%; opacity: 0.28; animation: drift-b 20s ease-in-out infinite reverse; }

@keyframes drift-a { 0%,100% { transform: translate(0,0) rotate(0deg); } 50% { transform: translate(-24px,28px) rotate(8deg); } }
@keyframes drift-b { 0%,100% { transform: translate(0,0) rotate(0deg); } 50% { transform: translate(20px,-22px) rotate(-6deg); } }
@keyframes drift-c { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-14px,16px) scale(1.06); } }

.topnav {
    position: relative; z-index: 2;
    display: flex; align-items: center;
    height: var(--nav-h); padding: 0 56px;
    border-bottom: 1px solid var(--border);
}
.nav-brand { display: flex; align-items: center; gap: 10px; }
.nav-brand-icon { width: 32px; height: 32px; flex-shrink: 0; }
.nav-brand-text { font-size: 1.05rem; font-weight: 800; letter-spacing: -0.02em; }
.nav-spacer { flex: 1; }
.nav-link { font-size: 0.87rem; font-weight: 600; color: var(--text-secondary); text-decoration: none; margin-right: 32px; transition: color 0.15s; }
.nav-link:hover { color: var(--indigo); }
.nav-cta {
    display: flex; align-items: center; gap: 8px;
    background: var(--indigo); color: white; text-decoration: none;
    font-size: 0.86rem; font-weight: 700;
    padding: 10px 20px; border-radius: 999px; border: none; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.15s;
}
.nav-cta:hover { background: var(--indigo-dark); }

.hero {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; align-items: center; text-align: center;
    padding: 80px 24px 40px;
    min-height: calc(100vh - var(--nav-h) - 70px);
    justify-content: center;
}
.hero-eyebrow {
    display: inline-flex; align-items: center; gap: 9px;
    font-size: 0.9rem; font-weight: 700; color: var(--indigo-dark);
    background: var(--indigo-tint);
    padding: 10px 20px 10px 14px; border-radius: 999px;
    margin-bottom: 28px;
}
.hero-eyebrow .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--indigo); }
.hero-headline {
    font-family: 'Fraunces', serif;
    font-size: 4rem; font-weight: 600; line-height: 1.14;
    letter-spacing: -0.02em; max-width: 780px; margin-bottom: 22px;
}
.hero-headline em { font-style: normal; color: var(--indigo); }
.hero-sub {
    font-size: 1.02rem; color: var(--text-secondary);
    max-width: 480px; line-height: 1.6; margin-bottom: 40px;
}

.trust-strip { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
.trust-chip {
    display: flex; align-items: center; gap: 8px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 999px; padding: 9px 16px 9px 12px;
    font-size: 0.82rem; font-weight: 600; color: var(--text-secondary);
}
.trust-chip .chip-icon { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.chip-icon.c1 { background: var(--indigo-tint); color: var(--indigo); }
.chip-icon.c2 { background: var(--teal-tint); color: var(--teal); }
.chip-icon.c3 { background: #FEF3E7; color: #B45309; }

.modal-overlay {
    position: fixed; inset: 0; z-index: 50;
    background: rgba(27, 23, 48, 0.35);
    backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
    display: none; align-items: center; justify-content: center;
    padding: 24px; opacity: 0; transition: opacity 0.2s ease;
}
.modal-overlay.open { display: flex; }
.modal-overlay.show { opacity: 1; }

.form-card {
    position: relative; z-index: 1;
    width: 100%; max-width: 420px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: 0 30px 70px -20px rgba(27, 23, 48, 0.35);
    padding: 38px 38px 32px;
    transform: translateY(14px) scale(0.97); opacity: 0;
    transition: transform 0.22s ease, opacity 0.22s ease;
}
.modal-overlay.show .form-card { transform: translateY(0) scale(1); opacity: 1; }

.modal-close {
    position: absolute; top: 18px; right: 18px;
    width: 30px; height: 30px; border-radius: 50%;
    border: none; background: var(--bg); color: var(--text-muted);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background 0.15s, color 0.15s;
}
.modal-close:hover { background: var(--indigo-tint); color: var(--indigo-dark); }

.form-heading { margin-bottom: 24px; text-align: left; }
.form-heading h2 { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 600; margin-bottom: 6px; letter-spacing: -0.01em; }
.form-heading p { font-size: 0.85rem; color: var(--text-secondary); }

.alert {
    display: none; align-items: flex-start; gap: 10px;
    padding: 12px 14px; border-radius: var(--radius-sm);
    margin-bottom: 20px; font-size: 0.83rem; font-weight: 500;
    border: 1px solid transparent; text-align: left;
}
.alert.show { display: flex; }
.alert svg { flex-shrink: 0; margin-top: 1px; }
.alert-error { background: var(--red-bg); color: var(--red); border-color: #f2c6c3; }

.field { margin-bottom: 18px; text-align: left; }
.field label { display: block; font-size: 0.81rem; font-weight: 600; margin-bottom: 7px; }
.input-wrap { position: relative; }
.input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; display: flex; }
.input-wrap input {
    width: 100%; padding: 12px 14px 12px 42px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-size: 0.9rem; font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg); outline: none;
    transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
}
.input-wrap input::placeholder { color: var(--text-muted); font-weight: 400; }
.input-wrap input:focus { border-color: var(--indigo); background: var(--surface); box-shadow: 0 0 0 4px var(--indigo-tint); }
.input-wrap input.has-error { border-color: var(--red); }

.toggle-pw {
    position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: var(--text-muted);
    display: flex; padding: 2px;
}
.toggle-pw:hover { color: var(--text-secondary); }

.btn-submit {
    width: 100%; padding: 13px;
    background: var(--indigo); color: white; border: none; border-radius: 999px;
    font-size: 0.92rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif; margin-top: 6px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background 0.15s, transform 0.1s;
}
.btn-submit:hover { background: var(--indigo-dark); }
.btn-submit:active { transform: scale(0.98); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

/* Visible proof that validation is asynchronous: the button spins while
   fetch() is in flight, but the rest of the page stays fully interactive
   (you can still close the modal, click elsewhere, etc.) — nothing freezes. */
.spinner {
    width: 15px; height: 15px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.form-footer-note { text-align: center; margin-top: 24px; font-size: 0.74rem; color: var(--text-muted); }

.page-footer {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 1;
    text-align: center; padding: 18px 24px; font-size: 0.78rem; color: var(--text-muted);
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: linear-gradient(to top, var(--bg) 60%, transparent);
}
.page-footer svg { flex-shrink: 0; color: var(--indigo); opacity: 0.55; }

.swal2-popup { font-family: 'Plus Jakarta Sans', sans-serif !important; border-radius: 18px !important; }
.swal2-confirm { background: var(--indigo) !important; border-radius: 999px !important; padding: 10px 22px !important; }
.swal2-cancel { border-radius: 999px !important; padding: 10px 22px !important; }
.swal2-title { font-family: 'Fraunces', serif !important; }

@media (max-width: 900px) {
    .hero-headline { font-size: 2.9rem; }
}

@media (max-width: 640px) {
    .topnav { padding: 0 20px; }
    .nav-link { display: none; }
    .blob-a, .blob-b, .blob-c, .blob-d { max-width: 60vw; }
    .hero { padding: 56px 20px 32px; }
    .hero-headline { font-size: 2.1rem; margin-bottom: 16px; }
    .hero-sub { font-size: 0.92rem; margin-bottom: 30px; }
    .trust-chip { font-size: 0.76rem; padding: 7px 13px 7px 10px; }
    .form-card { padding: 30px 22px 24px; }
}
</style>
</head>
<body>

<div class="blob-field">
    <div class="blob blob-a"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#DCD4F7" d="M45.5,-58.4C57.9,-49.6,66,-33.8,69.6,-16.9C73.1,0,72.1,18,64.4,32.5C56.7,47,42.3,58,25.9,64.1C9.5,70.2,-9,71.4,-25.6,66C-42.2,60.6,-56.9,48.6,-64.8,33.2C-72.7,17.8,-73.8,-1,-68.4,-17.2C-63,-33.4,-51.1,-47,-36.9,-55.6C-22.7,-64.2,-6.2,-67.8,9.6,-68.5C25.4,-69.2,33.1,-67.2,45.5,-58.4Z" transform="translate(100 100)"/></svg></div>
    <div class="blob blob-b"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#FBD9B8" d="M39.5,-51.6C50.5,-42.6,58,-29.4,62.1,-14.6C66.2,0.3,66.8,16.9,60.1,30.4C53.4,44,39.4,54.4,24,60.6C8.6,66.8,-8.2,68.7,-23.6,64.3C-39,59.8,-53,49,-61.1,34.6C-69.2,20.2,-71.4,2.1,-67.4,-14.1C-63.4,-30.3,-53.2,-44.6,-40,-53.6C-26.8,-62.7,-13.4,-66.5,0.9,-67.7C15.2,-69,30.5,-60.6,39.5,-51.6Z" transform="translate(100 100)"/></svg></div>
    <div class="blob blob-c"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#0F3D3E" d="M42.6,-55.3C54.5,-47.9,62.6,-33.9,66.2,-18.7C69.8,-3.4,68.9,13.1,62.2,27.1C55.6,41.1,43.2,52.6,29,58.7C14.8,64.8,-1.2,65.5,-16.9,61.5C-32.6,57.5,-48,48.8,-57.6,35.5C-67.2,22.2,-71,4.3,-67.6,-11.9C-64.2,-28.1,-53.6,-42.6,-40.2,-50.1C-26.8,-57.6,-13.4,-58.1,1.4,-60.1C16.2,-62.1,30.7,-62.7,42.6,-55.3Z" transform="translate(100 100)"/></svg></div>
    <div class="blob blob-d"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#DCD4F7" d="M45.5,-58.4C57.9,-49.6,66,-33.8,69.6,-16.9C73.1,0,72.1,18,64.4,32.5C56.7,47,42.3,58,25.9,64.1C9.5,70.2,-9,71.4,-25.6,66C-42.2,60.6,-56.9,48.6,-64.8,33.2C-72.7,17.8,-73.8,-1,-68.4,-17.2C-63,-33.4,-51.1,-47,-36.9,-55.6C-22.7,-64.2,-6.2,-67.8,9.6,-68.5C25.4,-69.2,33.1,-67.2,45.5,-58.4Z" transform="translate(100 100)"/></svg></div>
</div>

<nav class="topnav">
    <div class="nav-brand">
        <div class="nav-brand-icon">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                <rect x="2" y="2" width="36" height="36" rx="11" fill="#4F3FBF"/>
                <path d="M13 20h4l2.5-7 5 14L27 20h4" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <span class="nav-brand-text">EHR System</span>
    </div>
    <div class="nav-spacer"></div>
    <a href="about.php" class="nav-link">About EHR System</a>
    <button type="button" class="nav-cta" id="btnOpenLogin">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Login
    </button>
</nav>

<div class="hero">
    <div class="hero-eyebrow"><span class="dot"></span>Trusted by your care team</div>
    <h1 class="hero-headline">One secure record for <em>every patient visit.</em></h1>
    <p class="hero-sub">Patients, appointments, and consultations — organized in one place, accessible only to the people your clinic authorizes.</p>

    <div class="trust-strip">
        <div class="trust-chip">
            <span class="chip-icon c1"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
            Role-based access
        </div>
        <div class="trust-chip">
            <span class="chip-icon c2"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></span>
            Every action audited
        </div>
        <div class="trust-chip">
            <span class="chip-icon c3"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
            Auto session timeout
        </div>
    </div>
</div>

<div class="modal-overlay" id="loginOverlay">
<div class="form-card">
    <button type="button" class="modal-close" id="btnCloseLogin" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="form-heading">
        <h2>Welcome back</h2>
        <p>Use your clinic-issued account to continue.</p>
    </div>

    <div class="alert alert-error" id="loginAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="loginAlertText"></span>
    </div>

    <form id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="field">
            <label for="username">Username</label>
            <div class="input-wrap">
                <span class="input-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                <input type="text" name="username" id="username" placeholder="Enter your username" autocomplete="username" required>
            </div>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="input-wrap">
                <span class="input-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
                <input type="password" name="password" id="password" placeholder="Enter your password" autocomplete="current-password" required>
                <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Show password">
                    <svg id="eye-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <span id="submitBtnLabel">Sign in</span>
            <svg id="submitBtnArrow" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
    </form>

    <div class="form-footer-note">Authorized clinic personnel only</div>
</div>
</div>

<!--
<div class="page-footer">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Academic prototype — fictional patient data only
</div>
-->

<script>
function togglePw() {
    var input = document.getElementById('password');
    var icon  = document.getElementById('eye-icon');
    var isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    icon.innerHTML = isText
        ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
        : '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
}

function openLoginModal() {
    var overlay = document.getElementById('loginOverlay');
    overlay.classList.add('open');
    requestAnimationFrame(() => overlay.classList.add('show'));
    document.body.style.overflow = 'hidden';
}
function closeLoginModal() {
    var overlay = document.getElementById('loginOverlay');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => overlay.classList.remove('open'), 200);
}
document.getElementById('btnOpenLogin').addEventListener('click', openLoginModal);
document.getElementById('btnCloseLogin').addEventListener('click', closeLoginModal);
document.getElementById('loginOverlay').addEventListener('click', function (e) {
    if (e.target === this) closeLoginModal();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeLoginModal();
});

<?php if ($inactiveMsg): ?>
openLoginModal();
Swal.fire({ icon: 'warning', title: 'Account Inactive', text: <?= json_encode($inactiveMsg) ?>, confirmButtonText: 'Understood' });
<?php endif; ?>

<?php if ($timeoutMsg): ?>
openLoginModal();
Swal.fire({
    toast: true, position: 'top-end', icon: 'info',
    title: <?= json_encode($timeoutMsg) ?>,
    showConfirmButton: false, timer: 4000, timerProgressBar: true
});
<?php endif; ?>

/* =====================================================================
   ASYNCHRONOUS LOGIN
   ---------------------------------------------------------------------
   1. preventDefault() stops the browser's normal (synchronous) submit.
   2. fetch() sends the credentials to api/login.php in the background.
   3. async/await lets this read top-to-bottom, but the request itself
      is still non-blocking — the modal, fields, and page stay fully
      interactive while we wait for the server's JSON response.
   4. The result decides the next step via SweetAlert2 + inline alert,
      then either redirects (success) or resets the form (failure) —
      no full page reload either way.
   ===================================================================== */
const loginForm     = document.getElementById('loginForm');
const submitBtn      = document.getElementById('submitBtn');
const submitLabel   = document.getElementById('submitBtnLabel');
const alertBox        = document.getElementById('loginAlert');
const alertText      = document.getElementById('loginAlertText');
const usernameInput  = document.getElementById('username');
const passwordInput  = document.getElementById('password');

function setLoading(isLoading) {
    submitBtn.disabled = isLoading;
    const arrowSlot = document.getElementById('submitBtnArrow');
    if (isLoading) {
        submitLabel.textContent = 'Signing in';
        arrowSlot.outerHTML = '<span class="spinner" id="submitBtnArrow"></span>';
    } else {
        submitLabel.textContent = 'Sign in';
        arrowSlot.outerHTML = '<svg id="submitBtnArrow" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
    }
}

function showInlineError(message) {
    alertText.textContent = message;
    alertBox.classList.add('show');
    usernameInput.classList.add('has-error');
    passwordInput.classList.add('has-error');
}

function clearInlineError() {
    alertBox.classList.remove('show');
    usernameInput.classList.remove('has-error');
    passwordInput.classList.remove('has-error');
}

loginForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    clearInlineError();

    const username = usernameInput.value.trim();
    const password = passwordInput.value;

    if (!username || !password) {
        showInlineError('Username and password are required.');
        return;
    }

    setLoading(true);

    try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        formData.append('csrf_token', document.getElementById('csrfToken').value);

        const response = await fetch('api/login.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'Signed in — redirecting…',
                showConfirmButton: false, timer: 1400, timerProgressBar: true
            });
            setTimeout(() => { window.location.href = data.redirect; }, 700);
        } else {
            setLoading(false);
            // Refresh the CSRF token so the NEXT attempt doesn't fail —
            // the server rotates the token on every validated request.
            if (data.csrf_token) {
                document.getElementById('csrfToken').value = data.csrf_token;
            }
            showInlineError(data.message || 'Something went wrong. Please try again.');
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: data.message || 'Login failed.',
                showConfirmButton: false, timer: 4000, timerProgressBar: true
            });
        }
    } catch (err) {
        setLoading(false);
        showInlineError('Could not reach the server. Check your connection and try again.');
        Swal.fire({
            toast: true, position: 'top-end', icon: 'error',
            title: 'Network error — please try again.',
            showConfirmButton: false, timer: 4000, timerProgressBar: true
        });
    }
});
</script>
</body>
</html>