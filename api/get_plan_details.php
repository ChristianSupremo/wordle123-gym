<?php
/**
 * Get Plan Details API
 * Path: api/get_plan_details.php
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/plan_modal_helpers.php';

header('Content-Type: application/json');

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$plan_id = $_GET['id'] ?? null;

if (!$plan_id) {
    echo json_encode(['success' => false, 'message' => 'Plan ID is required']);
    exit();
}

try {
    // Get plan details
    $plan = getPlanById($pdo, $plan_id);
    
    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Plan not found']);
        exit();
    }
    
    // Get plan statistics
    $stats = getPlanStats($pdo, $plan_id);
    
    echo json_encode([
        'success' => true,
        'plan' => $plan,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>