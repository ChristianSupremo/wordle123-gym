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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex,nofollow">
    <title>Activate Member Account | NEWYOU</title>
    <link rel="stylesheet" href="../assets/css/style/style.css">
    <style>
        .activate-wrap{min-height:100vh;display:grid;place-items:center;padding:24px;background:#f4f6f8}
        .activate-card{width:min(100%,460px);background:#fff;border-radius:18px;padding:36px;box-shadow:0 18px 50px rgba(15,23,42,.09)}
        .activate-card h1{margin:0 0 8px;color:#17202b}.activate-card p{color:#64748b}
        .activate-card label{display:block;margin:18px 0 7px;font-weight:600}
        .activate-card input{width:100%;box-sizing:border-box;padding:13px;border:1px solid #dbe2ea;border-radius:9px}
        .activate-card button,.activate-card .activate-link{display:block;text-align:center;width:100%;box-sizing:border-box;margin-top:22px;padding:14px;border:0;border-radius:9px;background:#152b36;color:white;font-weight:700;text-decoration:none;cursor:pointer}
        .activate-error{padding:12px;border-radius:8px;background:#fff1f2;color:#be123c}
    </style>
</head>
<body>
<div class="activate-wrap"><main class="activate-card">
    <h1><?= $success ? 'Account activated' : 'Activate your account' ?></h1>
    <?php if ($success): ?>
        <p>Your NEWYOU member account is ready. You can now sign in.</p>
        <a class="activate-link" href="login.php">Continue to member sign in</a>
    <?php else: ?>
        <?php if ($error !== ''): ?><div class="activate-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($member): ?>
            <p>Welcome, <?= htmlspecialchars($member['FirstName'] . ' ' . $member['LastName'], ENT_QUOTES, 'UTF-8') ?>. Create a password to activate your account.</p>
            <form method="post" action="activate.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(member_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <label for="password">Password (at least 10 characters)</label>
                <input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required>
                <label for="confirm_password">Confirm password</label>
                <input id="confirm_password" name="confirm_password" type="password" minlength="10" autocomplete="new-password" required>
                <button type="submit">Create member account</button>
            </form>
        <?php else: ?>
            <p>Please contact gym staff and request a new account invitation.</p>
            <a class="activate-link" href="login.php">Member sign in</a>
        <?php endif; ?>
    <?php endif; ?>
</main></div>
</body>
</html>
