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
    if ($membership_id) {
        // Updating membership
        if ($status === 'Cancelled') {
            $computed_status = 'Cancelled';
        } elseif ($status === 'Pending') {
            $computed_status = 'Pending';
        } else {
            // Auto-compute Active or Expired
            $computed_status = ($end_date < date('Y-m-d')) ? 'Expired' : 'Active';
        }
    } else {
        // NEW membership → always Pending
        $computed_status = 'Pending';
    }

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

$members = $pdo->query("SELECT MemberID, FirstName, LastName FROM Member ORDER BY LastName, FirstName")->fetchAll();
$plans = $pdo->query("SELECT PlanID, PlanName, Rate FROM Plan WHERE IsActive = 1")->fetchAll();

if ($action === 'list') {
    // Default filter: ALL
    $status_filter = $_GET['status_filter'] ?? 'All';
    $search = trim($_GET['search'] ?? '');

    $sql = "
        SELECT m.MembershipID,
               m.MemberID,
               CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
               mem.FirstName,
               mem.LastName,
               p.PlanName,
               m.StartDate,
               m.EndDate,
               m.Status,
               m.PlanID,
               DATEDIFF(m.EndDate, CURDATE()) AS DaysLeft
        FROM Membership m
        JOIN Member mem ON m.MemberID = mem.MemberID
        LEFT JOIN Plan p ON m.PlanID = p.PlanID
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
                WHEN 'Pending' THEN 2
                WHEN 'Expired' THEN 3
                WHEN 'Cancelled' THEN 4
                ELSE 5
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

<div class="page-header">
    <h1 class="page-title">Memberships</h1>
</div>

<!-- Search, Filter, Add -->
<div class="page-actions">
    <div class="search-filter-group">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search membership..." id="membershipsearchInput" value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="filter-dropdown">
            <button class="filter-btn" onclick="toggleFilter()">
                <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down"></i>
            </button>

            <div class="filter-dropdown-content" id="filterDropdown">
                <form method="GET" action="memberships.php">
                    <input type="hidden" name="action" value="list">

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="All" id="filterAll"
                            <?= $status_filter == 'All' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterAll">All Memberships</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Active" id="filterActive"
                            <?= $status_filter == 'Active' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterActive">Active</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Pending" id="filterPending"
                            <?= $status_filter == 'Pending' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterPending">Pending</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Expired" id="filterExpired"
                            <?= $status_filter == 'Expired' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterExpired">Expired</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Cancelled" id="filterCancelled"
                            <?= $status_filter == 'Cancelled' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterCancelled">Cancelled</label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="#" class="add-member-btn" onclick="event.preventDefault(); openAddMembershipModal();">
        <i class="bi bi-plus-lg"></i> Create Membership
    </a>
</div>

<!-- Memberships Table -->
<div class="members-table-container">
    <table class="members-table">
        <thead>
            <tr>
                <th>Member</th>
                <th>Plan</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days Left</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($memberships) > 0): ?>
                <?php foreach ($memberships as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['MemberName']) ?></td>
                        <td>
                            <?= $m['PlanName'] ? htmlspecialchars($m['PlanName']) : '<span style="color:#718096;">No Plan Assigned</span>' ?>
                        </td>
                        <td>
                            <?= $m['StartDate'] ? date('m/d/y', strtotime($m['StartDate'])) : '—' ?>
                        </td>
                        <td>
                            <?= $m['EndDate'] ? date('m/d/y', strtotime($m['EndDate'])) : '—' ?>
                        </td>
                        <td>
                            <?php if ($m['Status'] === 'Pending'): ?>
                                <span style="color:#718096;">—</span>
                            <?php else: ?>
                                <?php
                                $daysLeft = $m['DaysLeft'];
                                if ($m['Status'] === 'Cancelled') {
                                    echo '<span style="color: #718096;">Cancelled</span>';
                                } elseif ($m['Status'] === 'Expired') {
                                    $daysExpired = abs($daysLeft);
                                    echo '<span style="color: #e53e3e;">Expired (' . $daysExpired . ' day' . ($daysExpired != 1 ? 's' : '') . ')</span>';
                                } elseif ($daysLeft < 0) {
                                    echo '<span style="color: #e53e3e;">Expired</span>';
                                } elseif ($daysLeft == 0) {
                                    echo '<span style="color: #dd6b20;">Expires today</span>';
                                } elseif ($daysLeft <= 7) {
                                    echo '<span style="color: #dd6b20;">' . $daysLeft . ' day' . ($daysLeft != 1 ? 's' : '') . ' left</span>';
                                } else {
                                    echo '<span style="color: #38a169;">' . $daysLeft . ' day' . ($daysLeft != 1 ? 's' : '') . ' left</span>';
                                }
                                ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?= strtolower($m['Status']) ?>">
                                <?= htmlspecialchars($m['Status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view-btn" onclick="viewMembershipDetails(<?= $m['MembershipID'] ?>)" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="action-btn" onclick="editMembership(<?= $m['MembershipID'] ?>)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="action-btn" onclick="renewMembership(<?= $m['MembershipID'] ?>)">
                                    <i class="bi bi-arrow-repeat"></i> Renew
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:#718096;">
                        No memberships found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-container">
        <div class="pagination-info">
            Showing 1 to <?= count($memberships) ?> of <?= count($memberships) ?> results
        </div>
        <div class="pagination">
            <button class="pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
            <button class="pagination-btn active">1</button>
        </div>
    </div>
</div>

<script>

// Open add membership modal - now uses modal instead of redirect
function openAddMembershipModal() {
    openAddMembershipModal();
}
</script>

<?php else: ?>
    <!-- Form view remains the same for now -->
    <div class="page-header">
        <h1 class="page-title">
            <?= $action === 'edit' ? "Edit Membership" : ($action === 'renew' ? "Renew Membership" : "Create Membership") ?>
        </h1>
    </div>

    <div class="dashboard-card">
        <form action="memberships.php" method="post">
            <input type="hidden" name="membership_id" value="<?= $membership['MembershipID'] ?? '' ?>">

            <div class="form-grid">
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="bi bi-person"></i> Member
                        <span class="required">*</span>
                    </label>
                    <select name="MemberID" class="form-control-modern" required>
                        <option value="">Select Member...</option>
                        <?php foreach ($members as $mem): ?>
                            <option value="<?= $mem['MemberID'] ?>" 
                                <?= (isset($membership['MemberID']) && $membership['MemberID'] == $mem['MemberID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mem['FirstName'] . ' ' . $mem['LastName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="bi bi-tag"></i> Plan
                        <span class="required">*</span>
                    </label>
                    <select name="PlanID" class="form-control-modern" required>
                        <option value="">Select Plan...</option>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?= $plan['PlanID'] ?>" 
                                <?= (isset($membership['PlanID']) && $membership['PlanID'] == $plan['PlanID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($plan['PlanName'] . ' - ₱' . number_format($plan['Rate'], 2)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="bi bi-calendar"></i> Start Date
                        <span class="required">*</span>
                    </label>
                    <input type="date" name="StartDate" class="form-control-modern" 
                           value="<?= $membership['StartDate'] ?? '' ?>" required>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="bi bi-check-circle"></i> Status
                    </label>
                    <select name="Status" class="form-control-modern">
                        <option value="Pending"
                            <?= isset($membership['Status']) && $membership['Status'] == 'Pending' ? 'selected' : '' ?>>
                            Pending
                        </option>
                        <option value="Active"
                            <?= isset($membership['Status']) && $membership['Status'] == 'Active' ? 'selected' : '' ?>>
                            Active
                        </option>
                        <option value="Expired"
                            <?= isset($membership['Status']) && $membership['Status'] == 'Expired' ? 'selected' : '' ?>>
                            Expired
                        </option>
                        <option value="Cancelled" 
                            <?= isset($membership['Status']) && $membership['Status'] == 'Cancelled' ? 'selected' : '' ?>>
                            Cancelled
                        </option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <button type="submit" class="btn-primary-modern">
                    <i class="bi bi-check-lg"></i>
                    <?= $action === 'edit' ? "Update Membership" : ($action === 'renew' ? "Create Renewal" : "Create Membership") ?>
                </button>
                <a href="memberships.php" class="btn-secondary-modern" style="text-decoration: none;">
                    Cancel
                </a>
            </div>
        </form>
    </div>

<?php endif; ?>

<!-- Include modals outside the if/else so scripts are always available -->
<?php include 'includes/footer.php'; ?>
<?php include 'includes/membership_add_modal.php'; ?>
<?php include 'includes/membership_edit_modal.php'; ?>
<?php include 'includes/membership_renew_modal.php'; ?>
<?php include 'includes/membership_view_modal.php'; ?>