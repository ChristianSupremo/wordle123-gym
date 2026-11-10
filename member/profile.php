<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';
require_once __DIR__ . '/../includes/member_data.php';
check_member_login($pdo);

$memberId = (int) $_SESSION['member_id'];
$accountId = (int) $_SESSION['member_account_id'];

function member_flash(string $type, string $message): void
{
    $_SESSION['member_flash'] = ['type' => $type, 'message' => $message];
}

function member_valid_phone(string $v): bool
{
    return $v === '' || (bool) preg_match('/^[0-9+\-()\s]{7,20}$/', $v);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_member_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        member_flash('error', 'Your session expired. Please try again.');
        header('Location: profile.php');
        exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        // Only these fields are member-editable. Name, email, DOB and status stay staff-managed.
        $fields = [];
        foreach (['PhoneNo', 'Address', 'City', 'Province', 'Zipcode', 'EmergencyContactName', 'EmergencyContactNumber'] as $f) {
            $fields[$f] = trim((string) ($_POST[$f] ?? ''));
        }
        $limits = ['PhoneNo' => 20, 'Address' => 255, 'City' => 100, 'Province' => 100, 'Zipcode' => 20,
                   'EmergencyContactName' => 100, 'EmergencyContactNumber' => 20];

        $error = '';
        foreach ($limits as $f => $max) {
            if (mb_strlen($fields[$f]) > $max) {
                $error = 'One of the fields is too long.';
            }
        }
        if (!member_valid_phone($fields['PhoneNo']) || !member_valid_phone($fields['EmergencyContactNumber'])) {
            $error = 'Please enter valid phone numbers (digits, +, -, spaces and parentheses only).';
        }

        if ($error !== '') {
            member_flash('error', $error);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE member SET PhoneNo = ?, Address = ?, City = ?, Province = ?, Zipcode = ?,
                        EmergencyContactName = ?, EmergencyContactNumber = ?
                 WHERE MemberID = ?"
            );
            $stmt->execute([
                $fields['PhoneNo'] ?: null, $fields['Address'] ?: null, $fields['City'] ?: null,
                $fields['Province'] ?: null, $fields['Zipcode'] ?: null,
                $fields['EmergencyContactName'] ?: null, $fields['EmergencyContactNumber'] ?: null,
                $memberId,
            ]);
            member_flash('success', 'Your profile has been updated.');
        }
        header('Location: profile.php');
        exit();
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT PasswordHash FROM MemberAccounts WHERE MemberAccountID = ? AND MemberID = ? LIMIT 1");
        $stmt->execute([$accountId, $memberId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            member_flash('error', 'Your current password is incorrect.');
        } elseif (strlen($new) < 10) {
            member_flash('error', 'Your new password must contain at least 10 characters.');
        } elseif ($new !== $confirm) {
            member_flash('error', 'The new passwords do not match.');
        } elseif (hash_equals($current, $new)) {
            member_flash('error', 'Your new password must be different from your current password.');
        } else {
            $pdo->prepare("UPDATE MemberAccounts SET PasswordHash = ? WHERE MemberAccountID = ? AND MemberID = ?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $accountId, $memberId]);
            session_regenerate_id(true);
            member_flash('success', 'Your password has been changed.');
        }
        header('Location: profile.php');
        exit();
    }

    header('Location: profile.php');
    exit();
}

$member = member_get_profile($pdo, $memberId);
$membership = member_get_current_membership($pdo, $memberId);
$csrf = member_csrf_token();

$full_name = trim(($member['FirstName'] ?? '') . ' ' . ($member['LastName'] ?? ''));
$initials = strtoupper(mb_substr($member['FirstName'] ?? '', 0, 1) . mb_substr($member['LastName'] ?? '', 0, 1));
$ms_status = $membership['EffectiveStatus'] ?? null;

$page_title = 'My Profile';
include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="mp-subtitle">Manage your contact details and account security.</p>
</div>

