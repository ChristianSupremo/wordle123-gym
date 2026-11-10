<?php
// Member portal layout. Mirrors includes/header.php (admin) so both portals look the same.
// Expects the including page to have already called check_member_login($pdo). Optional: $page_title.
require_once __DIR__ . '/../../includes/member_auth.php';
// Pages call check_member_login($pdo) before including this file.

$member_name = $_SESSION['member_name'] ?? 'Member';
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Member Portal';

$member_nav = [
    ['dashboard.php',  'bi-house-door',   'Dashboard',     'Dashboard'],
    ['membership.php', 'bi-card-checklist', 'My Membership', 'My Membership'],
    ['payments.php',   'bi-credit-card',  'Payments',      'Payments'],
    ['attendance.php', 'bi-calendar2-check', 'Attendance', 'Attendance'],
    ['qr.php',         'bi-qr-code',      'My QR',         'My QR Code'],
    ['profile.php',    'bi-person',       'Profile',       'My Profile'],
];

$initials = '';
foreach (explode(' ', trim($member_name)) as $part) {
    $initials .= strtoupper(substr($part, 0, 1));
}
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>New You Fitness - <?= member_h($page_title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style/style.css">
    <link rel="stylesheet" href="../assets/css/style/member_portal.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <img src="../assets/css/style/logo.png" class="logo-icon" alt="New You Fitness">
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <div class="user-profile">
                <div class="user-avatar"><?= member_h($initials) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= member_h(explode(' ', trim($member_name))[0]) ?></div>
                    <div class="user-role">Member</div>
                </div>
            </div>

            <ul class="sidebar-nav">
                <?php foreach ($member_nav as [$href, $icon, $tooltip, $label]): ?>
                <li class="nav-item">
                    <a href="<?= $href ?>" data-tooltip="<?= member_h($tooltip) ?>" class="nav-link <?= $current_page === $href ? 'active' : '' ?>">
                        <i class="bi <?= $icon ?>"></i><span><?= member_h($label) ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
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
                <i class="bi bi-box-arrow-right"></i><span>Log Out</span>
            </a>
        </aside>

        <main class="main-content">
            <?php
            if (isset($_SESSION['member_flash'])) {
                $flash = $_SESSION['member_flash'];
                unset($_SESSION['member_flash']);
                echo '<div class="alert alert-' . member_h($flash['type']) . '">' . member_h($flash['message']) . '</div>';
            }
            ?>
