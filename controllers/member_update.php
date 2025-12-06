<?php
// controllers/member_update.php
require_once '../config/db.php';
require_once '../includes/functions.php';
check_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $member_id = $_POST['member_id'] ?? null;
    
    if (!$member_id) {
        echo json_encode(['success' => false, 'message' => 'Member ID is required']);
        exit;
    }

    // Collect form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_no = trim($_POST['phone_no'] ?? '');
    $gender = $_POST['gender'] ?? null;
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $emergency_contact_name = trim($_POST['emergency_contact_name'] ?? '');
    $emergency_contact_number = trim($_POST['emergency_contact_number'] ?? '');
    $membership_status = $_POST['membership_status'] ?? 'Active';

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_no)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
        exit;
    }

    // Handle photo upload
    $photo_filename = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/members/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit;
        }

        // Check file size (max 2MB)
        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 2MB allowed.']);
            exit;
        }

        // Generate unique filename
        $photo_filename = 'member_' . $member_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $photo_filename;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload photo']);
            exit;
        }

        // Delete old photo if exists
        $stmt = $pdo->prepare("SELECT Photo FROM Member WHERE MemberID = ?");
        $stmt->execute([$member_id]);
        $old_photo = $stmt->fetchColumn();
        
        if ($old_photo && file_exists($upload_dir . $old_photo)) {
            unlink($upload_dir . $old_photo);
        }
    }

    // Update member in database
    $sql = "UPDATE Member SET 
            FirstName = ?,
            LastName = ?,
            Email = ?,
            PhoneNo = ?,
            Gender = ?,
            DateOfBirth = ?,
            Address = ?,
            City = ?,
            Province = ?,
            Zipcode = ?,
            EmergencyContactName = ?,
            EmergencyContactNumber = ?,
            MembershipStatus = ?";
    
    $params = [
        $first_name,
        $last_name,
        $email,
        $phone_no,
        $gender,
        $date_of_birth,
        $address,
        $city,
        $province,
        $zipcode,
        $emergency_contact_name,
        $emergency_contact_number,
        $membership_status
    ];

    // Add photo to update if uploaded
    if ($photo_filename) {
        $sql .= ", Photo = ?";
        $params[] = $photo_filename;
    }

    $sql .= " WHERE MemberID = ?";
    $params[] = $member_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Member updated successfully',
        'member_id' => $member_id
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}