<!-- Profile summary -->
<div class="dashboard-card profile-hero">
    <div class="profile-avatar"><?= member_h($initials ?: 'M') ?></div>
    <div class="profile-hero-info">
        <h2 class="profile-name"><?= member_h($full_name) ?></h2>
        <p class="profile-email"><i class="bi bi-envelope"></i> <?= member_h($member['Email'] ?? '—') ?></p>
        <div class="profile-chips">
            <span class="profile-chip"><i class="bi bi-hash"></i> GYM-<?= $memberId ?></span>
            <?php if (!empty($member['JoinDate'])): ?>
                <span class="profile-chip"><i class="bi bi-calendar-check"></i> Member since <?= date('M Y', strtotime($member['JoinDate'])) ?></span>
            <?php endif; ?>
            <?php if ($membership): ?>
                <span class="plan-badge"><?= member_h($membership['PlanName'] ?? 'No plan') ?></span>
                <span class="status-badge <?= strtolower($ms_status) ?>"><?= member_h($ms_status) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <a class="btn-secondary-modern profile-hero-action" href="qr.php"><i class="bi bi-qr-code"></i> My QR code</a>
</div>

<div class="profile-grid">
    <!-- Editable details -->
    <form method="post" action="profile.php" id="profileForm" class="dashboard-card profile-form">
        <input type="hidden" name="csrf_token" value="<?= member_h($csrf) ?>">
        <input type="hidden" name="action" value="update_profile">

        <div class="profile-section">
            <h3 class="profile-section-title"><i class="bi bi-telephone"></i> Contact details</h3>
            <div class="mp-form-grid">
                <div class="form-group-modern">
                    <label class="form-label-modern" for="PhoneNo">Phone number</label>
                    <input class="form-control-modern" type="tel" id="PhoneNo" name="PhoneNo" maxlength="20" placeholder="e.g. 0917 123 4567" value="<?= member_h($member['PhoneNo'] ?? '') ?>">
                </div>
                <div class="form-group-modern full-width">
                    <label class="form-label-modern" for="Address">Street address</label>
                    <input class="form-control-modern" type="text" id="Address" name="Address" maxlength="255" placeholder="House no., street, barangay" value="<?= member_h($member['Address'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern" for="City">City</label>
                    <input class="form-control-modern" type="text" id="City" name="City" maxlength="100" value="<?= member_h($member['City'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern" for="Province">Province</label>
                    <input class="form-control-modern" type="text" id="Province" name="Province" maxlength="100" value="<?= member_h($member['Province'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern" for="Zipcode">Zip code</label>
                    <input class="form-control-modern" type="text" id="Zipcode" name="Zipcode" maxlength="20" value="<?= member_h($member['Zipcode'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h3 class="profile-section-title"><i class="bi bi-heart-pulse"></i> Emergency contact</h3>
            <div class="mp-form-grid">
                <div class="form-group-modern">
                    <label class="form-label-modern" for="EmergencyContactName">Contact name</label>
                    <input class="form-control-modern" type="text" id="EmergencyContactName" name="EmergencyContactName" maxlength="100" value="<?= member_h($member['EmergencyContactName'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label class="form-label-modern" for="EmergencyContactNumber">Contact number</label>
                    <input class="form-control-modern" type="tel" id="EmergencyContactNumber" name="EmergencyContactNumber" maxlength="20" value="<?= member_h($member['EmergencyContactNumber'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="profile-form-footer">
            <span class="profile-dirty" id="profileDirty" hidden><i class="bi bi-dot"></i> Unsaved changes</span>
            <button type="reset" class="btn-secondary-modern" id="profileReset">Reset</button>
            <button type="submit" class="btn-primary-modern" id="profileSave" disabled><i class="bi bi-check-lg"></i> Save changes</button>
        </div>
    </form>

    <aside class="profile-side">
        <!-- Read-only details -->
        <div class="dashboard-card">
            <h3 class="profile-section-title"><i class="bi bi-person-vcard"></i> Personal details</h3>
            <ul class="profile-facts">
                <li><span>Full name</span><strong><?= member_h($full_name) ?></strong></li>
                <li><span>Gender</span><strong><?= member_h($member['Gender'] ?? '—') ?></strong></li>
                <li><span>Date of birth</span><strong><?= !empty($member['DateOfBirth']) ? date('M d, Y', strtotime($member['DateOfBirth'])) : '—' ?></strong></li>
                <li><span>Email (sign-in)</span><strong><?= member_h($member['Email'] ?? '—') ?></strong></li>
            </ul>
            <p class="profile-note"><i class="bi bi-info-circle"></i> To change these, please ask the front desk.</p>
        </div>

        <!-- Password -->
        <form method="post" action="profile.php" id="passwordForm" class="dashboard-card" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= member_h($csrf) ?>">
            <input type="hidden" name="action" value="change_password">
            <h3 class="profile-section-title"><i class="bi bi-shield-lock"></i> Change password</h3>

            <div class="form-group-modern">
                <label class="form-label-modern" for="current_password">Current password</label>
                <div class="pw-field">
                    <input class="form-control-modern" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    <button type="button" class="pw-toggle" data-target="current_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <div class="form-group-modern">
                <label class="form-label-modern" for="new_password">New password</label>
                <div class="pw-field">
                    <input class="form-control-modern" type="password" id="new_password" name="new_password" minlength="10" autocomplete="new-password" required>
                    <button type="button" class="pw-toggle" data-target="new_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
                <small id="caps_warning_profile" class="pw-hint" style="color:#e53e3e; display:none;">⚠ Caps Lock is ON</small>
                <small class="pw-hint" id="pwLength">At least 10 characters</small>
            </div>
            <div class="form-group-modern">
                <label class="form-label-modern" for="confirm_password">Confirm new password</label>
                <div class="pw-field">
                    <input class="form-control-modern" type="password" id="confirm_password" name="confirm_password" minlength="10" autocomplete="new-password" required>
                    <button type="button" class="pw-toggle" data-target="confirm_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
                <small class="pw-hint" id="pwMatch"></small>
            </div>
            <button type="submit" class="btn-primary-modern profile-pw-submit"><i class="bi bi-shield-check"></i> Update password</button>
        </form>
    </aside>
