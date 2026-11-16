<?php
require_once __DIR__ . "/../includes/auth.php";
require_role('admin');

generate_csrf_token();

$page_title = "User Management";
$role_label = "Administrator";
$nav_links = [
    ['label' => 'Dashboard',  'url' => 'dashboard.php'],
    ['label' => 'Users',      'url' => 'users.php', 'active' => true],
    ['label' => 'Audit Logs', 'url' => 'audit_logs.php'],
    ['label' => 'Reports',    'url' => 'reports.php'],
    ['label' => 'Backup & Recovery', 'url' => 'backup.php'],
];

ob_start();
?>
<div class="card">
    <div class="toolbar">
        <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Search by username or name…">
        </div>
        <select id="roleFilter" class="status-filter">
            <option value="">All roles</option>
            <option value="admin">Admin</option>
            <option value="doctor">Doctor</option>
            <option value="staff">Staff</option>
        </select>
        <button type="button" class="btn-primary" id="btnAddUser">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create User
        </button>
    </div>

    <table class="data-table" id="usersTable">
        <thead>
            <tr>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="usersBody"></tbody>
    </table>
    <div id="emptyState" class="empty-state" style="display:none;">
        <p>No users found.</p>
    </div>
    <div id="loadingState" class="loading-state">Loading users…</div>
</div>

<!-- ═══ Create User Modal ═══ -->
<div class="modal-overlay" id="addModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseAdd" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>Create User Account</h2>
        <p>All fields are required.</p>
    </div>

    <div class="alert alert-error" id="addAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="addAlertText"></span>
    </div>

    <form id="addForm" novalidate>
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="field"><label>Full Name *</label><input type="text" name="full_name" required></div>
        <div class="field"><label>Username *</label><input type="text" name="username" placeholder="e.g. j.delacruz" required></div>
        <div class="field">
            <label>Temporary Password *</label>
            <input type="password" name="password" id="newUserPassword" required minlength="8">
            <div class="field-hint">The user should change this after first login.</div>
        </div>
        <div class="field">
            <label>Role *</label>
            <select name="role" required>
                <option value="">Select a role…</option>
                <option value="staff">Staff / Nurse</option>
                <option value="doctor">Doctor</option>
                <option value="admin">Administrator</option>
            </select>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <span id="submitBtnLabel">Create Account</span>
        </button>
    </form>
</div>
</div>

<!-- ═══ Edit User Modal ═══ -->
<div class="modal-overlay" id="editModal">
<div class="modal-card">
    <button type="button" class="modal-close" id="btnCloseEdit" aria-label="Close">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-heading">
        <h2>Edit User Account</h2>
        <p>A reason is required for any change to another user's account.</p>
    </div>

    <div class="alert alert-error" id="editAlert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="editAlertText"></span>
    </div>

    <form id="editForm" novalidate>
        <input type="hidden" name="user_id" id="editUserId">
        <input type="hidden" name="csrf_token" id="editCsrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="field"><label>Full Name *</label><input type="text" name="full_name" id="editFullName" required></div>
        <div class="field"><label>Username *</label><input type="text" name="username" id="editUsername" required></div>
        <div class="field">
            <label>Reset Password (optional)</label>
            <input type="password" name="new_password" id="editNewPassword" minlength="8" placeholder="Leave blank to keep current password">
        </div>
        <div class="field">
            <label>Reason for this change *</label>
            <textarea name="reason" rows="2" required placeholder="e.g. Corrected misspelled name at registration; requested by the user"></textarea>
            <div class="field-hint">This is recorded in the audit log and attributed to your account.</div>
        </div>

        <button type="submit" class="btn-submit" id="editSubmitBtn">
            <span id="editSubmitLabel">Save Changes</span>
        </button>
    </form>
</div>
</div>

<style>
.toolbar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-wrap svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
.search-wrap input { width: 100%; padding: 10px 14px 10px 38px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.87rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; }
.search-wrap input:focus { border-color: var(--indigo); background: var(--surface); }
.status-filter { padding: 10px 12px; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; }
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

