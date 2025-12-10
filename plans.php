<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/helpers/plan_modal_helpers.php';
check_login();

$action = $_GET['action'] ?? 'list';
$plan_id = $_GET['id'] ?? null;

// --- Handle Form Submissions (Add/Edit) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plan_id = $_POST['plan_id'] ?? null;
    $plan_name = trim($_POST['PlanName']);
    $description = trim($_POST['Description']);
    $plan_type = $_POST['PlanType'];
    $duration = (int)$_POST['Duration'];
    $rate = (float)$_POST['Rate'];
    $is_active = isset($_POST['IsActive']) ? 1 : 0;

    // Basic validation
    if ($plan_name && $description && $plan_type && $duration > 0 && $rate > 0) {
        if ($plan_id) {
            // Update existing plan
            $sql = "UPDATE Plan SET PlanName=?, Description=?, PlanType=?, Duration=?, Rate=?, IsActive=? WHERE PlanID=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$plan_name, $description, $plan_type, $duration, $rate, $is_active, $plan_id]);
            $_SESSION['message'] = "Plan updated successfully!";
        } else {
            // Add new plan
            $sql = "INSERT INTO Plan (PlanName, Description, PlanType, Duration, Rate, IsActive) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$plan_name, $description, $plan_type, $duration, $rate, $is_active]);
            $_SESSION['message'] = "New plan added successfully!";
        }
        $_SESSION['message_type'] = "success";
        header('Location: plans.php');
        exit();
    } else {
        $_SESSION['message'] = "Please fill in all required fields with valid values.";
        $_SESSION['message_type'] = "error";
        $redirect_url = $plan_id ? "plans.php?action=edit&id=$plan_id" : "plans.php?action=add";
        header("Location: $redirect_url");
        exit();
    }
}

// --- Handle Activate/Deactivate ---
if ($action === 'toggle_status' && $plan_id) {
    $sql = "UPDATE Plan SET IsActive = NOT IsActive WHERE PlanID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$plan_id]);
    
    $_SESSION['message'] = "Plan status updated successfully!";
    $_SESSION['message_type'] = "success";
    header('Location: plans.php');
    exit();
}

// Fetch plans for list view
$status_filter = $_GET['status_filter'] ?? 'All';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM Plan WHERE 1=1";
$params = [];

if ($status_filter !== 'All') {
    $sql .= " AND IsActive = ?";
    $params[] = $status_filter;
}

if ($search !== '') {
    $sql .= " AND (PlanName LIKE ? OR Description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY IsActive DESC, PlanName";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$plans = $stmt->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="assets/css/style/payment_modals.css">
<link rel="stylesheet" href="assets/css/style/plan_modals.css">

<div class="page-header">
    <h1 class="page-title">Plan Management</h1>
</div>

<!-- Search, Filter, Add -->
<div class="page-actions">
    <div class="search-filter-group">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search plans..." id="searchInput" value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="filter-dropdown">
            <button class="filter-btn" onclick="toggleFilter()">
                <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down"></i>
            </button>

            <div class="filter-dropdown-content" id="filterDropdown">
                <form method="GET" action="plans.php">
                    <input type="hidden" name="action" value="list">

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="All" id="filterAll"
                            <?= $status_filter == 'All' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterAll">All Plans</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="1" id="filterActive"
                            <?= $status_filter == '1' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterActive">Active</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="0" id="filterInactive"
                            <?= $status_filter == '0' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterInactive">Inactive</label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="#" class="add-member-btn" onclick="event.preventDefault(); openAddPlanModal();">
        <i class="bi bi-plus-lg"></i> Add New Plan
    </a>
</div>

<!-- Plans Table -->
<div class="members-table-container">
    <table class="members-table">
        <thead>
            <tr>
                <th>Plan Name</th>
                <th>Duration</th>
                <th>Rate</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($plans) > 0): ?>
                <?php foreach ($plans as $p): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($p['PlanName']) ?></strong>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                <?= htmlspecialchars($p['PlanType']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="plan-duration-text">
                                <?= htmlspecialchars($p['Duration']) ?>
                            </span>
                            <span class="plan-type-text">
                                <?= strtolower($p['PlanType']) ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color: #059669; font-size: 15px;">
                                ₱<?= number_format($p['Rate'], 2) ?>
                            </strong>
                        </td>
                        <td>
                            <div class="plan-description-text">
                                <?= htmlspecialchars($p['Description']) ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($p['IsActive']): ?>
                                <span class="status-badge completed">Active</span>
                            <?php else: ?>
                                <span class="status-badge failed">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn" onclick="viewPlan(<?= $p['PlanID'] ?>)" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="action-btn" onclick="editPlan(<?= $p['PlanID'] ?>)" title="Edit Plan">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="plans.php?action=toggle_status&id=<?= $p['PlanID'] ?>" 
                                   class="action-btn" 
                                   title="<?= $p['IsActive'] ? 'Deactivate' : 'Activate' ?>"
                                   onclick="return handleToggleStatus(event, <?= $p['IsActive'] ?>)">
                                    <i class="bi bi-<?= $p['IsActive'] ? 'toggle-off' : 'toggle-on' ?>"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:#718096;">
                        No plans found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-container">
        <div class="pagination-info">
            Showing 1 to <?= count($plans) ?> of <?= count($plans) ?> results
        </div>
        <div class="pagination">
            <button class="pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
            <button class="pagination-btn active">1</button>
        </div>
    </div>
</div>

<script>
// Toggle filter dropdown
function toggleFilter() {
    document.getElementById('filterDropdown').classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const filterBtn = document.querySelector('.filter-btn');
    const dropdown = document.getElementById('filterDropdown');
    if (!filterBtn.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Search functionality with debounce
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        window.location.href = 'plans.php?search=' + encodeURIComponent(this.value) + '&status_filter=<?= $status_filter ?>';
    }, 500);
});

// Handle toggle status with confirmation
async function handleToggleStatus(event, isActive) {
    event.preventDefault();
    
    const action = isActive ? 'deactivate' : 'activate';
    const confirmed = await confirm.show({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Plan`,
        message: `Are you sure you want to ${action} this plan?`,
        confirmText: action.charAt(0).toUpperCase() + action.slice(1),
        cancelText: 'Cancel',
        type: 'warning'
    });
    
    if (confirmed) {
        window.location.href = event.target.closest('a').href;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/plan_view_modal.php'; ?>
<?php include 'includes/plan_edit_modal.php'; ?>
<?php include 'includes/plan_add_modal.php'; ?>