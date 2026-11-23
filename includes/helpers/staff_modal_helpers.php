<?php
/**
 * Staff Modal Helper Functions
 * Path: includes/helpers/staff_modal_helpers.php
 */

/**
 * Get all staff members with role information
 */
function getAllStaff($pdo, $status_filter = 'All', $search = '') {
    $sql = "
        SELECT 
            s.StaffID,
            s.FirstName,
            s.LastName,
            CONCAT(s.FirstName, ' ', s.LastName) AS FullName,
            s.Email,
            s.Phone,
            s.Photo,
            s.Username,
            s.HireDate,
            s.Status,
            s.LastLogin,
            s.CreatedAt,
            r.RoleID,
            r.RoleName,
            r.Description AS RoleDescription,
            r.AccessLevel,
            creator.FirstName AS CreatedByFirstName,
            creator.LastName AS CreatedByLastName
        FROM Staff s
        LEFT JOIN Roles r ON s.RoleID = r.RoleID
        LEFT JOIN Staff creator ON s.CreatedBy = creator.StaffID
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status_filter !== 'All') {
        $sql .= " AND s.Status = ?";
        $params[] = $status_filter;
    }
    
    if ($search !== '') {
        $sql .= " AND (s.FirstName LIKE ? OR s.LastName LIKE ? OR s.Email LIKE ? OR s.Username LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    
    $sql .= " ORDER BY s.Status DESC, s.FirstName, s.LastName";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get staff by ID with full details
 */
function getStaffById($pdo, $staff_id) {
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            CONCAT(s.FirstName, ' ', s.LastName) AS FullName,
            r.RoleName,
            r.Description AS RoleDescription,
            r.AccessLevel,
            creator.FirstName AS CreatedByFirstName,
            creator.LastName AS CreatedByLastName,
            updater.FirstName AS UpdatedByFirstName,
            updater.LastName AS UpdatedByLastName
        FROM Staff s
        LEFT JOIN Roles r ON s.RoleID = r.RoleID
        LEFT JOIN Staff creator ON s.CreatedBy = creator.StaffID
        LEFT JOIN Staff updater ON s.UpdatedBy = updater.StaffID
        WHERE s.StaffID = ?
    ");
    $stmt->execute([$staff_id]);
    return $stmt->fetch();
}

/**
 * Get all roles
 */
function getAllRoles($pdo) {
    $stmt = $pdo->query("
        SELECT 
            RoleID,
            RoleName,
            Description,
            AccessLevel,
            IsDefault
        FROM Roles
        ORDER BY AccessLevel DESC, RoleName
    ");
    return $stmt->fetchAll();
}

/**
 * Get staff statistics
 */
function getStaffStats($pdo, $staff_id) {
    // Total members registered by this staff
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM Member
        WHERE CreatedBy = ?
    ");
    $stmt->execute([$staff_id]);
    $members_registered = $stmt->fetch()['total'];
    
    // Total payments processed by this staff
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total, SUM(AmountPaid) as total_amount
        FROM Payment
        WHERE StaffID = ?
    ");
    $stmt->execute([$staff_id]);
    $payment_data = $stmt->fetch();
    $payments_processed = $payment_data['total'];
    $total_amount = $payment_data['total_amount'] ?? 0;
    
    // Total memberships created by this staff (FIXED)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM Membership
        WHERE StaffID = ?
    ");
    $stmt->execute([$staff_id]);
    $memberships_created = $stmt->fetch()['total'];
    
    return [
        'members_registered' => $members_registered,
        'payments_processed' => $payments_processed,
        'total_amount' => $total_amount,
        'memberships_created' => $memberships_created
    ];
}

/**
 * "Can ..." / "Cannot ..." permission lines for a role (from permission_helper.php)
 */
function getRolePermissions($role_name) {
    return describe_role_permissions($role_name);
}

/**
 * True if changing this staff member to $new_role_id / $new_status would leave
 * no active Admin. Pass the current values for anything that is not changing.
 */
function wouldRemoveLastAdmin($pdo, $staff_id, $new_role_id, $new_status) {
    $stmt = $pdo->prepare("
        SELECT r.RoleName, s.Status
        FROM Staff s
        LEFT JOIN Roles r ON s.RoleID = r.RoleID
        WHERE s.StaffID = ?
    ");
    $stmt->execute([$staff_id]);
    $target = $stmt->fetch();
    if (!$target || $target['RoleName'] !== ADMIN_ROLE_NAME || $target['Status'] !== 'Active') {
        return false; // not currently an active admin
    }

    $stmt = $pdo->prepare("SELECT RoleName FROM Roles WHERE RoleID = ?");
    $stmt->execute([$new_role_id]);
    if ($stmt->fetchColumn() === ADMIN_ROLE_NAME && $new_status === 'Active') {
        return false; // stays an active admin
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM Staff s
        JOIN Roles r ON s.RoleID = r.RoleID
        WHERE r.RoleName = ? AND s.Status = 'Active'
    ");
    $stmt->execute([ADMIN_ROLE_NAME]);
    return (int) $stmt->fetchColumn() <= 1;
}

/**
 * Check if staff can be deactivated
 */
function canDeactivateStaff($pdo, $staff_id) {
    // Cannot deactivate yourself
    if ($staff_id == $_SESSION['staff_id']) {
        return ['can_deactivate' => false, 'reason' => 'You cannot deactivate your own account'];
    }

    $stmt = $pdo->prepare("SELECT RoleID FROM Staff WHERE StaffID = ?");
    $stmt->execute([$staff_id]);
    $role_id = $stmt->fetchColumn();
    if ($role_id !== false && wouldRemoveLastAdmin($pdo, $staff_id, $role_id, 'Inactive')) {
        return ['can_deactivate' => false, 'reason' => 'Cannot deactivate the last active admin'];
    }

    return ['can_deactivate' => true, 'reason' => ''];
}

/**
 * Format last login display
 */
function formatLastLogin($last_login) {
    if (!$last_login) {
        return 'Never';
    }
    
    $date = new DateTime($last_login);
    $now = new DateTime();
    $diff = $now->diff($date);
    
    if ($diff->days == 0) {
        return 'Today at ' . $date->format('g:i A');
    } elseif ($diff->days == 1) {
        return 'Yesterday at ' . $date->format('g:i A');
    } elseif ($diff->days < 7) {
        return $diff->days . ' days ago';
    } else {
        return $date->format('M j, Y');
    }
}

/**
 * Get role badge class
 */
function getRoleBadgeClass($role_name) {
    $classes = [
        'Admin' => 'role-admin',
        'Manager' => 'role-manager',
        'Receptionist' => 'role-receptionist'
    ];
    return $classes[$role_name] ?? 'role-default';
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}
?>