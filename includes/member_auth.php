<?php
// Member-side authentication helpers. Staff authentication remains in functions.php.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_member_login(): void
{
    if (empty($_SESSION['member_account_id']) || empty($_SESSION['member_id'])) {
        header('Location: login.php');
        exit();
    }
}

function member_csrf_token(): string
{
    if (empty($_SESSION['member_csrf_token'])) {
        $_SESSION['member_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['member_csrf_token'];
}

function verify_member_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['member_csrf_token'])
        && hash_equals($_SESSION['member_csrf_token'], $token);
}
