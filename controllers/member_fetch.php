<?php
if ($action === 'edit' && $member_id) {
    $stmt = $pdo->prepare("SELECT * FROM Member WHERE MemberID=?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();

    if (!$member) {
        $_SESSION['message'] = "Member not found.";
        $_SESSION['message_type'] = "error";
        header("Location: members.php");
        exit();
    }

} elseif ($action === 'add') {
    $member = [
        'MemberID'=>null,
        'FirstName'=>'','LastName'=>'','Address'=>'','City'=>'',
        'Province'=>'','Zipcode'=>'','Gender'=>'Male','DateOfBirth'=>'',
        'PhoneNo'=>'','Email'=>'','EmergencyContactName'=>'',
        'EmergencyContactNumber'=>'','MembershipStatus'=>'Pending'
    ];
}
