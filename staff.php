<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

 $action = $_GET['action'] ?? 'list';
 $staff_id = $_GET['id'] ?? null;

// --- Handle Form Submissions (Add/Edit) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = $_POST['staff_id'] ?? null;
    $full_name = trim($_POST['FullName']);
    $username = trim($_POST['Username']);
    $role_id = $_POST['RoleID'];
    $hire_date = $_POST['HireDate'];
    $status = $_POST['Status'];
    $password = $_POST['Password']; // Can be empty on edit

    // Basic validation
    if ($full_name && $username && $role_id && $hire_date) {
        if ($staff_id) {
            // --- UPDATE existing staff member ---
            if (!empty($password)) {
                // If a new password is provided, hash it and update
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE Staff SET FullName=?, RoleID=?, Username=?, Password=?, HireDate=?, Status=? WHERE StaffID=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $role_id, $username, $hashed_password, $hire_date, $status, $staff_id]);
            } else {
                // If password is empty, do not update the password field
                $sql = "UPDATE Staff SET FullName=?, RoleID=?, Username=?, HireDate=?, Status=? WHERE StaffID=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $role_id, $username, $hire_date, $status, $staff_id]);
            }
            $_SESSION['message'] = "Staff member updated successfully!";
        } else {
            // --- ADD new staff member ---
            if (empty($password)) {
                $_SESSION['message'] = "Password is required for new staff members.";
                $_SESSION['message_type'] = "error";
                header("Location: staff.php?action=add");
                exit();
            }
            // Hash the password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Staff (FullName, RoleID, Username, Password, HireDate, Status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $role_id, $username, $hashed_password, $hire_date, $status]);
            $_SESSION['message'] = "New staff member added successfully!";
        }
        $_SESSION['message_type'] = "success";
        header('Location: staff.php');
        exit();
    } else {
        $_SESSION['message'] = "Please fill in all required fields.";
        $_SESSION['message_type'] = "error";
        $redirect_url = $staff_id ? "staff.php?action=edit&id=$staff_id" : "staff.php?action=add";
        header("Location: $redirect_url");
        exit();
    }
}

// --- Handle Activate/Deactivate ---
if ($action === 'toggle_status' && $staff_id) {
    // Prevent a user from deactivating themselves
    if ($staff_id == $_SESSION['staff_id']) {
        $_SESSION['message'] = "You cannot deactivate your own account.";
        $_SESSION['message_type'] = "error";
    } else {
        $sql = "UPDATE Staff SET Status = NOT Status WHERE StaffID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$staff_id]);
        $_SESSION['message'] = "Staff status updated successfully!";
        $_SESSION['message_type'] = "success";
    }
    header('Location: staff.php');
    exit();
}


// --- Handle Page Rendering ---
if ($action === 'edit' && $staff_id) {
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE StaffID = ?");
    $stmt->execute([$staff_id]);
    $staff_member = $stmt->fetch();
    if (!$staff_member) {
        $_SESSION['message'] = "Staff member not found.";
        $_SESSION['message_type'] = "error";
        header('Location: staff.php');
        exit();
    }
} elseif ($action === 'add') {
    // Set up empty staff member object for the form
    $staff_member = [
        'StaffID' => null, 'FullName' => '', 'Username' => '', 'RoleID' => '',
        'HireDate' => date('Y-m-d'), 'Status' => 'Active'
    ];
}

// Fetch all roles for the dropdown
 $roles = $pdo->query("SELECT RoleID, RoleName FROM Roles ORDER BY RoleName")->fetchAll();

// If action is list, show the list of staff
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT s.StaffID, s.FullName, s.Username, s.HireDate, s.Status, r.RoleName
        FROM Staff s
        JOIN Roles r ON s.RoleID = r.RoleID
        ORDER BY s.FullName
    ");
    $staff_list = $stmt->fetchAll();
}

?>

<?php include 'includes/header.php'; ?>

<?php if ($action === 'list'): ?>
    <h2>Staff Management</h2>
    <a href="staff.php?action=add" class="btn btn-primary mb-3">Add New Staff</a>
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Hire Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff_list as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['FullName']); ?></td>
                <td><?php echo htmlspecialchars($s['Username']); ?></td>
                <td><?php echo htmlspecialchars($s['RoleName']); ?></td>
                <td><?php echo date('M j, Y', strtotime($s['HireDate'])); ?></td>
                <td>
                    <span class="badge badge-<?php echo $s['Status'] == 'Active' ? 'success' : 'secondary'; ?>">
                        <?php echo $s['Status']; ?>
                    </span>
                </td>
                <td>
                    <a href="staff.php?action=edit&id=<?php echo $s['StaffID']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <?php if ($s['StaffID'] != $_SESSION['staff_id']): ?>
                    <a href="staff.php?action=toggle_status&id=<?php echo $s['StaffID']; ?>" class="btn btn-sm <?php echo $s['Status'] == 'Active' ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo $s['Status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <h2><?php echo $staff_member['StaffID'] ? 'Edit Staff Member' : 'Add New Staff Member'; ?></h2>
    <form action="staff.php" method="post">
        <input type="hidden" name="staff_id" value="<?php echo $staff_member['StaffID']; ?>">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="FullName">Full Name</label>
                <input type="text" class="form-control" name="FullName" value="<?php echo htmlspecialchars($staff_member['FullName']); ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="Username">Username</label>
                <input type="text" class="form-control" name="Username" value="<?php echo htmlspecialchars($staff_member['Username']); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="RoleID">Role</label>
                <select name="RoleID" class="form-control" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['RoleID']; ?>" <?php echo ($staff_member['RoleID'] == $role['RoleID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['RoleName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="HireDate">Hire Date</label>
                <input type="date" class="form-control" name="HireDate" value="<?php echo htmlspecialchars($staff_member['HireDate']); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="Password">Password</label>
                <input type="password" class="form-control" name="Password" placeholder="<?php echo $staff_member['StaffID'] ? 'Leave blank to keep current' : 'Enter new password'; ?>">
                <?php if ($staff_member['StaffID']): ?>
                    <small class="form-text text-muted">Leave this field blank if you don't want to change the password.</small>
                <?php endif; ?>
            </div>
            <div class="form-group col-md-6">
                <label for="Status">Status</label>
                <select name="Status" class="form-control" required>
                    <option value="Active" <?php echo ($staff_member['Status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($staff_member['Status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-success"><?php echo $staff_member['StaffID'] ? 'Update Staff' : 'Add Staff'; ?></button>
        <a href="staff.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>