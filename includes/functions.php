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
    $allowed_columns = ['MemberID', 'FullName', 'PhoneNo', 'JoinDate'];
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
        $sql .= " ORDER BY JoinDate DESC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>