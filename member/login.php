<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';

if (!empty($_SESSION['member_account_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_member_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare(
            "SELECT ma.MemberAccountID, ma.MemberID, ma.PasswordHash, ma.AccountStatus,
                    m.FirstName, m.LastName
             FROM MemberAccounts ma
             INNER JOIN member m ON m.MemberID = ma.MemberID
             WHERE ma.Username = ?
             LIMIT 1"
        );
        $stmt->execute([$username]);
        $account = $stmt->fetch();

        if ($account && $account['AccountStatus'] === 'Active'
            && password_verify($password, $account['PasswordHash'])) {
            session_regenerate_id(true);
            $_SESSION['member_account_id'] = (int) $account['MemberAccountID'];
            $_SESSION['member_id'] = (int) $account['MemberID'];
            $_SESSION['member_name'] = trim($account['FirstName'] . ' ' . $account['LastName']);
            unset($_SESSION['staff_id'], $_SESSION['staff_name'], $_SESSION['role_id']);

            $pdo->prepare("UPDATE MemberAccounts SET LastLogin = NOW() WHERE MemberAccountID = ?")
                ->execute([$account['MemberAccountID']]);

            header('Location: dashboard.php');
            exit();
        }
        $error = 'Invalid credentials or your member account is disabled.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Member Sign In | NEWYOU</title>
    <link rel="stylesheet" href="../assets/css/style/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .member-auth-wrap{min-height:100vh;display:grid;place-items:center;padding:24px;background:#f4f6f8}
        .member-auth-card{width:min(100%,440px);background:#fff;border-radius:18px;padding:36px;box-shadow:0 18px 50px rgba(15,23,42,.09)}
        .member-auth-brand{text-align:center;margin-bottom:28px}.member-auth-brand img{max-width:150px;max-height:58px;object-fit:contain}
        .member-auth-card h1{font-size:27px;margin:0 0 8px;color:#17202b}.member-auth-muted{color:#64748b;margin:0 0 24px}
        .member-auth-field{display:block;margin:16px 0 7px;font-weight:600;color:#334155}
        .member-auth-card input{width:100%;padding:13px 14px;border:1px solid #dbe2ea;border-radius:9px;font:inherit;box-sizing:border-box}
        .member-auth-submit{width:100%;margin-top:22px;padding:14px;border:0;border-radius:9px;background:#152b36;color:white;font-weight:700;cursor:pointer}
        .member-auth-error{padding:12px;border-radius:8px;background:#fff1f2;color:#be123c;margin-bottom:16px}
        .member-auth-foot{text-align:center;margin-top:22px;color:#64748b;font-size:13px}
        .member-auth-foot a{color:#0f766e}
    </style>
</head>
<body>
<div class="member-auth-wrap">
    <main class="member-auth-card">
        <div class="member-auth-brand"><img src="../assets/css/style/logo.png" alt="NEWYOU"></div>
        <h1>Welcome back</h1>
        <p class="member-auth-muted">Sign in to your NEWYOU member account.</p>
        <?php if ($error !== ''): ?><div class="member-auth-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post" action="login.php" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(member_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="member-auth-field" for="username">Email address</label>
            <input id="username" name="username" type="email" autocomplete="username" required>
            <label class="member-auth-field" for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button class="member-auth-submit" type="submit">Sign in</button>
        </form>
        <div class="member-auth-foot">Staff member? <a href="../index.php">Go to staff sign in</a></div>
    </main>
</div>
</body>
</html>
