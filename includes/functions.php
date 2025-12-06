<?php
// includes/functions.php

// Start the session on every page that includes this file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if the user is logged in. If not, redirects to the login page.
 */
function check_login() {
    if (!isset($_SESSION['staff_id'])) {
        header('Location: index.php');
        exit();
    }
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

function get_members($pdo, $status_filter = 'All', $search = '') {
    $sql = "
        SELECT 
            MemberID,
            FirstName,
            LastName,
            PhoneNo,
            JoinDate,
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
            )
        ";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like; // allows searching by phone number
    }

    $sql .= " ORDER BY LastName, FirstName";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>