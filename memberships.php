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
    $status = $_POST['Status']; // user-selected (may be Cancelled, otherwise ignored)
    
    // Fetch plan details to calculate end date
    $stmt = $pdo->prepare("SELECT Duration, PlanType FROM Plan WHERE PlanID = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    $duration = $plan['Duration'];
    $plan_type = strtolower($plan['PlanType']);

    // Calculate EndDate based on PlanType
    if ($plan_type == 'days') {
        $end_date = date('Y-m-d', strtotime("+$duration days", strtotime($start_date)));
    } elseif ($plan_type == 'months') {
        $end_date = date('Y-m-d', strtotime("+$duration months", strtotime($start_date)));
    } elseif ($plan_type == 'years') {
        $end_date = date('Y-m-d', strtotime("+$duration years", strtotime($start_date)));
    } else {
        $end_date = date('Y-m-d', strtotime("+$duration months", strtotime($start_date)));
    }

    // --- auto-compute status ---
    $computed_status = ($status === 'Cancelled') 
        ? 'Cancelled'
        : (($end_date < date('Y-m-d')) ? 'Expired' : 'Active');

    $staff_id = $_SESSION['staff_id'];

    if ($membership_id) {
        // UPDATE EXISTING MEMBERSHIP
        $sql = "UPDATE Membership 
                SET MemberID=?, PlanID=?, StartDate=?, EndDate=?, Status=? 
                WHERE MembershipID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $plan_id, $start_date, $end_date, $computed_status, $membership_id]);
        $_SESSION['message'] = "Membership updated successfully!";

    } else {
        // CREATE NEW MEMBERSHIP (RENEW)
        $sql = "INSERT INTO Membership (MemberID, PlanID, StaffID, StartDate, EndDate, Status)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $plan_id, $staff_id, $start_date, $end_date, $computed_status]);
        $new_membership_id = $pdo->lastInsertId();

        // Create agreement
        $terms = "Standard gym membership agreement.";
        $sql = "INSERT INTO Agreement (MemberID, MembershipID, AgreementDate, Terms)
                VALUES (?, ?, CURDATE(), ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $new_membership_id, $terms]);

        // Member auto-active if membership is active
        if ($computed_status === 'Active') {
            $sql = "UPDATE Member SET MembershipStatus = 'Active' WHERE MemberID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$member_id]);
        }

        $_SESSION['message'] = "Membership saved successfully!";
    }

    // --- SYNC MEMBER STATUS ---
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM Membership
        WHERE MemberID = ?
          AND Status = 'Active'
          AND EndDate >= CURDATE()
    ");
    $check->execute([$member_id]);
    $hasActive = $check->fetchColumn();

    $memberStatus = $hasActive ? 'Active' : 'Inactive';
    $u = $pdo->prepare("UPDATE Member SET MembershipStatus = ? WHERE MemberID = ?");
    $u->execute([$memberStatus, $member_id]);

    $_SESSION['message_type'] = "success";
    header('Location: memberships.php');
    exit();
}


// --- Handle Page Rendering ---
if ($action === 'edit' && $membership_id) {
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
    $stmt = $pdo->prepare("SELECT * FROM Membership WHERE MembershipID = ?");
    $stmt->execute([$membership_id]);
    $membership = $stmt->fetch();
    
    $membership['MembershipID'] = null;
    $membership['StartDate'] = date('Y-m-d');

    if (!$membership) {
        $_SESSION['message'] = "Membership not found.";
        $_SESSION['message_type'] = "error";
        header('Location: memberships.php');
        exit();
    }

} elseif ($action === 'add') {
    $membership = [
        'MembershipID' => null,
        'MemberID' => '',
        'PlanID' => '',
        'StartDate' => date('Y-m-d'),
        'Status' => 'Active'
    ];
}

$members = $pdo->query("SELECT MemberID, FirstName, LastName FROM Member ORDER BY LastName, FirstName")->fetchAll();
$plans = $pdo->query("SELECT PlanID, PlanName, Rate FROM Plan WHERE IsActive = 1")->fetchAll();

