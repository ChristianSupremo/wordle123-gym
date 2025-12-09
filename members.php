<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/helpers/table_sort_helper.php';
check_login();

$action = $_GET['action'] ?? 'list';
$member_id = $_GET['id'] ?? null;

// Process POST save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    include 'controllers/member_save.php';
}

// Load data for add/edit
include 'controllers/member_fetch.php';

// Fetch members (list view)
if ($action === 'list') {
    $status_filter = $_GET['status_filter'] ?? 'All';
    $search = trim($_GET['search'] ?? '');
    
    // Get sorting parameters
    $sort_config = get_members_sort_config();
    $sort_params = get_sort_params($sort_config);
    
    $members = get_members($pdo, $status_filter, $search, $sort_params);
}

include 'includes/header.php';
?>

<?php if ($action === 'list'): ?>

<div class="page-header">
    <h1 class="page-title">
        Members
        <?php 
        // Display sort indicator
        if (!empty($sort_params['original'])) {
            echo get_sort_indicator(
                $sort_params['original'], 
                $sort_params['order'], 
                $sort_config['column_labels']
            );
        }
        ?>
    </h1>
    <?php echo render_clear_sort_button(); ?>
</div>

<!-- SEARCH + FILTER + ADD BUTTON -->
<div class="page-actions">

    <div class="search-filter-group">

        <!-- GLOBAL SEARCH -->
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
                type="text" 
                placeholder="Search member..." 
                id="globalSearchInput"
                data-page="members.php"
                data-param="search"
                value="<?= htmlspecialchars($search) ?>"
            >
        </div>

        <!-- GLOBAL FILTER DROPDOWN -->
        <div class="filter-dropdown">
            <button class="filter-btn" onclick="toggleFilter('members')">
                <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down"></i>
            </button>

            <div class="filter-dropdown-content" id="filterDropdown_members">
                <form method="GET" action="members.php">
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
                            <?= $status_filter == 'All' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="filterAll">All Status</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Active" id="filterActive"
                            <?= $status_filter == 'Active' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="filterActive">Active</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Inactive" id="filterInactive"
                            <?= $status_filter == 'Inactive' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="filterInactive">Inactive</label>
                    </div>

                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Pending" id="filterPending"
                            <?= $status_filter == 'Pending' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="filterPending">Pending</label>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- ADD MEMBER BUTTON -->
    <a href="#" class="add-member-btn" onclick="event.preventDefault(); openAddModal();">
        <i class="bi bi-plus-lg"></i> Add Member
    </a>

</div> <!-- /page-actions -->

<!-- MEMBERS TABLE -->
<div class="members-table-container">

    <table class="members-table">
        <thead>
            <tr>
                <?php
                // Render sortable headers
                echo render_sortable_header('Member ID', 'MemberID', $sort_params['original'], $sort_params['order'], 'members.php');
                echo render_sortable_header('Full Name', 'FullName', $sort_params['original'], $sort_params['order'], 'members.php');
                echo render_sortable_header('Contact Number', 'PhoneNo', $sort_params['original'], $sort_params['order'], 'members.php');       
                ?>
                <th>Status</th>
                <?php
                echo render_sortable_header('Join Date', 'JoinDate', $sort_params['column'], $sort_params['order'], 'members.php');
                ?>
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

                    <td><?= $m['JoinDate'] ? date('m/d/y', strtotime($m['JoinDate'])) : 'N/A' ?></td>

                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-member-btn"
                                    onclick="viewMember(<?= $m['MemberID'] ?>)">
                                <i class="bi bi-eye"></i>
                            </button>

                            <button class="action-btn edit-member-btn"
                                    onclick="editMember(<?= $m['MemberID'] ?>)">
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

    <!-- Pagination Placeholder -->
    <div class="pagination-container">
        <div class="pagination-info">
            Showing <?= count($members) ?> results
        </div>
        <div class="pagination">
            <button class="pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
            <button class="pagination-btn active">1</button>
        </div>
    </div>

</div> <!-- /members-table-container -->

<?php else: ?>

<?php include 'includes/member_form.php'; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/member_view_modal.php'; ?>
<?php include 'includes/member_edit_modal.php'; ?>
<?php include 'includes/member_add_modal.php'; ?>