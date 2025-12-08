<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["error" => "No ID provided"]);
    exit;
}

// 1. Fetch member info
$stmt = $pdo->prepare("
    SELECT 
        m.*,
        CONCAT(s.FirstName, ' ', s.LastName) AS StaffName
    FROM Member m
    LEFT JOIN Staff s ON m.CreatedBy = s.StaffID
    WHERE m.MemberID = ?
");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// If member not found
if (!$member) {
    echo json_encode(["error" => "Member not found"]);
    exit;
}

// 2. Fetch all memberships of this member
$stmt = $pdo->prepare("
    SELECT 
        m.MembershipID,
        p.PlanName,
        m.StartDate,
        m.EndDate,
        m.Status
    FROM Membership m
    LEFT JOIN Plan p ON m.PlanID = p.PlanID
    WHERE m.MemberID = ?
    ORDER BY m.StartDate DESC
");
$stmt->execute([$id]);
$memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch all payments of this member
$stmt = $pdo->prepare("
    SELECT 
        py.PaymentID,
        py.PaymentDate,
        py.AmountPaid,
        py.ReferenceNumber,
        py.PaymentStatus,
        pm.MethodName AS PaymentMethod
    FROM Payment py
    JOIN Membership m ON py.MembershipID = m.MembershipID
    JOIN PaymentMethods pm ON py.PaymentMethodID = pm.PaymentMethodID
    WHERE m.MemberID = ?
    ORDER BY py.PaymentDate DESC
");
$stmt->execute([$id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return all data in JSON for modal
echo json_encode([
    "member" => $member,
    "memberships" => $memberships,
    "payments" => $payments
]);
