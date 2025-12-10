<?php
/**
 * Get Staff Details API
 * Path: api/get_staff_details.php
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/helpers/staff_modal_helpers.php';

header('Content-Type: application/json');

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$staff_id = $_GET['id'] ?? null;

if (!$staff_id) {
    echo json_encode(['success' => false, 'message' => 'Staff ID is required']);
    exit();
}

try {
    // Get staff details
    $staff = getStaffById($pdo, $staff_id);
    
    if (!$staff) {
        echo json_encode(['success' => false, 'message' => 'Staff member not found']);
        exit();
    }
    
    // Get staff statistics
    $stats = getStaffStats($pdo, $staff_id);
    
    // Get role permissions
    $permissions = getRolePermissions($staff['AccessLevel']);
    
    // Check if can be deactivated
    $deactivate_check = canDeactivateStaff($pdo, $staff_id);
    
    echo json_encode([
        'success' => true,
        'staff' => $staff,
        'stats' => $stats,
        'permissions' => $permissions,
        'can_deactivate' => $deactivate_check['can_deactivate'],
        'deactivate_reason' => $deactivate_check['reason']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>