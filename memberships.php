<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

 $action = $_GET['action'] ?? 'list';
 $membership_id = $_GET['id'] ?? null;

// --- Handle Form Submissions (for Add/Renew/Update) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $membership_id = $_POST['membership_id'] ?? null;
    $member_id = $_POST['MemberID'];
    $plan_id = $_POST['PlanID'];
    $start_date = $_POST['StartDate'];
    $status = $_POST['Status'];

    // Fetch plan details to calculate end date
    $stmt = $pdo->prepare("SELECT Duration, PlanType FROM Plan WHERE PlanID = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    $duration = $plan['Duration'];
    $plan_type = strtolower($plan['PlanType']);

    // Calculate EndDate based on the PlanType
    if ($plan_type == 'days') {
        $end_date = date('Y-m-d', strtotime("+$duration days", strtotime($start_date)));
    } elseif ($plan_type == 'months') {
        $end_date = date('Y-m-d', strtotime("+$duration months", strtotime($start_date)));
    } elseif ($plan_type == 'years') {
        $end_date = date('Y-m-d', strtotime("+$duration years", strtotime($start_date)));
    } else {
        $end_date = date('Y-m-d', strtotime("+$duration months", strtotime($start_date)));
    }

    $staff_id = $_SESSION['staff_id'];

    if ($membership_id) {
        // --- CHANGE: This is now an UPDATE to the existing membership ---
        $sql = "UPDATE Membership SET MemberID=?, PlanID=?, StartDate=?, EndDate=?, Status=? WHERE MembershipID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $plan_id, $start_date, $end_date, $status, $membership_id]);
        $_SESSION['message'] = "Membership updated successfully!";
    } else {
        // --- This is a RENEWAL, which creates a NEW membership record ---
        $sql = "INSERT INTO Membership (MemberID, PlanID, StaffID, StartDate, EndDate, Status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $plan_id, $staff_id, $start_date, $end_date, $status]);
        $new_membership_id = $pdo->lastInsertId();

        // Create Agreement for the new membership
        $terms = "Standard gym membership agreement.";
        $sql = "INSERT INTO Agreement (MemberID, MembershipID, AgreementDate, Terms) VALUES (?, ?, CURDATE(), ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $new_membership_id, $terms]);
        
        $_SESSION['message'] = "Membership renewed successfully!";
    }
    $_SESSION['message_type'] = "success";
    header('Location: memberships.php');
    exit();
}

// --- Handle Page Rendering ---
if ($action === 'edit' && $membership_id) {
    // Fetch the specific membership to be edited
    $stmt = $pdo->prepare("SELECT * FROM Membership WHERE MembershipID = ?");
    $stmt->execute([$membership_id]);
    $membership = $stmt->fetch();
    if (!$membership) {
        $_SESSION['message'] = "Membership not found.";
        $_SESSION['message_type'] = "error";
        header('Location: memberships.php');
        exit();
    }
} elseif ($action === 'renew' && $membership_id) {
    // Fetch the membership to be renewed to pre-fill the form
    $stmt = $pdo->prepare("SELECT * FROM Membership WHERE MembershipID = ?");
    $stmt->execute([$membership_id]);
    $membership = $stmt->fetch();
    // Set the ID to null so the form creates a new record
    $membership['MembershipID'] = null;
    // Set a new default start date for the renewal
    $membership['StartDate'] = date('Y-m-d');
    if (!$membership) {
        $_SESSION['message'] = "Membership not found.";
        $_SESSION['message_type'] = "error";
        header('Location: memberships.php');
        exit();
    }
} elseif ($action === 'add') {
    $membership = [
        'MembershipID' => null, 'MemberID' => '', 'PlanID' => '', 'StartDate' => date('Y-m-d'), 'Status' => 'Active'
    ];
}

 $members = $pdo->query("SELECT MemberID, FirstName, LastName FROM Member ORDER BY LastName")->fetchAll();
 $plans = $pdo->query("SELECT PlanID, PlanName, Rate FROM Plan WHERE IsActive = 1")->fetchAll();

