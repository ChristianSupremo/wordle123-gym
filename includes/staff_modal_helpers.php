<?php
/**
 * Staff Modal Helper Functions
 * Path: includes/staff_modal_helpers.php
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
 * Get role permissions description
 */
function getRolePermissions($access_level) {
    $permissions = [
        3 => [ // Admin
            'Can manage all staff',
            'Can manage roles and permissions',
            'Can manage all members',
            'Can manage all memberships',
            'Can manage all payments',
            'Can view all reports',
            'Can manage system settings'
        ],
        2 => [ // Manager
            'Can manage members',
            'Can manage memberships',
            'Can manage payments',
            'Can view reports',
            'Cannot manage staff or roles'
        ],
        1 => [ // Receptionist
            'Can register members',
            'Can create memberships',
            'Can record payments',
            'Cannot manage staff',
            'Cannot access system settings'
        ]
    ];
    
    return $permissions[$access_level] ?? [];
}

/**
 * Check if staff can be deactivated
 */
function canDeactivateStaff($pdo, $staff_id) {
    // Cannot deactivate yourself
    if ($staff_id == $_SESSION['staff_id']) {
        return ['can_deactivate' => false, 'reason' => 'You cannot deactivate your own account'];
    }
    
    // Check if this is the last active admin
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as admin_count
        FROM Staff s
        JOIN Roles r ON s.RoleID = r.RoleID
        WHERE r.AccessLevel = 3 AND s.Status = 'Active'
    ");
    $stmt->execute();
    $admin_count = $stmt->fetch()['admin_count'];
    
    if ($admin_count <= 1) {
        // Check if this staff is an admin
        $stmt = $pdo->prepare("
            SELECT r.AccessLevel
            FROM Staff s
            JOIN Roles r ON s.RoleID = r.RoleID
            WHERE s.StaffID = ?
        ");
        $stmt->execute([$staff_id]);
        $staff = $stmt->fetch();
        
        if ($staff && $staff['AccessLevel'] == 3) {
            return ['can_deactivate' => false, 'reason' => 'Cannot deactivate the last active admin'];
        }
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
function getRoleBadgeClass($access_level) {
    $classes = [
        3 => 'role-admin',
        2 => 'role-manager',
        1 => 'role-receptionist'
    ];
    return $classes[$access_level] ?? 'role-default';
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