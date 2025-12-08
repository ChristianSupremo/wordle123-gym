<?php
session_start();

if (isset($_SESSION['staff_id'])) {
    session_unset();
    session_destroy();

    session_start();
    $_SESSION['logout_status'] = 'success';
    $_SESSION['logout_message'] = 'You have been logged out successfully.';
} else {
    session_start();
    $_SESSION['logout_status'] = 'error';
    $_SESSION['logout_message'] = 'Logout failed — no active session.';
}

header("Location: index.php");
exit();
