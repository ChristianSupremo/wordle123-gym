<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/payment_modal_helpers.php';
require_once 'includes/helpers/table_sort_helper.php'; // Add this line
check_login();

 $action = $_GET['action'] ?? 'list';
 $payment_id = $_GET['id'] ?? null;

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = $_POST['payment_id'] ?? null;
    $membership_id = $_POST['MembershipID'];
    $payment_method_id = $_POST['PaymentMethod'];
    $amount_paid = $_POST['AmountPaid'];
    $reference_number = $_POST['ReferenceNumber'] ?? null;
    $remarks = $_POST['Remarks'] ?? null;
    $payment_status = $_POST['PaymentStatus'] ?? 'Completed';
    $staff_id = $_SESSION['staff_id'];

    if ($payment_id) {
        // UPDATE
        $sql = "UPDATE Payment 
                SET PaymentMethodID=?, AmountPaid=?, ReferenceNumber=?, Remarks=?, PaymentStatus=? 
                WHERE PaymentID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$payment_method_id, $amount_paid, $reference_number, $remarks, $payment_status, $payment_id]);

        $_SESSION['message'] = "Payment updated successfully!";
    } else {
        // INSERT
        $sql = "INSERT INTO Payment (MembershipID, PaymentMethodID, StaffID, AmountPaid, ReferenceNumber, Remarks, PaymentStatus, PaymentDate)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$membership_id, $payment_method_id, $staff_id, $amount_paid, $reference_number, $remarks, $payment_status]);

        $_SESSION['message'] = "Payment recorded successfully!";
    }

    $_SESSION['message_type'] = "success";
    header('Location: payments.php');
    exit();
}

// Fetch data for list view
if ($action === 'list') {
    $status_filter = $_GET['status_filter'] ?? 'All';
    $search = trim($_GET['search'] ?? '');
    
    // Get sorting parameters
    $sort_config = get_payments_sort_config();
    $sort_params = get_sort_params($sort_config);

    $sql = "
        SELECT 
            p.PaymentID,
            p.MembershipID AS PaymentMembershipID,
            m.MembershipID,
            p.PaymentDate,
            p.AmountPaid,
            pm.PaymentMethodID,
            pm.MethodName AS PaymentMethod,
            p.ReferenceNumber,
            p.PaymentStatus,
            p.Remarks,
            
            -- Member Name
            CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
            mem.FirstName,
            mem.LastName,

            pl.PlanName,
            pl.Rate,
            m.StartDate,
            m.EndDate,
            m.Status AS MembershipStatus,

            -- Updated Staff Name (no more FullName field)
            CONCAT(s.FirstName, ' ', s.LastName) AS StaffName

        FROM Membership m
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN Plan pl ON m.PlanID = pl.PlanID
        LEFT JOIN Payment p ON m.MembershipID = p.MembershipID
        LEFT JOIN PaymentMethods pm ON p.PaymentMethodID = pm.PaymentMethodID
        LEFT JOIN Staff s ON p.StaffID = s.StaffID
        WHERE 1=1
    ";

    $params = [];

    if ($status_filter !== 'All') {
        if ($status_filter === 'No Payment') {
            $sql .= " AND p.PaymentID IS NULL";
        } else {
            $sql .= " AND p.PaymentStatus = ?";
            $params[] = $status_filter;
        }
    }

    if ($search !== '') {
        $sql .= " AND (mem.FirstName LIKE ?
                       OR mem.LastName LIKE ?
                       OR CONCAT(mem.FirstName, ' ', mem.LastName) LIKE ?
                       OR p.ReferenceNumber LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Add sorting
    if (!empty($sort_params['column'])) {
        $sql .= " ORDER BY " . $sort_params['column'] . " " . $sort_params['order'];
    } else {
        $sql .= " ORDER BY 
                  CASE WHEN p.PaymentDate IS NULL THEN 1 ELSE 0 END,
                  p.PaymentDate DESC,
                  m.StartDate DESC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
}

// Fetch active memberships and payment methods for modals
 $active_memberships = getActiveMemberships($pdo);
 $payment_methods = getPaymentMethods($pdo);

?>

<?php include 'includes/header.php'; ?>

<!-- Include shared CSS -->
<link rel="stylesheet" href="assets/css/style/payment_modals.css">

<?php if ($action === 'list'): ?>

<div class="page-header">
    <h1 class="page-title">
        Payment History
        <?php 
        // Display sort indicator
        if (!empty($sort_params['column'])) {
            echo get_sort_indicator(
                $sort_params['column'], 
                $sort_params['order'], 
                $sort_config['column_labels']
            );
        }
        ?>
    </h1>
    <?php echo render_clear_sort_button(); ?>
</div>

<!-- Search, Filter, Add -->
<div class="page-actions">
    <div class="search-filter-group">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
                type="text" 
                placeholder="Search member or reference..." 
                id="globalSearchInput"
                data-page="payments.php"
                data-param="search"
                value="<?= htmlspecialchars($search) ?>"
            >
        </div>

        <div class="filter-dropdown">
            <button class="filter-btn" onclick="toggleFilter('payments')">
                <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down"></i>
            </button>

            <div class="filter-dropdown-content" id="filterDropdown_payments">
                <form method="GET" action="payments.php">
                    <input type="hidden" name="action" value="list">
                    
                    <!-- Preserve sort parameters -->
                    <?php if (!empty($sort_params['column'])): ?>
                        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_params['column']) ?>">
                        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sort_params['order']) ?>">
                    <?php endif; ?>
                    
                    <!-- Preserve search parameter -->
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="All" id="filterAll"
                            <?= $status_filter == 'All' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterAll">All Records</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="No Payment" id="filterNoPayment"
                            <?= $status_filter == 'No Payment' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterNoPayment">No Payment</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Completed" id="filterCompleted"
                            <?= $status_filter == 'Completed' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterCompleted">Completed</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Pending" id="filterPending"
                            <?= $status_filter == 'Pending' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterPending">Pending</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Failed" id="filterFailed"
                            <?= $status_filter == 'Failed' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterFailed">Failed</label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="#" class="add-member-btn" onclick="event.preventDefault(); openAddPaymentModal();">
        <i class="bi bi-plus-lg"></i> Record Payment
    </a>
