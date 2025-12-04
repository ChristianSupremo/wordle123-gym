<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

$member_id = $_POST['member_id'] ?? null;
$firstName = trim($_POST['FirstName']);
$lastName  = trim($_POST['LastName']);
$email     = trim($_POST['Email']);

if (!$firstName || !$lastName || !$email) {
    $_SESSION['message'] = "Please fill in all required fields.";
    $_SESSION['message_type'] = "error";
    header("Location: members.php?action=".($member_id?'edit&id='.$member_id:'add'));
    exit();
}

if ($member_id) {
    // UPDATE
    $sql = "UPDATE Member SET 
                FirstName=?, LastName=?, Address=?, City=?, Province=?, Zipcode=?, 
                Gender=?, DateOfBirth=?, PhoneNo=?, Email=?, 
                EmergencyContactName=?, EmergencyContactNumber=?, MembershipStatus=? 
            WHERE MemberID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $firstName, $lastName,
        $_POST['Address'], $_POST['City'], $_POST['Province'], $_POST['Zipcode'],
        $_POST['Gender'], $_POST['DateOfBirth'], $_POST['PhoneNo'], $email,
        $_POST['EmergencyContactName'], $_POST['EmergencyContactNumber'],
        $_POST['MembershipStatus'], $member_id
    ]);
    $_SESSION['message'] = "Member updated successfully!";

} else {
    // CREATE
    $sql = "INSERT INTO Member (
                FirstName, LastName, Address, City, Province, Zipcode, Gender,
                DateOfBirth, PhoneNo, Email, JoinDate,
                EmergencyContactName, EmergencyContactNumber, MembershipStatus
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, 'Pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $firstName, $lastName,
        $_POST['Address'], $_POST['City'], $_POST['Province'], $_POST['Zipcode'],
        $_POST['Gender'], $_POST['DateOfBirth'], $_POST['PhoneNo'], $email,
        $_POST['EmergencyContactName'], $_POST['EmergencyContactNumber']
    ]);
    $_SESSION['message'] = "New member added successfully!";
}

$_SESSION['message_type'] = "success";
header("Location: members.php");
exit();
