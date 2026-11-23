<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Logout is POST-only with a CSRF token so other sites cannot log staff out.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . (isset($_SESSION['staff_id']) ? 'dashboard.php' : 'index.php'));
    exit();
}

if (isset($_SESSION['staff_id']) && verify_csrf_request()) {
    $_SESSION = [];
    session_destroy();

    session_start();
    session_regenerate_id(true); // the old session ID is dead; issue a fresh one
    $_SESSION['logout_status'] = 'success';
    $_SESSION['logout_message'] = 'You have been logged out successfully.';
} elseif (isset($_SESSION['staff_id'])) {
    $_SESSION['message'] = 'Logout failed: your security token expired. Refresh the page and try again.';
    $_SESSION['message_type'] = 'error';
    header('Location: dashboard.php');
    exit();
} else {
    $_SESSION['logout_status'] = 'error';
    $_SESSION['logout_message'] = 'Logout failed — no active session.';
}

header("Location: index.php");
exit();
