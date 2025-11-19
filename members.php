<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

 $action = $_GET['action'] ?? 'list';
 $member_id = $_GET['id'] ?? null;

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'] ?? null;

    // Basic validation
    $firstName = trim($_POST['FirstName']);
    $lastName = trim($_POST['LastName']);
    $email = trim($_POST['Email']);

    if ($firstName && $lastName && $email) {
        if ($member_id) {
            // Update existing member
            $sql = "UPDATE Member SET FirstName=?, LastName=?, Address=?, City=?, Province=?, Zipcode=?, Gender=?, DateOfBirth=?, PhoneNo=?, Email=?, EmergencyContactName=?, EmergencyContactNumber=?, MembershipStatus=? WHERE MemberID=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $firstName, $lastName, $_POST['Address'], $_POST['City'], $_POST['Province'], $_POST['Zipcode'], $_POST['Gender'], $_POST['DateOfBirth'], $_POST['PhoneNo'], $email, $_POST['EmergencyContactName'], $_POST['EmergencyContactNumber'], $_POST['MembershipStatus'], $member_id
            ]);
            $_SESSION['message'] = "Member updated successfully!";
        } else {
            // Add new member
            $sql = "INSERT INTO Member 
            (FirstName, LastName, Address, City, Province, Zipcode, Gender, DateOfBirth, PhoneNo, Email, JoinDate, EmergencyContactName, EmergencyContactNumber, MembershipStatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, 'Pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $firstName, $lastName, $_POST['Address'], $_POST['City'], $_POST['Province'], $_POST['Zipcode'], $_POST['Gender'], $_POST['DateOfBirth'], $_POST['PhoneNo'], $email, $_POST['EmergencyContactName'], $_POST['EmergencyContactNumber']
            ]);
            $_SESSION['message'] = "New member added successfully!";
        }
        $_SESSION['message_type'] = "success";
        header('Location: members.php');
        exit();
    } else {
        $_SESSION['message'] = "Please fill in all required fields.";
        $_SESSION['message_type'] = "error";
        // If editing, redirect back to the edit page with the ID
        if ($member_id) {
            header("Location: members.php?action=edit&id=$member_id");
        } else {
            header('Location: members.php?action=add');
        }
        exit();
    }
}

// --- Handle Page Rendering ---
if ($action === 'edit' && $member_id) {
    // Fetch member data for editing
    $stmt = $pdo->prepare("SELECT * FROM Member WHERE MemberID = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
    if (!$member) {
        $_SESSION['message'] = "Member not found.";
        $_SESSION['message_type'] = "error";
        header('Location: members.php');
        exit();
    }
} elseif ($action === 'add') {
    // Set up empty member object for the form
    $member = [
        'MemberID' => null, 'FirstName' => '', 'LastName' => '', 'Address' => '', 'City' => '',
        'Province' => '', 'Zipcode' => '', 'Gender' => 'Male', 'DateOfBirth' => '',
        'PhoneNo' => '', 'Email' => '', 'EmergencyContactName' => '', 'EmergencyContactNumber' => '',
        'MembershipStatus' => 'Pending'
    ];
}

// If action is list or anything else, show the list of members
if ($action === 'list') {
    // --- CHANGE: Add filtering logic ---
    $status_filter = $_GET['status_filter'] ?? 'Active'; // Default to showing Active members

    // Base query
    $sql = "
        SELECT MemberID, FirstName, LastName, Email, MembershipStatus 
        FROM Member 
        WHERE 1=1
    ";
    
    // Add filter condition if not 'All'
    $params = [];
    if ($status_filter !== 'All') {
        $sql .= " AND MembershipStatus = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY LastName";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll();
}

?>

<?php include 'includes/header.php'; ?>

<?php if ($action === 'list'): ?>
    <h2>Member Management</h2>
    
    <!-- CHANGE: Add the filter form -->
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" class="form-inline">
                <input type="hidden" name="action" value="list">
                <label for="status_filter" class="mr-2">Show:</label>
                <select name="status_filter" id="status_filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="Active" <?php echo ($status_filter == 'Active') ? 'selected' : ''; ?>>Active Members</option>
                    <option value="Inactive" <?php echo ($status_filter == 'Inactive') ? 'selected' : ''; ?>>Inactive Members</option>
                    <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending Members</option>
                    <option value="All" <?php echo ($status_filter == 'All') ? 'selected' : ''; ?>>All Members</option>
                </select>
            </form>
        </div>
        <div class="col-md-6 text-right">
            <a href="members.php?action=add" class="btn btn-primary">Add New Member</a>
        </div>
    </div>
    
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m): ?>
            <tr>
                <td><?php echo htmlspecialchars($m['FirstName'] . ' ' . $m['LastName']); ?></td>
                <td><?php echo htmlspecialchars($m['Email']); ?></td>
                <td><span class="badge badge-<?php echo $m['MembershipStatus'] == 'Active' ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($m['MembershipStatus']); ?></span></td>
                <td>
                    <a href="members.php?action=edit&id=<?php echo $m['MemberID']; ?>" class="btn btn-sm btn-info">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <h2><?php echo $member['MemberID'] ? 'Edit Member' : 'Add New Member'; ?></h2>
    <form action="members.php" method="post">
        <input type="hidden" name="member_id" value="<?php echo $member['MemberID']; ?>">
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="FirstName">First Name</label>
                <input type="text" class="form-control" name="FirstName" value="<?php echo htmlspecialchars($member['FirstName']); ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="LastName">Last Name</label>
                <input type="text" class="form-control" name="LastName" value="<?php echo htmlspecialchars($member['LastName']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="Address">Address</label>
            <textarea class="form-control" name="Address" rows="3"><?php echo htmlspecialchars($member['Address']); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="City">City</label>
                <input type="text" class="form-control" name="City" value="<?php echo htmlspecialchars($member['City']); ?>">
            </div>
            <div class="form-group col-md-4">
                <label for="Province">Province</label>
                <input type="text" class="form-control" name="Province" value="<?php echo htmlspecialchars($member['Province']); ?>">
            </div>
            <div class="form-group col-md-4">
                <label for="Zipcode">Zipcode</label>
                <input type="text" class="form-control" name="Zipcode" value="<?php echo htmlspecialchars($member['Zipcode']); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="DateOfBirth">Date of Birth</label>
                <input type="date" class="form-control" name="DateOfBirth" value="<?php echo htmlspecialchars($member['DateOfBirth']); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="Gender">Gender</label>
                <select name="Gender" class="form-control">
                    <option value="Male" <?php echo ($member['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($member['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($member['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="PhoneNo">Phone Number</label>
                <input type="tel" class="form-control" name="PhoneNo" value="<?php echo htmlspecialchars($member['PhoneNo']); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="Email">Email</label>
                <input type="email" class="form-control" name="Email" value="<?php echo htmlspecialchars($member['Email']); ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="EmergencyContactName">Emergency Contact Name</label>
                <input type="text" class="form-control" name="EmergencyContactName" value="<?php echo htmlspecialchars($member['EmergencyContactName']); ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="EmergencyContactNumber">Emergency Contact Number</label>
                <input type="tel" class="form-control" name="EmergencyContactNumber" value="<?php echo htmlspecialchars($member['EmergencyContactNumber']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="MembershipStatus">Membership Status</label>
            <select name="MembershipStatus" class="form-control" required>
                <option value="Active" <?php echo ($member['MembershipStatus'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo ($member['MembershipStatus'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="Pending" <?php echo ($member['MembershipStatus'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-success"><?php echo $member['MemberID'] ? 'Update Member' : 'Add Member'; ?></button>
        <a href="members.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>