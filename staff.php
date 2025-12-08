<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/staff_modal_helpers.php';
check_login();

// Handle search and filter
$status_filter = $_GET['status_filter'] ?? 'All';
$search = trim($_GET['search'] ?? '');

// Get all staff with filtering
$staff_list = getAllStaff($pdo, $status_filter, $search);

// Get all roles for modals
$roles = getAllRoles($pdo);

include 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Staff Management</h1>
</div>

<!-- Search, Filter, Add -->
<div class="page-actions">
    <div class="search-filter-group">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search staff..." id="staffSearchInput" value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="filter-dropdown">
            <button class="filter-btn" onclick="toggleStaffFilter()">
                <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down"></i>
            </button>

            <div class="filter-dropdown-content" id="staffFilterDropdown">
                <form method="GET" action="staff.php">
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
                </form>
            </div>
        </div>
    </div>

    <a href="#" class="add-member-btn" onclick="event.preventDefault(); openAddStaffModal();">
        <i class="bi bi-plus-lg"></i> Add Staff
    </a>
</div>

<!-- Staff Table -->
<div class="members-table-container">
    <table class="members-table">
        <thead>
            <tr>
                <th>Staff ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Hire Date</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($staff_list) > 0): ?>
                <?php foreach ($staff_list as $staff): ?>
                    <tr>
                        <td><?= htmlspecialchars($staff['StaffID']) ?></td>

                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?= $staff['Photo'] ? 'assets/uploads/staff/' . $staff['Photo'] : 'assets/uploads/staff/admin.png' ?>" 
                                     alt="<?= htmlspecialchars($staff['FullName']) ?>"
                                     style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                <span><?= htmlspecialchars($staff['FullName']) ?></span>
                            </div>
                        </td>

                        <td><?= htmlspecialchars($staff['Email']) ?></td>

                        <td>
                            <span class="role-badge <?= getRoleBadgeClass($staff['AccessLevel']) ?>">
                                <?= htmlspecialchars($staff['RoleName']) ?>
                            </span>
                        </td>

                        <td>
                            <span class="status-badge <?= strtolower($staff['Status']) ?>">
                                <?= htmlspecialchars($staff['Status']) ?>
                            </span>
                        </td>

                        <td><?= date('M j, Y', strtotime($staff['HireDate'])) ?></td>

                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view-member-btn"
                                        onclick="viewStaff(<?= $staff['StaffID'] ?>)"
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <button class="action-btn edit-member-btn" 
                                        onclick="editStaff(<?= $staff['StaffID'] ?>)"
                                        title="Edit Staff">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <?php if ($staff['StaffID'] != $_SESSION['staff_id']): ?>
                                    <button class="action-btn delete-member-btn"
                                            onclick="toggleStaffStatus(<?= $staff['StaffID'] ?>, '<?= $staff['Status'] ?>', '<?= htmlspecialchars($staff['FullName']) ?>')"
                                            title="<?= $staff['Status'] == 'Active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="bi bi-<?= $staff['Status'] == 'Active' ? 'slash-circle' : 'check-circle' ?>"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:#718096;">
                        No staff members found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-container">
        <div class="pagination-info">
            Showing 1 to <?= count($staff_list) ?> of <?= count($staff_list) ?> results
        </div>
        <div class="pagination">
            <button class="pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
            <button class="pagination-btn active">1</button>
        </div>
    </div>
</div>

<script>
// Toggle filter dropdown
function toggleStaffFilter() {
    document.getElementById('staffFilterDropdown').classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const filterBtn = document.querySelector('.filter-btn');
    const dropdown = document.getElementById('staffFilterDropdown');
    if (filterBtn && dropdown && !filterBtn.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Search on Enter
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('staffSearchInput');
    if (!searchInput) return;

    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            window.location.href = 'staff.php?search=' + encodeURIComponent(this.value);
        }
    });
});

// Toggle staff status (activate/deactivate)
async function toggleStaffStatus(staffId, currentStatus, staffName) {
    const action = currentStatus === 'Active' ? 'deactivate' : 'activate';
    const actionText = action.charAt(0).toUpperCase() + action.slice(1);
    
    const confirmed = await confirm.show({
        title: `${actionText} Staff Member`,
        message: `Are you sure you want to ${action} ${staffName}?`,
        confirmText: actionText,
        cancelText: 'Cancel',
        type: currentStatus === 'Active' ? 'warning' : 'info'
    });
    
    if (!confirmed) return;
    
    try {
        const response = await fetch('api/toggle_staff_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ staff_id: staffId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            toast.success(data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            toast.error(data.message || 'Failed to update staff status');
        }
    } catch (error) {
        console.error('Error:', error);
        toast.error('An error occurred while updating staff status');
    }
}


</script>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/staff_view_modal.php'; ?>
<?php include 'includes/staff_add_modal.php'; ?>
<?php include 'includes/staff_edit_modal.php'; ?>