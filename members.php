<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

$action = $_GET['action'] ?? 'list';
$member_id = $_GET['id'] ?? null;

// Process POST save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    include 'controllers/member_save.php';
}
// Load data for add/edit
include 'controllers/member_fetch.php';

// Fetch list of members
if ($action === 'list') {
    $status_filter = $_GET['status_filter'] ?? 'All';
    $search = trim($_GET['search'] ?? '');
    $members = get_members($pdo, $status_filter, $search);
}

include 'includes/header.php';
?>
<?php if ($action === 'list'): ?>

<div class="page-header">
<h1 class="page-title">Members</h1>
</div>

<!-- Search, Filter, Add -->
<div class="page-actions">
<div class="search-filter-group">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search by..." id="searchInput" value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="filter-dropdown">
        <button class="filter-btn" onclick="toggleFilter()">
            <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down"></i>
        </button>

        <div class="filter-dropdown-content" id="filterDropdown">
            <form method="GET" action="members.php">
                <input type="hidden" name="action" value="list">

                <div class="filter-option">
                    <input type="radio" name="status_filter" value="All" id="filterAll"
                        <?= $status_filter == 'All' ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label for="filterAll">All Status</label>
                </div>

                <div class="filter-option">
                    <input type="radio" name="status_filter" value="Active" id="filterActive"
                        <?= $status_filter == 'Active' ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label for="filterActive">Active</label>
                </div>

                <div class="filter-option">
                    <input type="radio" name="status_filter" value="Inactive" id="filterInactive"
                        <?= $status_filter == 'Inactive' ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label for="filterInactive">Inactive</label>
                </div>

                <div class="filter-option">
                    <input type="radio" name="status_filter" value="Pending" id="filterPending"
                        <?= $status_filter == 'Pending' ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label for="filterPending">Pending</label>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CHANGED: Add Member button now opens modal instead of navigating -->
<a href="#" class="add-member-btn" onclick="event.preventDefault(); openAddModal();">
    <i class="bi bi-plus-lg"></i> Add Member
</a>
</div>

<!-- Members Table -->
<div class="members-table-container">
<table class="members-table">
    <thead>
        <tr>
            <th>Member ID</th>
            <th>Full Name</th>
            <th>Contact Number</th>
            <th>Status</th>
            <th>Join Date</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php if (count($members) > 0): ?>
            <?php foreach ($members as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['MemberID']) ?></td>

                    <td><?= htmlspecialchars($m['FirstName'] . ' ' . $m['LastName']) ?></td>

                    <td><?= htmlspecialchars($m['PhoneNo'] ?? 'N/A') ?></td>

                    <td>
                        <span class="status-badge <?= strtolower($m['MembershipStatus']) ?>">
                            <?= htmlspecialchars($m['MembershipStatus']) ?>
                        </span>
                    </td>

                    <td><?= isset($m['JoinDate']) ? date('m/d/y', strtotime($m['JoinDate'])) : 'N/A' ?></td>

                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-member-btn"
                                    onclick="viewMember(<?= $m['MemberID'] ?>)">
                                <i class="bi bi-eye"></i>
                            </button>

                            <button class="action-btn edit-member-btn" onclick="editMember(<?= $m['MemberID'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; padding:40px; color:#718096;">
                    No members found
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

        <!-- Pagination basic -->
        <div class="pagination-container">
            <div class="pagination-info">
                Showing 1 to <?= count($members) ?> of <?= count($members) ?> results
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

// Search on Enter
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        window.location.href =
            'members.php?action=list&search=' + encodeURIComponent(this.value);
    }
});
</script>
<?php else: ?>

<?php include 'includes/member_form.php'; ?>

<?php endif; ?>

<!-- CHANGED: Include all three modals -->
<?php include 'includes/footer.php'; ?>
<?php include 'includes/member_view_modal.php'; ?>
<?php include 'includes/member_edit_modal.php'; ?>
<?php include 'includes/member_add_modal.php'; ?>