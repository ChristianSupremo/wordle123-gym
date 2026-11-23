<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = false;
$member = null;

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    $error = 'This invitation link is invalid.';
} else {
    $stmt = $pdo->prepare(
        "SELECT i.InvitationID, i.MemberID, i.ExpiresAt, i.UsedAt,
                m.FirstName, m.LastName, m.Email
         FROM MemberAccountInvitations i
         INNER JOIN member m ON m.MemberID = i.MemberID
         WHERE i.TokenHash = ?
         ORDER BY i.InvitationID DESC LIMIT 1"
    );
    $stmt->execute([hash('sha256', $token)]);
    $member = $stmt->fetch();

    if (!$member || $member['UsedAt'] !== null || strtotime($member['ExpiresAt']) < time()) {
        $error = 'This invitation has expired or has already been used. Please ask gym staff for a new invitation.';
        $member = null;
    } else {
        $existing = $pdo->prepare("SELECT MemberAccountID FROM MemberAccounts WHERE MemberID = ? OR Username = ? LIMIT 1");
        $existing->execute([$member['MemberID'], $member['Email']]);
        if ($existing->fetch()) {
            $error = 'An account already exists for this member. Please sign in or contact gym staff.';
            $member = null;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $member) {
    if (!verify_member_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($password) < 10) {
            $error = 'Your password must contain at least 10 characters.';
        } elseif ($password !== $confirm) {
            $error = 'The passwords do not match.';
        } else {
            try {
                $pdo->beginTransaction();
                $lock = $pdo->prepare(
                    "SELECT InvitationID, MemberID, ExpiresAt, UsedAt
                     FROM MemberAccountInvitations
                     WHERE InvitationID = ? AND TokenHash = ?
                     FOR UPDATE"
                );
                $lock->execute([$member['InvitationID'], hash('sha256', $token)]);
                $locked = $lock->fetch();
                if (!$locked || $locked['UsedAt'] !== null || strtotime($locked['ExpiresAt']) < time()) {
                    throw new RuntimeException('This invitation is no longer valid. Request a new one from gym staff.');
                }

                $create = $pdo->prepare(
                    "INSERT INTO MemberAccounts (MemberID, Username, PasswordHash, AccountStatus)
                     VALUES (?, ?, ?, 'Active')"
                );
                $create->execute([
                    $member['MemberID'],
                    $member['Email'],
                    password_hash($password, PASSWORD_DEFAULT)
                ]);

                $pdo->prepare("UPDATE MemberAccountInvitations SET UsedAt = NOW() WHERE InvitationID = ?")
                    ->execute([$member['InvitationID']]);
                $pdo->commit();
                $success = true;
                $member = null;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = $e instanceof PDOException
                    ? 'Unable to create the account. Please contact gym staff.'
                    : $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex,nofollow">
    <title>Activate Member Account | NEWYOU</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style/style.css">
    <link rel="stylesheet" href="../assets/css/style/payment_modals.css">
</head>
<body class="login-page">
    <div class="login-container">
        <section class="left-section" aria-label="NEWYOU member portal">
            <div class="login-brand-mark">
                <img src="../assets/css/style/logo.png" alt="NEWYOU">
            </div>
            <div class="login-hero-copy">
                <p class="login-eyebrow">NEWYOU MEMBER PORTAL</p>
                <h2>Welcome to<br><span>your account.</span></h2>
                <p class="login-hero-description">Set a password to start tracking your membership.</p>
                <div class="login-feature-list" aria-label="Portal features">
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-card-checklist"></i></span><span>Membership</span></div>
                    <span class="login-feature-divider" aria-hidden="true"></span>
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-calendar2-check"></i></span><span>Attendance</span></div>
                    <span class="login-feature-divider" aria-hidden="true"></span>
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-credit-card"></i></span><span>Payments</span></div>
                </div>
            </div>
        </section>

        <section class="right-section" aria-label="Activate account">
            <div class="login-form">
                <div class="logo">
                    <img src="../assets/css/style/logo.png" class="logo-icon" alt="NEWYOU">
                </div>

                <?php if ($success): ?>
                    <h1>Account activated</h1>
                    <p class="subtitle">Your NEWYOU member account is ready.</p>
                    <a class="sign-in-btn" href="login.php?activated=1" style="display:block;text-align:center;text-decoration:none;">Continue to sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                <?php elseif ($member): ?>
                    <h1>Activate your account</h1>
                    <p class="subtitle">Welcome, <?= htmlspecialchars($member['FirstName'] . ' ' . $member['LastName'], ENT_QUOTES, 'UTF-8') ?>. Create a password to continue.</p>
                    <form method="post" action="activate.php" id="activateForm">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(member_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="password">Password (at least 10 characters)</label>
                            <div class="password-input-wrapper">
                                <i class="bi bi-lock login-password-icon" aria-hidden="true"></i>
                                <input type="password" id="password" name="password" placeholder="Create a password" minlength="10" autocomplete="new-password" required>
                            </div>
                            <small id="caps_warning_login" style="color:#e53e3e; display:none; font-size:13px; margin-top:5px;">⚠ Caps Lock is ON</small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm password</label>
                            <div class="password-input-wrapper">
                                <i class="bi bi-lock login-password-icon" aria-hidden="true"></i>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" minlength="10" autocomplete="new-password" required>
                            </div>
                        </div>
                        <button type="submit" class="sign-in-btn">Create member account <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                    </form>
                <?php else: ?>
                    <h1>Invitation unavailable</h1>
                    <p class="subtitle">Please contact gym staff and request a new account invitation.</p>
                    <a class="sign-in-btn" href="login.php" style="display:block;text-align:center;text-decoration:none;">Member sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                <?php endif; ?>

                <div class="login-secure-note"><span class="login-secure-rule"></span><i class="bi bi-shield-check" aria-hidden="true"></i><span>Secure member access.</span><span class="login-secure-rule"></span></div>
            </div>
        </section>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/capslock_detector.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($error !== ''): ?>
                if (typeof toast !== 'undefined' && toast.error) { toast.error(<?= json_encode($error) ?>); }
            <?php endif; ?>
        });
        setTimeout(() => { enableCapsLockWarning("password", "caps_warning_login"); }, 100);
    </script>
</body>
</html>
