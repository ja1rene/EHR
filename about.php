<?php
require_once "includes/auth.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About — EHR System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

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
    --bg:             #FBFAFF;
    --border:         #E6E3F2;
    --text-primary:   #1B1730;
    --text-secondary: #675F82;
    --text-muted:     #9A93B3;
    --radius:         16px;
    --radius-sm:      10px;
    --nav-h:          76px;
    --shadow-sm:      0 1px 2px rgba(27,23,48,0.04), 0 4px 14px -6px rgba(27,23,48,0.08);
    --shadow-md:      0 2px 4px rgba(27,23,48,0.04), 0 18px 40px -18px rgba(79,63,191,0.28);
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text-primary);
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

/* ───── Nav ───── */
.topnav {
    position: sticky; top: 0; z-index: 50;
    display: flex; align-items: center;
    height: var(--nav-h); padding: 0 56px;
    background: rgba(251,250,255,0.82);
    backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
}
.nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
.nav-brand-icon { width: 32px; height: 32px; flex-shrink: 0; }
.nav-brand-text { font-size: 1.05rem; font-weight: 800; letter-spacing: -0.02em; }
.nav-spacer { flex: 1; }
.nav-cta {
    display: flex; align-items: center; gap: 8px;
    background: var(--indigo); color: white; text-decoration: none;
    font-size: 0.86rem; font-weight: 700;
    padding: 10px 20px; border-radius: 999px;
    transition: background 0.15s, transform 0.15s;
}
.nav-cta:hover { background: var(--indigo-dark); transform: translateY(-1px); }

/* ───── Hero ───── */
.blob-field { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
.blob { position: absolute; opacity: 0.5; max-width: 40vw; }
.blob svg { width: 100%; height: 100%; display: block; }
.blob-a { width: 480px; top: -140px; right: -180px; animation: drift-a 22s ease-in-out infinite; }
.blob-b { width: 360px; bottom: -120px; left: -140px; animation: drift-b 26s ease-in-out infinite; }
.blob-c { width: 220px; top: 45%; right: 3%; opacity: 0.28; animation: drift-c 18s ease-in-out infinite; }
@keyframes drift-a { 0%,100% { transform: translate(0,0) rotate(0deg); } 50% { transform: translate(-24px,28px) rotate(8deg); } }
@keyframes drift-b { 0%,100% { transform: translate(0,0) rotate(0deg); } 50% { transform: translate(20px,-22px) rotate(-6deg); } }
@keyframes drift-c { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-14px,16px) scale(1.06); } }
.hero, .wrap { position: relative; z-index: 1; }

.hero-inner { position: relative; z-index: 1; max-width: 1040px; margin: 0 auto; padding: 84px 24px 76px; }

.page-eyebrow {
    display: inline-flex; align-items: center; gap: 9px;
    font-size: 0.86rem; font-weight: 700; color: var(--indigo-dark);
    background: var(--indigo-tint);
    padding: 9px 18px 9px 13px; border-radius: 999px;
    margin-bottom: 26px;
}
.page-eyebrow .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--indigo); box-shadow: 0 0 0 4px rgba(79,63,191,0.18); }

h1 {
    font-family: 'Fraunces', serif;
    font-size: 3.1rem; font-weight: 600; line-height: 1.12;
    letter-spacing: -0.025em; margin-bottom: 22px; max-width: 760px;
}
h1 em { font-style: normal; color: var(--indigo); }
.lede {
    font-size: 1.1rem; color: var(--text-secondary); line-height: 1.65;
    max-width: 640px; margin-bottom: 34px;
}
.hero-chips { display: flex; flex-wrap: wrap; gap: 10px; }
.chip {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--surface); border: 1px solid var(--border); border-radius: 999px;
    padding: 8px 16px 8px 12px; font-size: 0.82rem; font-weight: 600; box-shadow: var(--shadow-sm);
}
.chip svg { color: var(--indigo); }

