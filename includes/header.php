<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New You Fitness - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style/style.css">
    <link rel="stylesheet" href="assets/css/style/filter_styles.css">
    <link rel="stylesheet" href="assets/css/style/table_sort.css">
</head>
<body class="dashboard-page">
    <?php if (isset($_SESSION['staff_id'])): ?>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <img src="assets/css/style/logo.png" class="logo-icon">
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <div class="user-profile">
                <div class="user-avatar">
                    <?php 
                    $initials = '';
                    $name_parts = explode(' ', $_SESSION['staff_name']);
                    foreach ($name_parts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    echo substr($initials, 0, 2);
                    ?>
                </div>
                <div class="user-info">
                    <div class="user-name">Admin - <?php echo htmlspecialchars(explode(' ', $_SESSION['staff_name'])[0]); ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>

            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="dashboard.php" data-tooltip="Dashboard" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="members.php" data-tooltip="Members" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i>
                        <span>Members</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="memberships.php" data-tooltip="Memberships"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'memberships.php' ? 'active' : ''; ?>">
                        <i class="bi bi-card-checklist"></i>
                        <span>Memberships</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="payments.php" data-tooltip="Payments"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                        <i class="bi bi-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="plans.php" data-tooltip="Plans" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'plans.php' ? 'active' : ''; ?>">
                        <i class="bi bi-tag"></i>
                        <span>Manage Plans</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="staff.php" data-tooltip="Staff"
                    class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : ''; ?>">
                        <i class="bi bi-person-badge"></i>
                        <span>Staff</span>
                    </a>
                </li>
            </ul>

            <div class="dark-mode-toggle">
                <i class="bi bi-moon"></i>
                <span>Dark Mode</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                </label>
            </div>

            <a href="#" onclick="logoutNow()" class="logout-btn" data-tooltip="Log Out">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </a>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php echo flash_message(); ?>
    <?php else: ?>
        <div class="container mt-4">
            <?php echo flash_message(); ?>
    <?php endif; ?>