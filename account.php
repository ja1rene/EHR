<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

generate_csrf_token();

$page_title = "My Account";
$role_label = match ($_SESSION['role']) {
    'admin'  => 'Administrator',
    'doctor' => 'Doctor',
    'staff'  => 'Staff / Nurse',
    default  => $_SESSION['role'],
};
$nav_links = [];
$show_back = true;

ob_start();
?>
<div class="account-grid">

    <!-- ═══ My Profile ═══ -->
    <div class="card">
        <div class="section-title">My Profile</div>

        <div class="alert alert-error" id="profileAlert">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="profileAlertText"></span>
        </div>

        <form id="profileForm">
            <input type="hidden" name="csrf_token" id="profileCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="avatar-row">
                <?php if (!empty($_SESSION['avatar_path'])): ?>
                    <img src="<?= base_url(htmlspecialchars($_SESSION['avatar_path'])) ?>" alt="" class="avatar-preview" id="avatarPreview">
                <?php else: ?>
                    <div class="avatar-preview avatar-initials" id="avatarPreview"><?= htmlspecialchars(strtoupper(substr($_SESSION['full_name'], 0, 1))) ?></div>
                <?php endif; ?>
                <div>
                    <label class="avatar-upload-btn" for="avatarInput">Change Photo</label>
                    <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                    <div class="field-hint">JPG, PNG, or WEBP. Max 2MB.</div>
                </div>
            </div>

            <div class="field">
                <label>Full Name *</label>
                <input type="text" name="full_name" id="fullNameInput" value="<?= htmlspecialchars($_SESSION['full_name']) ?>" required>
            </div>
            <div class="field">
                <label>Username</label>
                <input type="text" value="<?= htmlspecialchars($_SESSION['username']) ?>" disabled>
                <div class="field-hint">Only an Administrator can change your username.</div>
            </div>
            <div class="field">
                <label>Role</label>
                <input type="text" value="<?= htmlspecialchars($role_label) ?>" disabled>
            </div>

            <button type="submit" class="btn-submit" id="profileSubmitBtn">
                <span id="profileSubmitLabel">Save Profile</span>
            </button>
        </form>
    </div>

    <!-- ═══ Change Password ═══ -->
    <div class="card">
        <div class="section-title">Change Password</div>

        <div class="alert alert-error" id="pwAlert">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="pwAlertText"></span>
        </div>

        <form id="pwForm">
            <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="field"><label>Current Password *</label><input type="password" name="current_password" required autocomplete="current-password"></div>
            <div class="field">
                <label>New Password *</label>
                <input type="password" name="new_password" id="newPasswordInput" required minlength="8" autocomplete="new-password">
            </div>
            <div class="field"><label>Confirm New Password *</label><input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span id="submitBtnLabel">Update Password</span>
            </button>
        </form>
    </div>

</div>

<style>
.account-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
.section-title { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--indigo); margin-bottom: 18px; }
.alert { display: none; align-items: flex-start; gap: 9px; padding: 11px 13px; border-radius: 8px; margin-bottom: 16px; font-size: 0.82rem; font-weight: 500; border: 1px solid transparent; }
.alert.show { display: flex; }
.alert-error { background: #FDECEA; color: #B3261E; border-color: #f2c6c3; }

.avatar-row { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
.avatar-preview { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); }
.avatar-initials { background: var(--teal); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.4rem; }
.avatar-upload-btn {
    display: inline-block; background: var(--indigo-tint); color: var(--indigo-dark);
    padding: 7px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; cursor: pointer;
}
.avatar-upload-btn:hover { background: #e2ddfa; }

.field { margin-bottom: 16px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 6px; }
.field input {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.86rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none;
}
.field input:disabled { color: var(--text-muted); cursor: not-allowed; }
.field input:focus { border-color: var(--indigo); background: var(--surface); }
.field-hint { font-size: 0.74rem; color: var(--text-muted); margin-top: 5px; }

.btn-submit {
    width: 100%; padding: 12px; background: var(--indigo); color: white; border: none; border-radius: 999px;
    font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; margin-top: 6px;
}
.btn-submit:hover { background: var(--indigo-dark); }
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 820px) {
    .account-grid { grid-template-columns: 1fr; }
}
</style>
<?php
$page_content = ob_get_clean();
require __DIR__ . "/includes/dashboard_shell.php";
?>

<script src="<?= base_url('assets/js/password-strength.js') ?>"></script>
<script>
attachPasswordStrength('newPasswordInput');

/* ── Profile save (name + avatar) ── */
const profileForm      = document.getElementById('profileForm');
const profileAlert       = document.getElementById('profileAlert');
const profileAlertText  = document.getElementById('profileAlertText');
const profileSubmitBtn   = document.getElementById('profileSubmitBtn');
const profileSubmitLabel = document.getElementById('profileSubmitLabel');

document.getElementById('avatarInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById('avatarPreview');
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'avatar-preview';
        img.id = 'avatarPreview';
        preview.replaceWith(img);
    };
    reader.readAsDataURL(file);
});

profileForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    profileAlert.classList.remove('show');
    profileSubmitBtn.disabled = true;
    profileSubmitLabel.innerHTML = '<span class="spinner"></span> Saving…';

    try {
        const formData = new FormData(profileForm);
        const response = await fetch('<?= base_url("api/account_update_profile.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('profileCsrfToken').value = data.csrf_token;

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Profile updated', showConfirmButton: false, timer: 2500, timerProgressBar: true })
                .then(() => location.reload()); // reload so the topnav avatar/name refreshes everywhere
        } else {
            profileAlertText.textContent = data.message || 'Could not save profile.';
            profileAlert.classList.add('show');
            profileSubmitBtn.disabled = false;
            profileSubmitLabel.textContent = 'Save Profile';
        }
    } catch (err) {
        profileAlertText.textContent = 'Could not reach the server. Please try again.';
        profileAlert.classList.add('show');
        profileSubmitBtn.disabled = false;
        profileSubmitLabel.textContent = 'Save Profile';
    }
});

/* ── Password change ── */
const pwForm       = document.getElementById('pwForm');
const pwAlert        = document.getElementById('pwAlert');
const pwAlertText   = document.getElementById('pwAlertText');
const submitBtn      = document.getElementById('submitBtn');
const submitLabel   = document.getElementById('submitBtnLabel');

pwForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    pwAlert.classList.remove('show');
    submitBtn.disabled = true;
    submitLabel.innerHTML = '<span class="spinner"></span> Updating…';

    try {
        const formData = new FormData(pwForm);
        const response = await fetch('<?= base_url("api/account_change_password.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('csrfToken').value = data.csrf_token;

        if (data.success) {
            pwForm.reset();
            document.getElementById('newPasswordInput').dispatchEvent(new Event('input')); // reset the strength meter display
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Password updated', showConfirmButton: false, timer: 2500, timerProgressBar: true });
        } else {
            pwAlertText.textContent = data.message || 'Could not update password.';
            pwAlert.classList.add('show');
        }
    } catch (err) {
        pwAlertText.textContent = 'Could not reach the server. Please try again.';
        pwAlert.classList.add('show');
    } finally {
        submitBtn.disabled = false;
        submitLabel.textContent = 'Update Password';
    }
});
</script>