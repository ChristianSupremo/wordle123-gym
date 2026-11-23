<?php
// includes/functions.php

// Staff sessions end after this many seconds without a request.
const SESSION_IDLE_TIMEOUT = 7200;

// Start the session on every page that includes this file
if (session_status() == PHP_SESSION_NONE) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? null) == 443;
    ini_set('session.use_strict_mode', '1');                    // ignore session IDs the server never issued
    ini_set('session.gc_maxlifetime', (string) SESSION_IDLE_TIMEOUT); // keep session files as long as the idle timeout
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $is_https]); // Secure only on HTTPS so local http:// XAMPP keeps working
    session_start();
}

require_once __DIR__ . '/helpers/permission_helper.php';
require_once __DIR__ . '/helpers/csrf_helper.php';

/**
 * Authentication: requires a logged-in staff member whose account still exists
 * and is Active and whose session has not been idle past SESSION_IDLE_TIMEOUT.
 * Re-reads role/status from the DB on every request so deactivation and role
 * changes apply to already-open sessions. Also rejects state-changing requests
 * without a valid CSRF token (csrf_helper.php).
 * Authorization (what they may do) is require_permission() in permission_helper.php.
 */
function check_login() {
    $staff = isset($_SESSION['staff_id']) ? current_staff() : null;
    $idle_expired = isset($_SESSION['last_activity']) && time() - (int) $_SESSION['last_activity'] > SESSION_IDLE_TIMEOUT;

    if ($staff && $staff['Status'] === 'Active' && !$idle_expired) {
        $_SESSION['last_activity'] = time();
        $_SESSION['role_id'] = $staff['RoleID'];
        $_SESSION['staff_name'] = $staff['FirstName'] . ' ' . $staff['LastName'];
        require_csrf();
        return;
    }

    if (isset($_SESSION['staff_id'])) {
        // Idle too long, or account deactivated/deleted since login: end the session.
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['logout_status'] = 'error';
        $_SESSION['logout_message'] = $idle_expired && $staff && $staff['Status'] === 'Active'
            ? 'Your session expired due to inactivity. Please log in again.'
            : 'Your account is no longer active. Please contact an administrator.';
    }

    if (wants_json_response()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
        exit();
    }
    header('Location: index.php');
    exit();
}

/**
 * Renders a simple success or error message.
 */
function flash_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info'; // 'success', 'error', 'info'
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return "<div class='alert alert-{$type}'>{$message}</div>";
    }
    return '';
}


/**
 * Updated includes/functions.php
 * Added sorting support to get_members() function
 */

function get_members($pdo, $status_filter = 'All', $search = '', $sort_params = []) {
    // Base query with FullName for sorting
    $sql = "
        SELECT 
            MemberID,
            FirstName,
            LastName,
            CONCAT(FirstName, ' ', LastName) as FullName,
            PhoneNo,
            JoinDate,
            createdAt,
            MembershipStatus
        FROM member
        WHERE 1=1
    ";

    $params = [];

    // Filter by status
    if ($status_filter !== 'All') {
        $sql .= " AND MembershipStatus = ?";
        $params[] = $status_filter;
    }

    // Search filter
    if ($search !== '') {
        $sql .= " 
            AND (
                FirstName LIKE ? 
                OR LastName LIKE ? 
                OR CONCAT(FirstName, ' ', LastName) LIKE ?
                OR PhoneNo LIKE ?
                OR MemberID LIKE ?
            )
        ";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Apply sorting
    $allowed_columns = ['MemberID', 'FullName', 'PhoneNo', 'JoinDate', 'createdAt'];
    $sort_column = $sort_params['column'] ?? null;
    $sort_order = $sort_params['order'] ?? 'ASC';
    
    // Validate and apply sorting
    if (!empty($sort_column) && in_array($sort_column, $allowed_columns)) {
        // Ensure sort order is valid
        $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
        
        // Map FullName to the concatenated column for sorting
        if ($sort_column === 'FullName') {
            $sql .= " ORDER BY CONCAT(FirstName, ' ', LastName) $sort_order";
        } else {
            $sql .= " ORDER BY $sort_column $sort_order";
        }
    } else {
        // Default sorting (when no sort is applied or after third click)
        $sql .= " ORDER BY createdAt DESC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatMemberDate($date_string, $format = 'full') {
    if (!$date_string) return 'N/A';
    
    $date = new DateTime($date_string);
    
    if ($format === 'full') {
        return $date->format('M d, Y g:i A');
    } elseif ($format === 'short') {
        return $date->format('m/d/y');
    } elseif ($format === 'time') {
        return $date->format('g:i A');
    }
    
    return $date->format('Y-m-d H:i:s');
}
?>