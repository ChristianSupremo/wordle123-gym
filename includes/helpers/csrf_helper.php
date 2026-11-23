<?php
/**
 * CSRF protection for staff pages. One random token per session.
 * Path: includes/helpers/csrf_helper.php (loaded by includes/functions.php)
 *
 * - check_login() calls verify_csrf_request() for every non-GET/HEAD/OPTIONS
 *   request, so every endpoint using require_permission() is covered.
 * - includes/header.php puts the token in <meta name="csrf-token"> and adds it to
 *   same-origin fetch() calls (X-CSRF-Token header) and POST forms (csrf_token field).
 * - index.php (login) and logout.php validate explicitly.
 */

function csrf_token() {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input for server-rendered forms. */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/** Issue a new token (login, logout). */
function csrf_rotate() {
    unset($_SESSION['csrf_token']);
    return csrf_token();
}

function is_state_changing_request() {
    return !in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'OPTIONS'], true);
}

/** True if the request carries this session's token in the X-CSRF-Token header or csrf_token POST field. */
function verify_csrf_request() {
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    return is_string($sent) && $sent !== ''
        && !empty($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/** Rejects state-changing requests without a valid token (403, JSON or page). */
function require_csrf() {
    if (is_state_changing_request() && !verify_csrf_request()) {
        deny_access('Your security token is missing or expired. Refresh the page and try again.');
    }
}