/* ───── Layout ───── */
.wrap { max-width: 1040px; margin: 0 auto; padding: 0 24px 80px; }
section { padding-top: 84px; }
.section-head { max-width: 640px; margin-bottom: 34px; }
.section-label {
    display: inline-flex; align-items: center; gap: 10px;
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
    color: var(--indigo); margin-bottom: 12px;
}
.section-title {
    font-family: 'Fraunces', serif;
    font-size: 2rem; font-weight: 600; margin-bottom: 14px; letter-spacing: -0.02em; line-height: 1.2;
}
.section-text { color: var(--text-secondary); line-height: 1.7; font-size: 0.98rem; }

/* ───── Visit journey ───── */
.journey {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 0;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
    box-shadow: var(--shadow-sm); padding: 26px 12px; position: relative;
}
.journey::before {
    content: ''; position: absolute; top: 46px; left: 10%; right: 10%; height: 2px; background: var(--lavender);
}
.step { position: relative; text-align: center; padding: 0 10px; }
.step-num {
    width: 42px; height: 42px; margin: 0 auto 12px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--indigo-tint); color: var(--indigo); font-weight: 800; font-size: 0.9rem;
    position: relative; z-index: 1;
}
.step:last-child .step-num { background: var(--indigo); color: #fff; }
.step h4 { font-size: 0.86rem; font-weight: 700; margin-bottom: 3px; }
.step p  { font-size: 0.76rem; color: var(--text-muted); }

/* ───── Modules (bento) ───── */
.module-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
.module-card {
    grid-column: span 2;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 24px; box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.module-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--lavender); }