if ($action === 'list') {
    // Default filter: ALL
    $status_filter = $_GET['status_filter'] ?? 'All';
    $search = trim($_GET['search'] ?? '');

    $sql = "
        SELECT m.MembershipID,
               CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
               mem.FirstName,
               mem.LastName,
               p.PlanName,
               m.StartDate,
               m.EndDate,
               m.Status,
               m.PlanID
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

    if ($search !== '') {
        $sql .= " AND (mem.FirstName LIKE ?
                       OR mem.LastName LIKE ?
                       OR CONCAT(mem.FirstName, ' ', mem.LastName) LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    //New sort: Status (Active→Expired→Cancelled), then LastName, FirstName
    $sql .= " 
        ORDER BY 
            CASE m.Status
                WHEN 'Active' THEN 1
                WHEN 'Expired' THEN 2
                WHEN 'Cancelled' THEN 3
                ELSE 4
            END,
            mem.LastName,
            mem.FirstName
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $memberships = $stmt->fetchAll();
}
?>

<?php include 'includes/header.php'; ?>

<?php if ($action === 'list'): ?>
    <h2>Membership Management</h2>

    <div class="row mb-3">
        <div class="col-md-8">
            <form method="GET" class="form-inline">
                <input type="hidden" name="action" value="list">

                <label for="status_filter" class="mr-2">Show:</label>
                <select name="status_filter" id="status_filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="All" <?php echo ($status_filter == 'All') ? 'selected' : ''; ?>>All Memberships</option>
                    <option value="Active" <?php echo ($status_filter == 'Active') ? 'selected' : ''; ?>>Active Memberships</option>
                    <option value="Expired" <?php echo ($status_filter == 'Expired') ? 'selected' : ''; ?>>Expired Memberships</option>
                    <option value="Cancelled" <?php echo ($status_filter == 'Cancelled') ? 'selected' : ''; ?>>Cancelled Memberships</option>
                </select>

                <label for="search" class="mr-2">Search:</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-control mr-2"
                    placeholder="Member name..."
                    value="<?php echo htmlspecialchars($search ?? ''); ?>">

                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
        <div class="col-md-4 text-right">
            <a href="memberships.php?action=add" class="btn btn-primary">Create New Membership</a>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Member</th>
                <th>Plan</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($memberships as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['MemberName']); ?></td>
                <td><?= htmlspecialchars($m['PlanName']); ?></td>
                <td><?= date('M j, Y', strtotime($m['StartDate'])); ?></td>
                <td><?= date('M j, Y', strtotime($m['EndDate'])); ?></td>
                <td>
                    <span class="badge 
                        <?php 
                            echo $m['Status'] === 'Active' ? 'badge-success' : 
                                ($m['Status'] === 'Expired' ? 'badge-danger' : 'badge-secondary'); 
                        ?>
                    ">
                        <?= htmlspecialchars($m['Status']); ?>
                    </span>
                </td>
                <td>
                    <a href="memberships.php?action=edit&id=<?= $m['MembershipID']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="memberships.php?action=renew&id=<?= $m['MembershipID']; ?>" class="btn btn-sm btn-warning">Renew</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>
    <h2>
        <?= $action === 'edit' ? "Edit Membership" : ($action === 'renew' ? "Renew Membership" : "Create Membership"); ?>
    </h2>

    <form action="memberships.php" method="post">
        <input type="hidden" name="membership_id" value="<?= $membership['MembershipID'] ?? ''; ?>">

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Member</label>
                <select name="MemberID" class="form-control" required>
                    <option value="">Select Member...</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['MemberID']; ?>" <?= (isset($membership['MemberID']) && $membership['MemberID'] == $m['MemberID']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($m['FirstName'] . ' ' . $m['LastName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group col-md-6">
                <label>Plan</label>
                <select name="PlanID" class="form-control" required>
                    <option value="">Select Plan...</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= $plan['PlanID']; ?>" <?= (isset($membership['PlanID']) && $membership['PlanID'] == $plan['PlanID']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($plan['PlanName'] . ' - ₱' . number_format($plan['Rate'], 2)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Start Date</label>
                <input type="date" name="StartDate" class="form-control" value="<?= $membership['StartDate'] ?? ''; ?>" required>
            </div>

            <div class="form-group col-md-6">
                <label>Status (only applied if Cancelled)</label>
                <select name="Status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Expired">Expired</option>
                    <option value="Cancelled" <?= isset($membership['Status']) && $membership['Status'] == 'Cancelled' ? 'selected' : ''; ?>>
                        Cancelled
                    </option>
                </select>
            </div>
        </div>

        <button class="btn btn-success">
            <?= $action === 'edit' ? "Update Membership" : ($action === 'renew' ? "Create Renewal" : "Create Membership"); ?>
        </button>
        <a href="memberships.php" class="btn btn-secondary">Cancel</a>
    </form>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
