<?php
/**
 * Staff Update Controller
 * Path: controllers/staff_update.php
 */

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
    // Collect form data
    $staff_id = $_POST['staff_id'] ?? null;
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? null;
    $hire_date = $_POST['hire_date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Active';

    // Validate required fields
    if (empty($staff_id) || empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($role_id)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Validate password length if provided
    if (!empty($password) && strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }

    // Check if staff exists
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE StaffID = ?");
    $stmt->execute([$staff_id]);
    $existing_staff = $stmt->fetch();

    if (!$existing_staff) {
        echo json_encode(['success' => false, 'message' => 'Staff member not found']);
        exit;
    }

    // Check if email already exists (excluding current staff)
    $stmt = $pdo->prepare("SELECT StaffID FROM Staff WHERE Email = ? AND StaffID != ?");
    $stmt->execute([$email, $staff_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Check if username already exists (excluding current staff)
    $stmt = $pdo->prepare("SELECT StaffID FROM Staff WHERE Username = ? AND StaffID != ?");
    $stmt->execute([$username, $staff_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit;
    }

    // Handle photo upload
    $photo_filename = $existing_staff['Photo']; // Keep existing photo by default
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/staff/';
        
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

        // Delete old photo if exists
        if ($existing_staff['Photo']) {
            $old_photo_path = $upload_dir . $existing_staff['Photo'];
            if (file_exists($old_photo_path)) {
                unlink($old_photo_path);
            }
        }

        // Generate unique filename
        $photo_filename = 'staff_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $photo_filename;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload photo']);
            exit;
        }
    }

    // Build update query
    if (!empty($password)) {
        // Update with new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE Staff SET 
                FirstName = ?,
                LastName = ?,
                Email = ?,
                Phone = ?,
                Username = ?,
                PasswordHash = ?,
                RoleID = ?,
                HireDate = ?,
                Status = ?,
                Photo = ?,
                UpdatedBy = ?,
                UpdatedAt = NOW()
                WHERE StaffID = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $phone,
            $username,
            $password_hash,
            $role_id,
            $hire_date,
            $status,
            $photo_filename,
            $_SESSION['staff_id'],
            $staff_id
        ]);
    } else {
        // Update without changing password
        $sql = "UPDATE Staff SET 
                FirstName = ?,
                LastName = ?,
                Email = ?,
                Phone = ?,
                Username = ?,
                RoleID = ?,
                HireDate = ?,
                Status = ?,
                Photo = ?,
                UpdatedBy = ?,
                UpdatedAt = NOW()
                WHERE StaffID = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $phone,
            $username,
            $role_id,
            $hire_date,
            $status,
            $photo_filename,
            $_SESSION['staff_id'],
            $staff_id
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Staff member updated successfully'
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
?>