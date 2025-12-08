<?php
/**
 * Toggle Staff Status API
 * Path: api/toggle_staff_status.php
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/staff_modal_helpers.php';
check_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $staff_id = $input['staff_id'] ?? null;
    
    if (!$staff_id) {
        echo json_encode(['success' => false, 'message' => 'Staff ID is required']);
        exit();
    }
    
    // Check if staff can be deactivated
    $deactivate_check = canDeactivateStaff($pdo, $staff_id);
    
    if (!$deactivate_check['can_deactivate']) {
        echo json_encode(['success' => false, 'message' => $deactivate_check['reason']]);
        exit();
    }
    
    // Get current status
    $stmt = $pdo->prepare("SELECT Status FROM Staff WHERE StaffID = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch();
    
    if (!$staff) {
        echo json_encode(['success' => false, 'message' => 'Staff member not found']);
        exit();
    }
    
    // Toggle status
    $new_status = $staff['Status'] === 'Active' ? 'Inactive' : 'Active';
    
    $stmt = $pdo->prepare("
        UPDATE Staff 
        SET Status = ?, 
            UpdatedBy = ?, 
            UpdatedAt = NOW() 
        WHERE StaffID = ?
    ");
    $stmt->execute([$new_status, $_SESSION['staff_id'], $staff_id]);
    
    $action = $new_status === 'Active' ? 'activated' : 'deactivated';
    
    echo json_encode([
        'success' => true,
        'message' => "Staff member {$action} successfully",
        'new_status' => $new_status
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>