.role-tag { display: inline-flex; padding: 3px 11px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; }
.role-admin  { background: #F3E8FF; color: #7C3AED; }
.role-doctor { background: var(--teal-tint); color: var(--teal); }
.role-staff  { background: var(--indigo-tint); color: var(--indigo-dark); }

.status-pill { display: inline-flex; padding: 3px 11px; border-radius: 999px; font-size: 0.74rem; font-weight: 700; }
.status-active   { background: var(--teal-tint); color: var(--teal); }
.status-inactive { background: #FDECEA; color: #B3261E; }

.btn-mini {
    padding: 6px 12px; border-radius: 999px; border: 1px solid var(--border); background: var(--surface);
    font-size: 0.76rem; font-weight: 700; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; white-space: nowrap;
}
.btn-mini.deactivate { color: #B3261E; border-color: #f2c6c3; }
.btn-mini.deactivate:hover { background: #FDECEA; }
.btn-mini.activate { color: var(--teal); border-color: #cfe4e2; }
.btn-mini.activate:hover { background: var(--teal-tint); }
.btn-mini:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-mini.edit { color: var(--indigo); border-color: #d5d0f5; }
.btn-mini.edit:hover { background: var(--indigo-tint); }
.row-actions-inline { display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap; }

.empty-state, .loading-state { text-align: center; padding: 48px 20px; color: var(--text-muted); font-size: 0.85rem; display: none; }
.loading-state { display: block; }

.modal-overlay {
    position: fixed; inset: 0; z-index: 200; background: rgba(27,23,48,0.35); backdrop-filter: blur(6px);
    display: none; align-items: flex-start; justify-content: center; padding: 40px 20px; opacity: 0;
    transition: opacity 0.2s ease; overflow-y: auto;
}
.modal-overlay.open { display: flex; }
.modal-overlay.show { opacity: 1; }
.modal-card {
    position: relative; width: 100%; max-width: 440px; background: var(--surface); border: 1px solid var(--border);
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

.field { margin-bottom: 16px; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; margin-bottom: 6px; }
.field input, .field select, .field textarea {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: 0.86rem; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); outline: none; resize: vertical;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color: var(--indigo); background: var(--surface); }
.field-hint { font-size: 0.74rem; color: var(--text-muted); margin-top: 5px; }

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

<script src="<?= base_url('assets/js/password-strength.js') ?>"></script>
<script>
attachPasswordStrength('newUserPassword');
attachPasswordStrength('editNewPassword');

const usersBody    = document.getElementById('usersBody');
const emptyState   = document.getElementById('emptyState');
const loadingState = document.getElementById('loadingState');
const searchInput  = document.getElementById('searchInput');
const roleFilter   = document.getElementById('roleFilter');
let searchDebounce = null;

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}
function roleLabel(r) {
    const map = { admin: 'Admin', doctor: 'Doctor', staff: 'Staff' };
    return map[r] || r;
}

async function loadUsers() {
    loadingState.style.display = 'block';
    emptyState.style.display = 'none';
    usersBody.innerHTML = '';

    const params = new URLSearchParams();
    if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
    if (roleFilter.value) params.set('role', roleFilter.value);

    try {
        const response = await fetch('<?= base_url("api/admin_users_list.php") ?>?' + params.toString());
        const data = await response.json();
        loadingState.style.display = 'none';

        if (!data.success || data.users.length === 0) {
            emptyState.style.display = 'block';
            return;
        }

        data.users.forEach(u => {
            const row = document.createElement('tr');
            const isSelf = u.user_id == <?= (int)$_SESSION['user_id'] ?>;
            const toggleBtn = u.account_status === 'active'
                ? `<button type="button" class="btn-mini deactivate" data-id="${u.user_id}" data-status="inactive" ${isSelf ? 'disabled title="You cannot deactivate your own account"' : ''}>Deactivate</button>`
                : `<button type="button" class="btn-mini activate" data-id="${u.user_id}" data-status="active">Activate</button>`;
            const editBtn = isSelf
                ? `<button type="button" class="btn-mini edit" disabled title="Use My Account to update your own profile">Edit</button>`
                : `<button type="button" class="btn-mini edit" data-id="${u.user_id}" data-fullname="${escapeHtml(u.full_name)}" data-username="${escapeHtml(u.username)}">Edit</button>`;

            row.innerHTML = `
                <td>${escapeHtml(u.username)}</td>
                <td>${escapeHtml(u.full_name)}</td>
                <td><span class="role-tag role-${u.role}">${roleLabel(u.role)}</span></td>
                <td><span class="status-pill status-${u.account_status}">${u.account_status === 'active' ? 'Active' : 'Inactive'}</span></td>
                <td>${u.last_login_at || 'Never'}</td>
                <td><div class="row-actions-inline">${editBtn}${toggleBtn}</div></td>
            `;
            usersBody.appendChild(row);
        });

        document.querySelectorAll('.btn-mini[data-status]').forEach(btn => {
            btn.addEventListener('click', () => toggleStatus(btn.dataset.id, btn.dataset.status));
        });
        document.querySelectorAll('.btn-mini.edit').forEach(btn => {
            btn.addEventListener('click', () => openEditModal(btn.dataset.id, btn.dataset.fullname, btn.dataset.username));
        });
    } catch (err) {
        loadingState.style.display = 'none';
        emptyState.style.display = 'block';
    }
}

async function toggleStatus(userId, newStatus) {
    const action = newStatus === 'active' ? 'reactivate' : 'deactivate';
    const result = await Swal.fire({
        title: `Are you sure you want to ${action} this account?`,
        text: newStatus === 'inactive' ? 'This user will no longer be able to log in.' : 'This user will be able to log in again.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Yes, ${action} it`,
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', newStatus);
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    try {
        const response = await fetch('<?= base_url("api/admin_users_toggle_status.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('csrfToken').value = data.csrf_token;

        if (data.success) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Account updated', showConfirmButton: false, timer: 2500, timerProgressBar: true });
            loadUsers();
        } else {
            Swal.fire({ icon: 'error', title: 'Could not update', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
    }
}

searchInput.addEventListener('input', () => { clearTimeout(searchDebounce); searchDebounce = setTimeout(loadUsers, 350); });
roleFilter.addEventListener('change', loadUsers);
loadUsers();

/* ── Create User modal ── */
const addModal = document.getElementById('addModal');
function openAddModal() { document.getElementById('addForm').reset(); document.getElementById('addAlert').classList.remove('show'); addModal.classList.add('open'); requestAnimationFrame(() => addModal.classList.add('show')); document.body.style.overflow = 'hidden'; }
function closeAddModal() { addModal.classList.remove('show'); document.body.style.overflow = ''; setTimeout(() => addModal.classList.remove('open'), 200); }
document.getElementById('btnAddUser').addEventListener('click', openAddModal);
document.getElementById('btnCloseAdd').addEventListener('click', closeAddModal);
addModal.addEventListener('click', (e) => { if (e.target === addModal) closeAddModal(); });

const addForm      = document.getElementById('addForm');
const addAlert       = document.getElementById('addAlert');
const addAlertText  = document.getElementById('addAlertText');
const submitBtn      = document.getElementById('submitBtn');
const submitLabel   = document.getElementById('submitBtnLabel');

addForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    addAlert.classList.remove('show');
    submitBtn.disabled = true;
    submitLabel.innerHTML = '<span class="spinner"></span> Creating…';

    try {
        const formData = new FormData(addForm);
        const response = await fetch('<?= base_url("api/admin_users_add.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('csrfToken').value = data.csrf_token;

        if (data.success) {
            closeAddModal();
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Account created', showConfirmButton: false, timer: 2500, timerProgressBar: true });
            loadUsers();
        } else {
            addAlertText.textContent = data.message || 'Could not create account.';
            addAlert.classList.add('show');
        }
    } catch (err) {
        addAlertText.textContent = 'Could not reach the server. Please try again.';
        addAlert.classList.add('show');
    } finally {
        submitBtn.disabled = false;
        submitLabel.textContent = 'Create Account';
    }
});

/* ── Edit User modal ── */
const editModal = document.getElementById('editModal');
function openEditModal(userId, fullName, username) {
    document.getElementById('editUserId').value = userId;
    document.getElementById('editFullName').value = fullName;
    document.getElementById('editUsername').value = username;
    document.getElementById('editForm').reset();
    document.getElementById('editUserId').value = userId; // reset() clears hidden fields too
    document.getElementById('editFullName').value = fullName;
    document.getElementById('editUsername').value = username;
    document.getElementById('editAlert').classList.remove('show');
    editModal.classList.add('open');
    requestAnimationFrame(() => editModal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    editModal.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => editModal.classList.remove('open'), 200);
}
document.getElementById('btnCloseEdit').addEventListener('click', closeEditModal);
editModal.addEventListener('click', (e) => { if (e.target === editModal) closeEditModal(); });

const editForm       = document.getElementById('editForm');
const editAlert        = document.getElementById('editAlert');
const editAlertText   = document.getElementById('editAlertText');
const editSubmitBtn    = document.getElementById('editSubmitBtn');
const editSubmitLabel = document.getElementById('editSubmitLabel');

editForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    editAlert.classList.remove('show');

    const result = await Swal.fire({
        title: 'Save these changes?',
        text: 'This will be recorded in the audit log with the reason you provided.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save changes',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!result.isConfirmed) return;

    editSubmitBtn.disabled = true;
    editSubmitLabel.innerHTML = '<span class="spinner"></span> Saving…';

    try {
        const formData = new FormData(editForm);
        const response = await fetch('<?= base_url("api/admin_users_update.php") ?>', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.csrf_token) document.getElementById('editCsrfToken').value = data.csrf_token;

        if (data.success) {
            closeEditModal();
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'User updated', showConfirmButton: false, timer: 2500, timerProgressBar: true });
            loadUsers();
        } else {
            editAlertText.textContent = data.message || 'Could not save changes.';
            editAlert.classList.add('show');
        }
    } catch (err) {
        editAlertText.textContent = 'Could not reach the server. Please try again.';
        editAlert.classList.add('show');
    } finally {
        editSubmitBtn.disabled = false;
        editSubmitLabel.textContent = 'Save Changes';
    }
});
</script>