.module-card.wide { grid-column: span 3; }
.module-card-icon {
    width: 42px; height: 42px; border-radius: 12px; margin-bottom: 18px;
    display: flex; align-items: center; justify-content: center;
    background: var(--indigo-tint); color: var(--indigo);
}
.module-card:nth-child(2) .module-card-icon { background: var(--teal-tint);  color: var(--teal); }
.module-card:nth-child(4) .module-card-icon { background: var(--peach-tint); color: #B45309; }
.module-card:nth-child(5) .module-card-icon { background: var(--teal-tint);  color: var(--teal); }
.module-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
.module-card p  { font-size: 0.86rem; color: var(--text-secondary); line-height: 1.6; }
.module-tag {
    display: inline-block; margin-top: 14px; font-size: 0.72rem; font-weight: 700;
    color: var(--teal); background: var(--teal-tint); padding: 4px 11px; border-radius: 999px;
}

/* ───── Roles ───── */
.role-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.role-card {
    position: relative; overflow: hidden;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 28px 24px 24px; box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.role-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; }
.role-card.r-admin::before  { background: #7C3AED; }
.role-card.r-doctor::before { background: var(--teal); }
.role-card.r-staff::before  { background: var(--indigo); }
.role-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.role-tag {
    display: inline-flex; padding: 4px 13px; border-radius: 999px;
    font-size: 0.76rem; font-weight: 700; margin-bottom: 18px;
}
.role-tag.admin  { background: #F3E8FF; color: #7C3AED; }
.role-tag.doctor { background: var(--teal-tint); color: var(--teal); }
.role-tag.staff  { background: var(--indigo-tint); color: var(--indigo-dark); }
.role-card ul { list-style: none; }
.role-card li {
    font-size: 0.88rem; color: var(--text-secondary); padding: 8px 0 8px 26px; position: relative;
    border-top: 1px solid var(--border); line-height: 1.45;
}
.role-card li:first-child { border-top: none; }
.role-card li::before {
    content: ''; position: absolute; left: 0; top: 11px; width: 16px; height: 16px; border-radius: 50%;
    background: var(--indigo-tint)
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234F3FBF' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 12 10 16 18 8'/%3E%3C/svg%3E")
        center / 10px no-repeat;
}

/* ───── Security ───── */
.security-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.security-item {
    display: flex; gap: 16px; align-items: flex-start;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 22px; box-shadow: var(--shadow-sm);
}
.security-item-icon {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    background: var(--teal-tint); color: var(--teal);
    display: flex; align-items: center; justify-content: center;
}
.security-item h4 { font-size: 0.93rem; font-weight: 700; margin-bottom: 5px; line-height: 1.35; }
.security-item p  { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.55; }

/* ───── Context ───── */
.context-card {
    position: relative; overflow: hidden;
    background: var(--teal); color: #fff; border-radius: 22px; padding: 44px 48px;
    box-shadow: var(--shadow-md);
}
.context-card::after {
    content: ''; position: absolute; width: 320px; height: 320px; border-radius: 50%;
    right: -90px; top: -110px; background: radial-gradient(circle, rgba(220,212,247,0.28), transparent 70%);
}
.context-card .section-label { color: var(--peach); }
.context-card .section-title { color: #fff; }
.context-card p { position: relative; z-index: 1; font-size: 0.96rem; color: rgba(255,255,255,0.78); line-height: 1.7; max-width: 700px; }
.context-card p + p { margin-top: 12px; }
.context-card strong { color: #fff; }

/* ───── Footer ───── */
.page-footer {
    border-top: 1px solid var(--border); padding-top: 26px; margin-top: 72px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    font-size: 0.82rem; color: var(--text-muted);
}
.page-footer svg { flex-shrink: 0; color: var(--indigo); opacity: 0.6; }

/* ───── Scroll reveal ───── */
.reveal { opacity: 0; transform: translateY(18px); transition: opacity 0.6s ease, transform 0.6s ease; }
.reveal.in { opacity: 1; transform: none; }
@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    .reveal { opacity: 1; transform: none; transition: none; }
    .module-card, .role-card, .nav-cta { transition: none; }
    .blob { animation: none; }
}

/* ───── Responsive ───── */
@media (max-width: 860px) {
    .module-grid { grid-template-columns: repeat(2, 1fr); }
    .module-card, .module-card.wide { grid-column: span 1; }
    .role-grid { grid-template-columns: 1fr; }
    .security-grid { grid-template-columns: 1fr; }
    .journey { grid-template-columns: 1fr; gap: 18px; padding: 24px; }
    .step { display: flex; align-items: center; gap: 14px; text-align: left; padding: 0; }
    .step-num { margin: 0; flex-shrink: 0; }
    .journey::before { display: none; }
}
@media (max-width: 560px) {
    .topnav { padding: 0 20px; }
    .hero-inner { padding: 56px 20px 52px; }
    .wrap { padding: 0 20px 60px; }
    h1 { font-size: 2.1rem; }
    section { padding-top: 60px; }
    .section-title { font-size: 1.6rem; }
    .module-grid { grid-template-columns: 1fr; }
    .context-card { padding: 32px 26px; }
}
</style>
</head>
<body>

<div class="blob-field">
    <div class="blob blob-a"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#DCD4F7" d="M45.5,-58.4C57.9,-49.6,66,-33.8,69.6,-16.9C73.1,0,72.1,18,64.4,32.5C56.7,47,42.3,58,25.9,64.1C9.5,70.2,-9,71.4,-25.6,66C-42.2,60.6,-56.9,48.6,-64.8,33.2C-72.7,17.8,-73.8,-1,-68.4,-17.2C-63,-33.4,-51.1,-47,-36.9,-55.6C-22.7,-64.2,-6.2,-67.8,9.6,-68.5C25.4,-69.2,33.1,-67.2,45.5,-58.4Z" transform="translate(100 100)"/></svg></div>
    <div class="blob blob-b"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#FBD9B8" d="M39.5,-51.6C50.5,-42.6,58,-29.4,62.1,-14.6C66.2,0.3,66.8,16.9,60.1,30.4C53.4,44,39.4,54.4,24,60.6C8.6,66.8,-8.2,68.7,-23.6,64.3C-39,59.8,-53,49,-61.1,34.6C-69.2,20.2,-71.4,2.1,-67.4,-14.1C-63.4,-30.3,-53.2,-44.6,-40,-53.6C-26.8,-62.7,-13.4,-66.5,0.9,-67.7C15.2,-69,30.5,-60.6,39.5,-51.6Z" transform="translate(100 100)"/></svg></div>
    <div class="blob blob-c"><svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><path fill="#0F3D3E" d="M42.6,-55.3C54.5,-47.9,62.6,-33.9,66.2,-18.7C69.8,-3.4,68.9,13.1,62.2,27.1C55.6,41.1,43.2,52.6,29,58.7C14.8,64.8,-1.2,65.5,-16.9,61.5C-32.6,57.5,-48,48.8,-57.6,35.5C-67.2,22.2,-71,4.3,-67.6,-11.9C-64.2,-28.1,-53.6,-42.6,-40.2,-50.1C-26.8,-57.6,-13.4,-58.1,1.4,-60.1C16.2,-62.1,30.7,-62.7,42.6,-55.3Z" transform="translate(100 100)"/></svg></div>
</div>

<nav class="topnav">
    <a href="login.php" class="nav-brand">
        <div class="nav-brand-icon">
            <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                <rect x="2" y="2" width="36" height="36" rx="11" fill="#4F3FBF"/>
                <path d="M13 20h4l2.5-7 5 14L27 20h4" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <span class="nav-brand-text">EHR System</span>
    </a>
    <div class="nav-spacer"></div>
    <a href="login.php" class="nav-cta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Sign in
    </a>
</nav>

<header class="hero">
    <div class="hero-inner">
        <div class="page-eyebrow"><span class="dot"></span>About this system</div>
        <h1>A record-keeping system for a small clinic's <em>day-to-day work.</em></h1>
        <p class="lede">This is a web-based Electronic Health Record Management System built for a small or private clinic. It keeps patient records, appointments, consultations, and prescriptions in one place, with each user's access limited to what their role at the clinic actually needs.</p>
        <div class="hero-chips">
            <span class="chip"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg>3 user roles</span>
            <span class="chip"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>6 modules</span>
            <span class="chip"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Role-based access</span>
        </div>
    </div>
</header>

<div class="wrap">

    <section class="reveal">
        <div class="section-head">
            <div class="section-label">How a visit flows</div>
            <div class="section-title">From registration to prescription</div>
            <p class="section-text">The system is organized around the flow of a patient visit — from registration, through scheduling, to the consultation itself and whatever follows it.</p>
        </div>
        <div class="journey">
            <div class="step"><div class="step-num">1</div><div><h4>Register</h4><p>Patient record</p></div></div>
            <div class="step"><div class="step-num">2</div><div><h4>Schedule</h4><p>Appointment</p></div></div>
            <div class="step"><div class="step-num">3</div><div><h4>Vitals</h4><p>Measurements</p></div></div>
            <div class="step"><div class="step-num">4</div><div><h4>Consult</h4><p>Diagnosis</p></div></div>
            <div class="step"><div class="step-num">5</div><div><h4>Prescribe</h4><p>Treatment</p></div></div>
        </div>
    </section>

    <section class="reveal">
        <div class="section-head">
            <div class="section-label">What's inside</div>
            <div class="section-title">Modules</div>
        </div>
        <div class="module-grid">
            <div class="module-card wide">
                <div class="module-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/></svg></div>
                <h3>Patients</h3>
                <p>Registration, demographic and medical history, and a searchable patient list.</p>
            </div>
            <div class="module-card wide">
                <div class="module-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <h3>Appointments</h3>
                <p>Scheduling by patient and doctor, with status tracking through to completion.</p>
                <span class="module-tag">Doctors are notified by email</span>
            </div>
            <div class="module-card">
                <div class="module-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
                <h3>Vitals</h3>
                <p>Blood pressure, temperature, pulse, and other measurements recorded per visit.</p>
            </div>
            <div class="module-card">
                <div class="module-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
                <h3>Consultations</h3>
                <p>Chief complaint, findings, diagnosis, and treatment plan, tied to an appointment.</p>
            </div>
            <div class="module-card">
                <div class="module-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div>
                <h3>Prescriptions</h3>
                <p>One or more medicines per prescription, each with its own dosage and instructions.</p>
            </div>
            <div class="module-card">
                <div class="module-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v6M15 2v6M9 14h6"/><circle cx="12" cy="14" r="8"/></svg></div>
                <h3>Laboratory Results</h3>
                <p>Test name, result, reference range, and status, linked to the patient record.</p>
            </div>
        </div>
    </section>

    <section class="reveal">
        <div class="section-head">
            <div class="section-label">Who uses it</div>
            <div class="section-title">Three roles, three different views</div>
            <p class="section-text">Nobody sees the whole system the same way. What a person can view or change depends on their role — an administrator manages the system itself, but doesn't see clinical records; a doctor writes clinical notes but doesn't register patients; staff handle the clinic's day-to-day operations.</p>
        </div>
        <div class="role-grid">
            <div class="role-card r-admin">
                <span class="role-tag admin">Administrator</span>
                <ul>
                    <li>Creates and manages user accounts</li>
                    <li>Reviews the system's audit log</li>
                    <li>Sees usage statistics, not patient records</li>
                </ul>
            </div>
            <div class="role-card r-doctor">
                <span class="role-tag doctor">Doctor</span>
                <ul>
                    <li>Views any patient's record</li>
                    <li>Writes consultations and diagnoses</li>
                    <li>Creates prescriptions</li>
                    <li>Manages their own appointment schedule</li>
                </ul>
            </div>
            <div class="role-card r-staff">
                <span class="role-tag staff">Staff / Nurse</span>
                <ul>
                    <li>Registers and updates patient records</li>
                    <li>Schedules appointments</li>
                    <li>Records vitals and lab results</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="reveal">
        <div class="section-head">
            <div class="section-label">How it's protected</div>
            <div class="section-title">Security measures in place</div>
            <p class="section-text">A record system like this handles information that shouldn't be casually accessible, so a few things are enforced on every request rather than left to chance.</p>
        </div>
        <div class="security-grid">
            <div class="security-item">
                <div class="security-item-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div>
                <div>
                    <h4>Passwords are hashed, never stored as plain text</h4>
                    <p>Even someone with direct database access can't read anyone's password.</p>
                </div>
            </div>
            <div class="security-item">
                <div class="security-item-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                <div>
                    <h4>Sessions expire after inactivity</h4>
                    <p>Being logged in doesn't last forever — an idle session is signed out automatically.</p>
                </div>
            </div>
            <div class="security-item">
                <div class="security-item-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg></div>
                <div>
                    <h4>Every action is checked against the user's role</h4>
                    <p>Permissions are enforced on the server for every request, not just hidden in the interface.</p>
                </div>
            </div>
            <div class="security-item">
                <div class="security-item-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                <div>
                    <h4>Actions are logged</h4>
                    <p>Logins, record changes, and administrative actions are recorded with who did them and when.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="reveal">
        <div class="context-card">
            <div class="section-label">Context</div>
            <div class="section-title">About this project</div>
            <p>This system was built as a course project for <strong>Event-Driven Programming</strong>, developed incrementally module by module rather than all at once. It uses PHP and MySQL on the backend, with JavaScript handling form validation, live search, and asynchronous requests to the server.</p>
            <p>It's a working prototype, not a certified production system — all patient data used for testing is fictional.</p>
        </div>
    </section>

    <div class="page-footer">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Academic prototype — fictional patient data only
    </div>

</div>

<script>
(function () {
    var items = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('in'); });
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
        });
    }, { threshold: 0.12 });
    items.forEach(function (el) { io.observe(el); });
})();
</script>

</body>
</html>