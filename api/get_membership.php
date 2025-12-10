<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "No ID provided"]);
    exit;
}

// Fetch membership info with related data
$stmt = $pdo->prepare("
    SELECT 
        m.MembershipID,
        m.MemberID,
        m.PlanID,
        m.StartDate,
        m.EndDate,
        m.Status,
        CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
        p.PlanName,
        p.Rate AS PlanRate
    FROM Membership m
    JOIN Member mem ON m.MemberID = mem.MemberID
    LEFT JOIN Plan p ON m.PlanID = p.PlanID
    WHERE m.MembershipID = ?
");
$stmt->execute([$id]);
$membership = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$membership) {
    echo json_encode(["success" => false, "message" => "Membership not found"]);
    exit;
}

echo json_encode([
    "success" => true,
    "membership" => $membership
]);