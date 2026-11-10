<?php
require_once __DIR__ . '/../includes/member_auth.php';

if (empty($_SESSION['member_account_id'])) {
    header('Location: login.php');
    exit();
}

unset($_SESSION['member_account_id'], $_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_csrf_token']);
session_regenerate_id(true);
header('Location: login.php');
exit();
