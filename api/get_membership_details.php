<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
check_login();

header('Content-Type: application/json');

$membership_id = $_GET['id'] ?? null;

if (!$membership_id) {
    echo json_encode(['success' => false, 'message' => 'Membership ID is required']);
    exit;
}

try {
    // Fetch membership details with member and plan information
    $sql = "
        SELECT 
            m.MembershipID,
            m.MemberID,
            m.PlanID,
            m.StartDate,
            m.EndDate,
            m.Status,
            m.StaffID,
            DATEDIFF(m.EndDate, CURDATE()) AS DaysLeft,
            CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
            mem.FirstName,
            mem.LastName,
            mem.Email,
            mem.PhoneNo,
            mem.Gender,
            mem.DateOfBirth,
            mem.Address,
            mem.City,
            mem.Province,
            mem.Zipcode,
            mem.EmergencyContactName,
            mem.EmergencyContactNumber,
            mem.Photo,
            mem.MembershipStatus,
            p.PlanName,
            p.Rate AS PlanRate,
            p.Duration,
            p.PlanType,
            s.FullName AS StaffName
        FROM Membership m
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN Plan p ON m.PlanID = p.PlanID
        LEFT JOIN Staff s ON m.StaffID = s.StaffID
        WHERE m.MembershipID = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$membership_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Membership not found']);
        exit;
    }

    // Format duration
    $duration = $result['Duration'];
    $planType = strtolower($result['PlanType']);
    
    if ($planType === 'days') {
        $durationText = $duration . ' day' . ($duration != 1 ? 's' : '');
    } elseif ($planType === 'months') {
        $durationText = $duration . ' month' . ($duration != 1 ? 's' : '');
    } elseif ($planType === 'years') {
        $durationText = $duration . ' year' . ($duration != 1 ? 's' : '');
    } else {
        $durationText = $duration . ' ' . $planType;
    }

    // Separate membership and member data
    $membership = [
        'MembershipID' => $result['MembershipID'],
        'MemberID' => $result['MemberID'],
        'PlanID' => $result['PlanID'],
        'StartDate' => $result['StartDate'],
        'EndDate' => $result['EndDate'],
        'Status' => $result['Status'],
        'DaysLeft' => $result['DaysLeft'],
        'PlanName' => $result['PlanName'],
        'PlanRate' => $result['PlanRate'],
        'Duration' => $durationText,
        'StaffName' => $result['StaffName']
    ];

    $member = [
        'MemberID' => $result['MemberID'],
        'FirstName' => $result['FirstName'],
        'LastName' => $result['LastName'],
        'Email' => $result['Email'],
        'PhoneNo' => $result['PhoneNo'],
        'Gender' => $result['Gender'],
        'DateOfBirth' => $result['DateOfBirth'],
        'Address' => $result['Address'],
        'City' => $result['City'],
        'Province' => $result['Province'],
        'Zipcode' => $result['Zipcode'],
        'EmergencyContactName' => $result['EmergencyContactName'],
        'EmergencyContactNumber' => $result['EmergencyContactNumber'],
        'Photo' => $result['Photo'],
        'MembershipStatus' => $result['MembershipStatus']
    ];

    // Fetch payment history for this membership
    $paymentSql = "
        SELECT 
            p.PaymentID,
            p.PaymentDate,
            p.AmountPaid,
            pm.MethodName AS PaymentMethod,
            p.ReferenceNumber,
            p.PaymentStatus
        FROM Payment p
        LEFT JOIN paymentmethods pm 
            ON p.PaymentMethodID = pm.PaymentMethodID
        WHERE p.MembershipID = ?
        ORDER BY p.PaymentDate DESC
    ";

    $paymentStmt = $pdo->prepare($paymentSql);
    $paymentStmt->execute([$membership_id]);
    $payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'membership' => $membership,
        'member' => $member,
        'payments' => $payments
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching membership details: ' . $e->getMessage()
    ]);
}