</div>

<?php
$page_scripts = <<<'JS'
<script>
setTimeout(() => enableCapsLockWarning("new_password", "caps_warning_profile"), 100);

// Enable "Save changes" only when something actually changed.
(function () {
    const form = document.getElementById('profileForm');
    const save = document.getElementById('profileSave');
    const dirty = document.getElementById('profileDirty');
    const fields = Array.from(form.querySelectorAll('input[type="text"], input[type="tel"]'));
    const initial = fields.map(f => f.value);
    function update() {
        const changed = fields.some((f, i) => f.value !== initial[i]);
        save.disabled = !changed;
        dirty.hidden = !changed;
    }
    form.addEventListener('input', update);
    form.addEventListener('reset', () => setTimeout(update, 0));
})();

// Show/hide password buttons.
document.querySelectorAll('.pw-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = '<i class="bi bi-eye' + (show ? '-slash' : '') + '"></i>';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
});

// Live password hints.
(function () {
    const np = document.getElementById('new_password');
    const cp = document.getElementById('confirm_password');
    const len = document.getElementById('pwLength');
    const match = document.getElementById('pwMatch');
    function check() {
        const ok = np.value.length >= 10;
        len.textContent = ok ? '✓ Long enough' : 'At least 10 characters';
        len.className = 'pw-hint' + (ok ? ' ok' : '');
        if (cp.value === '') { match.textContent = ''; match.className = 'pw-hint'; return; }
        const same = np.value === cp.value;
        match.textContent = same ? '✓ Passwords match' : 'Passwords do not match yet';
        match.className = 'pw-hint ' + (same ? 'ok' : 'bad');
    }
    np.addEventListener('input', check);
    cp.addEventListener('input', check);
})();
</script>
JS;
include __DIR__ . '/includes/member_footer.php';
