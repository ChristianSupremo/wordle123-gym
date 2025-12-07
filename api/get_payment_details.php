<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
check_login();

header('Content-Type: application/json');

$payment_id = $_GET['id'] ?? null;

if (!$payment_id) {
    echo json_encode(['success' => false, 'message' => 'Payment ID is required']);
    exit;
}

try {
    $sql = "
        SELECT 
            p.PaymentID,
            p.MembershipID,
            p.PaymentDate,
            p.AmountPaid,
            pm.PaymentMethodID,
            pm.MethodName AS PaymentMethod,
            p.ReferenceNumber,
            p.PaymentStatus,
            p.Remarks,
            CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
            pl.PlanName,
            s.FullName AS StaffName
        FROM Payment p
        JOIN Membership m ON p.MembershipID = m.MembershipID
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN Plan pl ON m.PlanID = pl.PlanID
        JOIN PaymentMethods pm ON p.PaymentMethodID = pm.PaymentMethodID
        LEFT JOIN Staff s ON p.StaffID = s.StaffID
        WHERE p.PaymentID = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'payment' => $payment
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>