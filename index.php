<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['staff_id'])) {
    header('Location: dashboard.php');
    exit();
}

$login_status = '';
$login_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE Username = ? AND Status = 'Active'");
    $stmt->execute([$username]);
    $staff = $stmt->fetch();

    if ($staff && password_verify($password, $staff['PasswordHash'])) {

        // UPDATE LAST LOGIN
        $pdo->prepare("UPDATE Staff SET LastLogin = NOW() WHERE StaffID = ?")
            ->execute([$staff['StaffID']]);

        // Password is correct, start a new session
        $_SESSION['staff_id'] = $staff['StaffID'];
        $_SESSION['staff_name'] = $staff['FirstName'] . ' ' . $staff['LastName'];
        $_SESSION['role_id'] = $staff['RoleID'];

        // Set success status for toast
        $login_status = 'success';
        $login_message = 'Login successful! Redirecting to dashboard...';
        
        // Store in session and redirect
        $_SESSION['login_success'] = true;
        header('Location: dashboard.php');
        exit();
    } else {
        // Set error status for toast
        $login_status = 'error';
        $login_message = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Center - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style/style.css">
    <link rel="stylesheet" href="assets/css/style/payment_modals.css">
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Left Section - Existing gym photo with product messaging -->
        <section class="left-section" aria-label="NEWYOU gym management">
            <div class="login-brand-mark">
                <img src="assets/css/style/logo.png" alt="NEWYOU">
            </div>
            <div class="login-hero-copy">
                <p class="login-eyebrow">NEWYOU GYM MANAGEMENT</p>
                <h2>Your gym.<br><span>Better managed.</span></h2>
                <p class="login-hero-description">Everything you need to keep members moving.</p>
                <div class="login-feature-list" aria-label="Platform features">
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-people"></i></span><span>Memberships</span></div>
                    <span class="login-feature-divider" aria-hidden="true"></span>
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-calendar2-check"></i></span><span>Attendance</span></div>
                    <span class="login-feature-divider" aria-hidden="true"></span>
                    <div class="login-feature"><span class="login-feature-icon"><i class="bi bi-credit-card"></i></span><span>Payments</span></div>
                </div>
            </div>
        </section>

        <!-- Right Section - Login Form -->
        <section class="right-section" aria-label="Sign in">
            <div class="login-form">
                <div class="logo">
                    <img src="assets/css/style/logo.png" class="logo-icon">
                </div>
                
                <h1>Welcome back</h1>
                <p class="subtitle">Sign in to manage your gym.</p>

                <form action="index.php" method="post" id="loginForm">
                    <div class="form-group">
                        <label for="username">Email address</label>
                        <div class="login-input-wrapper">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input type="text" id="username" name="username" placeholder="Enter your email" autocomplete="username" required>
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
                        
                        <!-- 🔥 CAPSLOCK WARNING -->
                        <small id="caps_warning_login" 
                            style="color:#e53e3e; display:none; font-size:13px; margin-top:5px; display:block;">
                            ⚠ Caps Lock is ON
                        </small>
                    </div>

                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot password?</a>
                    </div>

                    <button type="submit" class="sign-in-btn">Sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </form>
                <div class="login-secure-note"><span class="login-secure-rule"></span><i class="bi bi-shield-check" aria-hidden="true"></i><span>Secure access for your team.</span><span class="login-secure-rule"></span></div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/capslock_detector.js"></script>
    <script>
        // Wait for DOM and scripts to load
        document.addEventListener('DOMContentLoaded', function() {
            // Show toast notification if login failed
            <?php if ($login_status === 'error'): ?>
                if (typeof toast !== 'undefined' && toast.error) {
                    toast.error('<?php echo addslashes($login_message); ?>');
                } else {
                    console.error('Toast notification system not loaded');
                }
            <?php endif; ?>
        });

        // Toggle password visibility for login
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

        // Enable CapsLock detection for login password
        setTimeout(() => {
            enableCapsLockWarning("password", "caps_warning_login");
        }, 100);

        // Add loading state to form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.sign-in-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Signing in...';
        });
    </script>
    <?php if (isset($_SESSION['logout_status'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const status = "<?php echo $_SESSION['logout_status']; ?>";
        const message = "<?php echo addslashes($_SESSION['logout_message']); ?>";

        if (typeof toast !== 'undefined') {
            if (status === "success") {
                toast.success(message);
            } else {
                toast.error(message);
            }
        }
    });
    </script>
    <?php 
    unset($_SESSION['logout_status']);
    unset($_SESSION['logout_message']);
    endif;
    ?>
</body>
</html>