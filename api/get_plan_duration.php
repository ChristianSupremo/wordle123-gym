<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
check_login();

header('Content-Type: application/json');

$plan_id = $_GET['id'] ?? null;

if (!$plan_id) {
    echo json_encode(['success' => false, 'message' => 'Plan ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT Duration, PlanType FROM Plan WHERE PlanID = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Plan not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'duration' => $plan['Duration'],
        'plan_type' => $plan['PlanType']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>