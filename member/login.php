<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';

if (!empty($_SESSION['member_account_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$info = '';
if (isset($_GET['disabled'])) {
    $error = 'Your member account is disabled or no longer available. Please contact gym staff.';
}
if (isset($_GET['activated'])) {
    $info = 'Your account is ready. Sign in to continue.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!verify_member_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (member_login_is_throttled($pdo, $username, $ip)) {
        http_response_code(429);
        $error = 'Too many failed sign-in attempts. Please wait 15 minutes and try again.';
    } else {
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

        // Always run a hash comparison so response time doesn't reveal whether the email exists.
        $hash = $account['PasswordHash'] ?? '$2y$10$.vGA1O9wmRjrwAVXD98HNOgsNpDczlqm3Jq7KnEd1rVAGv3Fykk1a';
        $passwordOk = password_verify($password, $hash);

        if ($account && $account['AccountStatus'] === 'Active' && $passwordOk) {
            session_regenerate_id(true);
            $_SESSION['member_account_id'] = (int) $account['MemberAccountID'];
            $_SESSION['member_id'] = (int) $account['MemberID'];
            $_SESSION['member_name'] = trim($account['FirstName'] . ' ' . $account['LastName']);
            $_SESSION['member_login_success'] = true;
            unset($_SESSION['staff_id'], $_SESSION['staff_name'], $_SESSION['role_id']);

            $pdo->prepare("UPDATE MemberAccounts SET LastLogin = NOW() WHERE MemberAccountID = ?")
                ->execute([$account['MemberAccountID']]);
            member_login_record($pdo, $username, $ip, true);

            header('Location: dashboard.php');
            exit();
        }

        member_login_record($pdo, $username, $ip, false);
        $error = 'Invalid email or password, or your member account is disabled.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Member Sign In | NEWYOU</title>
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
                <h2>Your fitness.<br><span>All in one place.</span></h2>
                <p class="login-hero-description">Track your membership, visits and payments anytime.</p>
                <div class="login-feature-list" aria-label="Portal features">
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-card-checklist"></i></span><span>Membership</span></div>
                    <span class="login-feature-divider" aria-hidden="true"></span>
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-calendar2-check"></i></span><span>Attendance</span></div>
                    <span class="login-feature-divider" aria-hidden="true"></span>
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-credit-card"></i></span><span>Payments</span></div>
                </div>
            </div>
        </section>

        <section class="right-section" aria-label="Sign in">
            <div class="login-form">
                <div class="logo">
                    <img src="../assets/css/style/logo.png" class="logo-icon" alt="NEWYOU">
                </div>

                <h1>Welcome back</h1>
                <p class="subtitle">Sign in to your member account.</p>

                <form action="login.php" method="post" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= member_h(member_csrf_token()) ?>">
                    <div class="form-group">
                        <label for="username">Email address</label>
                        <div class="login-input-wrapper">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input type="email" id="username" name="username" placeholder="Enter your email" autocomplete="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-wrapper">
                            <i class="bi bi-lock login-password-icon" aria-hidden="true"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle-btn-login" onclick="toggleLoginPassword()" aria-label="Show password">
                                <i class="bi bi-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                        <small id="caps_warning_login" style="color:#e53e3e; display:none; font-size:13px; margin-top:5px;">
                            ⚠ Caps Lock is ON
                        </small>
                    </div>

                    <button type="submit" class="sign-in-btn">Sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </form>
                <p class="member-portal-entry" style="text-align:center;margin-top:18px;color:#64748b;font-size:13px;">
                    Gym staff? <a href="../index.php" style="color:#0f766e;font-weight:600;">Go to staff sign in</a>
                </p>
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
            <?php elseif ($info !== ''): ?>
                if (typeof toast !== 'undefined' && toast.success) { toast.success(<?= json_encode($info) ?>); }
            <?php endif; ?>
        });

        function toggleLoginPassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        setTimeout(() => { enableCapsLockWarning("password", "caps_warning_login"); }, 100);

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = this.querySelector('.sign-in-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Signing in...';
        });
    </script>
</body>
</html>
