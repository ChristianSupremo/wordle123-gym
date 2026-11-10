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
$csrf = member_csrf_token();

$page_title = 'My Profile';
include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">My Profile</h1>
</div>

<div class="mp-stack">
    <div class="dashboard-card">
        <div class="card-header-flex"><h2 class="card-title">Account</h2></div>
        <dl class="mp-info-list">
            <div><dt>Full Name</dt><dd><?= member_h(trim(($member['FirstName'] ?? '') . ' ' . ($member['LastName'] ?? ''))) ?></dd></div>
            <div><dt>Email (sign-in)</dt><dd><?= member_h($member['Email'] ?? '—') ?></dd></div>
            <div><dt>Member ID</dt><dd>GYM-<?= $memberId ?></dd></div>
            <div><dt>Gender</dt><dd><?= member_h($member['Gender'] ?? '—') ?></dd></div>
            <div><dt>Date of Birth</dt><dd><?= !empty($member['DateOfBirth']) ? date('M d, Y', strtotime($member['DateOfBirth'])) : '—' ?></dd></div>
            <div><dt>Member Since</dt><dd><?= !empty($member['JoinDate']) ? date('M d, Y', strtotime($member['JoinDate'])) : '—' ?></dd></div>
        </dl>
        <p class="mp-subtitle" style="margin-top:18px;">To change your name, email or date of birth, please contact the front desk.</p>
    </div>

    <div class="dashboard-card">
        <div class="card-header-flex"><h2 class="card-title">Contact &amp; Emergency Details</h2></div>
        <form method="post" action="profile.php">
            <input type="hidden" name="csrf_token" value="<?= member_h($csrf) ?>">
            <input type="hidden" name="action" value="update_profile">
            <div class="mp-form-grid">
                <div class="form-group-modern">
                    <label for="PhoneNo">Phone Number</label>
                    <input class="form-control-modern" type="tel" id="PhoneNo" name="PhoneNo" maxlength="20" value="<?= member_h($member['PhoneNo'] ?? '') ?>">
                </div>
                <div class="form-group-modern full-width">
                    <label for="Address">Address</label>
                    <input class="form-control-modern" type="text" id="Address" name="Address" maxlength="255" value="<?= member_h($member['Address'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label for="City">City</label>
                    <input class="form-control-modern" type="text" id="City" name="City" maxlength="100" value="<?= member_h($member['City'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label for="Province">Province</label>
                    <input class="form-control-modern" type="text" id="Province" name="Province" maxlength="100" value="<?= member_h($member['Province'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label for="Zipcode">Zip Code</label>
                    <input class="form-control-modern" type="text" id="Zipcode" name="Zipcode" maxlength="20" value="<?= member_h($member['Zipcode'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label for="EmergencyContactName">Emergency Contact Name</label>
                    <input class="form-control-modern" type="text" id="EmergencyContactName" name="EmergencyContactName" maxlength="100" value="<?= member_h($member['EmergencyContactName'] ?? '') ?>">
                </div>
                <div class="form-group-modern">
                    <label for="EmergencyContactNumber">Emergency Contact Number</label>
                    <input class="form-control-modern" type="tel" id="EmergencyContactNumber" name="EmergencyContactNumber" maxlength="20" value="<?= member_h($member['EmergencyContactNumber'] ?? '') ?>">
                </div>
            </div>
            <div class="mp-form-actions">
                <button type="submit" class="btn-primary-modern"><i class="bi bi-check-lg"></i> Save changes</button>
            </div>
        </form>
    </div>

    <div class="dashboard-card">
        <div class="card-header-flex"><h2 class="card-title">Change Password</h2></div>
        <form method="post" action="profile.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= member_h($csrf) ?>">
            <input type="hidden" name="action" value="change_password">
            <div class="mp-form-grid">
                <div class="form-group-modern full-width">
                    <label for="current_password">Current Password</label>
                    <input class="form-control-modern" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                </div>
                <div class="form-group-modern">
                    <label for="new_password">New Password (at least 10 characters)</label>
                    <input class="form-control-modern" type="password" id="new_password" name="new_password" minlength="10" autocomplete="new-password" required>
                    <small id="caps_warning_profile" style="color:#e53e3e; display:none; font-size:13px; margin-top:5px;">⚠ Caps Lock is ON</small>
                </div>
                <div class="form-group-modern">
                    <label for="confirm_password">Confirm New Password</label>
                    <input class="form-control-modern" type="password" id="confirm_password" name="confirm_password" minlength="10" autocomplete="new-password" required>
                </div>
            </div>
            <div class="mp-form-actions">
                <button type="submit" class="btn-primary-modern"><i class="bi bi-shield-lock"></i> Update password</button>
            </div>
        </form>
    </div>
</div>

<?php
$page_scripts = '<script>setTimeout(() => enableCapsLockWarning("new_password", "caps_warning_profile"), 100);</script>';
include __DIR__ . '/includes/member_footer.php';
