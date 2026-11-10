<?php
require_once __DIR__ . '/../includes/member_auth.php';

if (empty($_SESSION['member_account_id'])) {
    header('Location: login.php');
    exit();
}

member_clear_session();
session_regenerate_id(true);
header('Location: login.php');
exit();
