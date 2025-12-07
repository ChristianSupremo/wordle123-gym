<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['staff_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE Username = ? AND Status = 'Active'");
    $stmt->execute([$username]);
    $staff = $stmt->fetch();

    if ($staff && password_verify($password, $staff['Password'])) {
        // Password is correct, start a new session
        $_SESSION['staff_id'] = $staff['StaffID'];
        $_SESSION['staff_name'] = $staff['FullName'];
        $_SESSION['role_id'] = $staff['RoleID'];

        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Center - Login</title>
    <link rel="stylesheet" href="assets/css/style/style.css">
    <link rel="stylesheet" href="assets/css/style/payment_modals.css">
</head>
<body class="login-page">
    <div class="login-container">
        <!-- Left Section - Gym Image -->
        <div class="left-section"></div>

        <!-- Right Section - Login Form -->
        <div class="right-section">
            <div class="login-form">
                <div class="logo">
                    <img src="assets/css/style/logo.png" class="logo-icon">
                </div>
                
                <h1>Log in to your account</h1>
                <p class="subtitle">Welcome back! Please enter your details.</p>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form action="index.php" method="post">
                    <div class="form-group">
                        <label for="username">Email</label>
                        <input type="text" id="username" name="username" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot password</a>
                    </div>

                    <button type="submit" class="sign-in-btn">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>