<?php
// api/get_membership.php
require_once '../config/db.php';
require_once '../includes/functions.php';
check_login();

header('Content-Type: application/json');

$membership_id = $_GET['id'] ?? null;

if (!$membership_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Membership ID is required']);
    exit;
}

try {
    // Fetch membership details
    $stmt = $pdo->prepare("
        SELECT 
            m.MembershipID,
            m.MemberID,
            m.PlanID,
            m.StartDate,
            m.EndDate,
            m.Status,
            CONCAT(mem.FirstName, ' ', mem.LastName) as MemberName,
            p.PlanName,
            p.Rate AS PlanRate
        FROM Membership m
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN Plan p ON m.PlanID = p.PlanID
        WHERE m.MembershipID = ?
        LIMIT 1
    ");
    
    $stmt->execute([$membership_id]);
    $membership = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$membership) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Membership not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'membership' => $membership
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}