if ($action === 'list') {
    $status_filter = $_GET['status_filter'] ?? 'Active';
    $sql = "
        SELECT m.MembershipID, CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName, p.PlanName, m.StartDate, m.EndDate, m.Status, m.PlanID
        FROM Membership m
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN Plan p ON m.PlanID = p.PlanID
        WHERE 1=1
    ";
    $params = [];
    if ($status_filter !== 'All') {
        $sql .= " AND m.Status = ?";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY m.StartDate DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $memberships = $stmt->fetchAll();
}
?>

<?php include 'includes/header.php'; ?>

<?php if ($action === 'list'): ?>
    <h2>Membership Management</h2>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" class="form-inline">
                <input type="hidden" name="action" value="list">
                <label for="status_filter" class="mr-2">Show:</label>
                <select name="status_filter" id="status_filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="Active" <?php echo ($status_filter == 'Active') ? 'selected' : ''; ?>>Active Memberships</option>
                    <option value="Expired" <?php echo ($status_filter == 'Expired') ? 'selected' : ''; ?>>Expired Memberships</option>
                    <option value="Cancelled" <?php echo ($status_filter == 'Cancelled') ? 'selected' : ''; ?>>Cancelled Memberships</option>
                    <option value="All" <?php echo ($status_filter == 'All') ? 'selected' : ''; ?>>All Memberships</option>
                </select>
            </form>
        </div>
        <div class="col-md-6 text-right">
            <a href="memberships.php?action=add" class="btn btn-primary">Create New Membership</a>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Member</th>
                <th>Plan</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($memberships as $m): ?>
            <tr>
                <td><?php echo htmlspecialchars($m['MemberName']); ?></td>
                <td>
                    <select class="form-control form-control-sm quick-edit-plan" data-membership-id="<?php echo $m['MembershipID']; ?>">
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?php echo $plan['PlanID']; ?>" <?php echo ($m['PlanID'] == $plan['PlanID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($plan['PlanName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><?php echo date('M j, Y', strtotime($m['StartDate'])); ?></td>
                <td><?php echo date('M j, Y', strtotime($m['EndDate'])); ?></td>
                <td>
                    <select class="form-control form-control-sm quick-edit-status" data-membership-id="<?php echo $m['MembershipID']; ?>">
                        <option value="Active" <?php echo ($m['Status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Expired" <?php echo ($m['Status'] == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                        <option value="Cancelled" <?php echo ($m['Status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </td>
                <td>
                    <!-- CHANGE: Separate Edit and Renew buttons -->
                    <a href="memberships.php?action=edit&id=<?php echo $m['MembershipID']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="memberships.php?action=renew&id=<?php echo $m['MembershipID']; ?>" class="btn btn-sm btn-warning">Renew</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- JavaScript for AJAX updates (this remains the same) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusDropdowns = document.querySelectorAll('.quick-edit-status');
        const planDropdowns = document.querySelectorAll('.quick-edit-plan');

        function handleUpdate(event) {
            const dropdown = event.target;
            const membershipId = dropdown.dataset.membershipId;
            const newValue = dropdown.value;
            const field = dropdown.classList.contains('quick-edit-status') ? 'status' : 'plan';

            if (!confirm(`Are you sure you want to change the ${field} to "${dropdown.options[dropdown.selectedIndex].text}"?`)) {
                dropdown.value = dropdown.defaultValue;
                return;
            }

            const formData = new FormData();
            formData.append('membership_id', membershipId);
            formData.append('field', field);
            formData.append('value', newValue);

            fetch('api/update_membership.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                    dropdown.value = dropdown.defaultValue;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
                dropdown.value = dropdown.defaultValue;
            });
        }

        statusDropdowns.forEach(dropdown => dropdown.addEventListener('change', handleUpdate));
        planDropdowns.forEach(dropdown => dropdown.addEventListener('change', handleUpdate));
    });
    </script>

<?php elseif ($action === 'edit' || $action === 'renew' || $action === 'add'): ?>
    <!-- CHANGE: The form is now used for both editing and renewing -->
    <h2>
        <?php 
            if ($action === 'edit') echo 'Edit Membership';
            elseif ($action === 'renew') echo 'Renew Membership';
            else echo 'Create New Membership'; 
        ?>
    </h2>
    <form action="memberships.php" method="post">
        <input type="hidden" name="membership_id" value="<?php echo $membership['MembershipID'] ?? ''; ?>">
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="MemberID">Member</label>
                <select name="MemberID" class="form-control" required>
                    <option value="">Select Member...</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo $member['MemberID']; ?>" <?php echo (isset($membership['MemberID']) && $membership['MemberID'] == $member['MemberID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['FirstName'] . ' ' . $member['LastName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="PlanID">Plan</label>
                <select name="PlanID" class="form-control" required>
                    <option value="">Select Plan...</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['PlanID']; ?>" <?php echo (isset($membership['PlanID']) && $membership['PlanID'] == $plan['PlanID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($plan['PlanName'] . ' - ₱' . number_format($plan['Rate'], 2)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="StartDate">Start Date</label>
                <input type="date" class="form-control" name="StartDate" value="<?php echo htmlspecialchars($membership['StartDate'] ?? ''); ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="Status">Status</label>
                <select name="Status" class="form-control" required>
                    <option value="Active" <?php echo (isset($membership['Status']) && $membership['Status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Expired" <?php echo (isset($membership['Status']) && $membership['Status'] == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                    <option value="Cancelled" <?php echo (isset($membership['Status']) && $membership['Status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-success">
            <?php 
                if ($action === 'edit') echo 'Update Membership';
                elseif ($action === 'renew') echo 'Create Renewal';
                else echo 'Create Membership'; 
            ?>
        </button>
        <a href="memberships.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>