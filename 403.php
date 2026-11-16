<?php
require_once __DIR__ . "/includes/security.php";
secure_session_start();
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access Denied — EHR System</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Inter', sans-serif;
    background: #f7f8f7; color: #1c2523;
    min-height: 100vh; display: flex; align-items: center; justify-content: center;
    padding: 24px;
}
.box { text-align: center; max-width: 380px; }
.icon-wrap {
    width: 56px; height: 56px; background: #fdecea; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; color: #b3261e;
}
h1 { font-size: 1.2rem; margin-bottom: 8px; }
p { font-size: 0.88rem; color: #5b6663; line-height: 1.5; margin-bottom: 22px; }
a {
    display: inline-block; background: #0a6f6b; color: white; text-decoration: none;
    padding: 10px 22px; border-radius: 8px; font-size: 0.88rem; font-weight: 600;
}
a:hover { background: #075451; }
</style>
</head>
<body>
<div class="box">
    <div class="icon-wrap">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
    </div>
    <h1>Access denied</h1>
    <p>Your account role doesn't have permission to view this page. If you think this is a mistake, contact your system administrator.</p>
    <a href="login.php">Return to sign in</a>
</div>
</body>
</html>