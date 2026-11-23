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
    <link rel="stylesheet" href="assets/css/style/admin_refinements.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <script>
    // CSRF (see includes/helpers/csrf_helper.php): attach this session's token to every
    // same-origin non-GET fetch() and every POST form, so pages and modals need no changes.
    (function () {
        var token = document.querySelector('meta[name="csrf-token"]').content;
        var nativeFetch = window.fetch.bind(window);
        window.fetch = function (input, init) {
            init = init || {};
            var isRequest = typeof Request !== 'undefined' && input instanceof Request;
            var method = String(init.method || (isRequest ? input.method : 'GET')).toUpperCase();
            var url = new URL(isRequest ? input.url : String(input), window.location.href);
            if (method !== 'GET' && method !== 'HEAD' && url.origin === window.location.origin) {
                var headers = new Headers(init.headers || (isRequest ? input.headers : undefined));
                headers.set('X-CSRF-Token', token);
                headers.set('X-Requested-With', 'XMLHttpRequest');
                init = Object.assign({}, init, { headers: headers });
            }
            return nativeFetch(input, init);
        };
        function addToken(form) {
            if (form && form.tagName === 'FORM' && String(form.getAttribute('method')).toLowerCase() === 'post'
                && !form.querySelector('input[name="csrf_token"]')) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'csrf_token';
                input.value = token;
                form.appendChild(input);
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form').forEach(addToken);
        });
        document.addEventListener('submit', function (e) { addToken(e.target); }, true);
    })();
    </script>
</head>
<body class="dashboard-page">
    <?php if (isset($_SESSION['staff_id'])): ?>
    <div class="dashboard-wrapper">
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
                    <div class="user-name"><?php echo htmlspecialchars(explode(' ', $_SESSION['staff_name'])[0]); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars(get_current_user_role() ?? 'No role'); ?></div>
                </div>
            </div>

            <?php
            // [file, permission, tooltip, icon, label] - links the current role cannot use are hidden
            $nav_items = [
                ['dashboard.php', 'dashboard.view', 'Dashboard', 'house-door', 'Dashboard'],
                ['members.php', 'members.view', 'Members', 'people', 'Members'],
                ['memberships.php', 'memberships.view', 'Memberships', 'card-checklist', 'Memberships'],
                ['payments.php', 'payments.view', 'Payments', 'credit-card', 'Payments'],
                ['gcash_submissions.php', 'payments.view', 'GCash Review', 'qr-code', 'GCash Review'],
                ['plans.php', 'plans.view', 'Plans', 'tag', has_permission('plans.manage') ? 'Manage Plans' : 'Plans'],
                ['attendance.php', 'attendance.manage', 'Attendance', 'qr-code-scan', 'Attendance'],
                ['insights.php', 'insights.view', 'Insights', 'graph-up', 'Member Insights'],
                ['staff.php', 'staff.view', 'Staff', 'person-badge', 'Staff'],
            ];
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            <ul class="sidebar-nav">
                <?php foreach ($nav_items as [$file, $permission, $tooltip, $icon, $label]): ?>
                    <?php if (has_permission($permission)): ?>
                <li class="nav-item">
                    <a href="<?php echo $file; ?>" data-tooltip="<?php echo $tooltip; ?>" class="nav-link <?php echo $current_page == $file ? 'active' : ''; ?>">
                        <i class="bi bi-<?php echo $icon; ?>"></i><span><?php echo $label; ?></span>
                    </a>
                </li>
                    <?php endif; ?>
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
            <?php echo flash_message(); ?>
    <?php else: ?>
        <div class="container mt-4">
            <?php echo flash_message(); ?>
    <?php endif; ?>