</div>

<!-- Payments Table -->
<div class="members-table-container">
    <table class="members-table">
        <thead>
            <tr>
                <?php
                // Render sortable headers
                echo render_sortable_header('Payment Date', 'PaymentDate', $sort_params['column'], $sort_params['order'], 'payments.php');
                echo render_sortable_header('Member Name', 'MemberName', $sort_params['column'], $sort_params['order'], 'payments.php');
                ?>
                <th>Plan</th>
                <?php
                echo render_sortable_header('Payment Method', 'PaymentMethod', $sort_params['column'], $sort_params['order'], 'payments.php');
                echo render_sortable_header('Amount Paid', 'Amount', $sort_params['column'], $sort_params['order'], 'payments.php');
                ?>
                <th>Reference #</th>
                <th>Status</th>
                <th>Staff</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($payments) > 0): ?>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td>
                            <?php if ($p['PaymentDate']): ?>
                                <?= formatPaymentDate($p['PaymentDate'], 'short') ?>
                            <?php else: ?>
                                <span style="color: #94a3b8;">No payment yet</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['MemberName']) ?></td>
                        <td><?= htmlspecialchars($p['PlanName']) ?></td>
                        <td>
                            <?php if ($p['PaymentMethod']): ?>
                                <span class="payment-method-badge">
                                    <i class="bi bi-<?= getPaymentMethodIcon($p['PaymentMethod']) ?>"></i>
                                    <?= htmlspecialchars($p['PaymentMethod']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #94a3b8;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['AmountPaid']): ?>
                                <strong>₱<?= number_format($p['AmountPaid'], 2) ?></strong>
                            <?php else: ?>
                                <span style="color: #94a3b8;">₱<?= number_format($p['Rate'], 2) ?></span>
                                <small style="display: block; color: #94a3b8; font-size: 11px;">Expected</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['ReferenceNumber'] ?? 'N/A') ?></td>
                        <td>
                            <?php if ($p['PaymentStatus']): ?>
                                <span class="status-badge <?= getStatusBadgeClass($p['PaymentStatus']) ?>">
                                    <?= htmlspecialchars($p['PaymentStatus']) ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa;">
                                    No Payment
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['StaffName'] ?? 'N/A') ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($p['PaymentID']): ?>
                                    <button class="action-btn" onclick="viewPaymentDetails(<?= $p['PaymentID'] ?>)" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="action-btn" onclick="editPayment(<?= $p['PaymentID'] ?>)" title="Edit Payment">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="action-btn" onclick="openAddPaymentModalForMembership(<?= $p['MembershipID'] ?>)" title="Record Payment">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:40px; color:#718096;">
                        No records found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-container">
        <div class="pagination-info">
            Showing 1 to <?= count($payments) ?> of <?= count($payments) ?> results
        </div>
        <div class="pagination">
            <button class="pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
            <button class="pagination-btn active">1</button>
        </div>
    </div>
</div>

<script>
// Toggle filter dropdown
function toggleFilter(type) {
    document.getElementById('filterDropdown_' + type).classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const dropdowns = document.querySelectorAll('.filter-dropdown-content');
    
    filterBtns.forEach(btn => {
        if (!btn.contains(event.target)) {
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove('show');
                }
            });
        }
    });
});
</script>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/payment_add_modal.php'; ?>
<?php include 'includes/payment_edit_modal.php'; ?>
<?php include 'includes/payment_view_modal.php'; ?>