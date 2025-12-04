<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

$action = $_GET['action'] ?? 'list';
$member_id = $_GET['id'] ?? null;

// Process POST (save member)
include 'controllers/member_save.php';

// Load data for edit / add
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

<h2>Member Management</h2>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="hidden" name="action" value="list">

            <label for="status_filter" class="mr-2">Show:</label>
            <select name="status_filter" id="status_filter"
                    class="form-control mr-2" onchange="this.form.submit()">
                <option value="All"      <?= $status_filter=='All'?'selected':'' ?>>All</option>
                <option value="Active"   <?= $status_filter=='Active'?'selected':'' ?>>Active</option>
                <option value="Inactive" <?= $status_filter=='Inactive'?'selected':'' ?>>Inactive</option>
                <option value="Pending"  <?= $status_filter=='Pending'?'selected':'' ?>>Pending</option>
            </select>

            <label for="search" class="mr-2">Search:</label>
            <input type="text" class="form-control mr-2"
                   name="search" placeholder="Name..."
                   value="<?= htmlspecialchars($search) ?>">

            <button class="btn btn-primary">Filter</button>
        </form>
    </div>

    <div class="col-md-4 text-right">
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
    <td><?= htmlspecialchars($m['FirstName'].' '.$m['LastName']) ?></td>
    <td><?= htmlspecialchars($m['Email']) ?></td>
    <td>
        <span class="badge 
            <?= $m['MembershipStatus']=='Active'?'bg-success':
                ($m['MembershipStatus']=='Inactive'?'bg-warning':'bg-secondary') ?>">
            <?= htmlspecialchars($m['MembershipStatus']) ?>
        </span>
    </td>
    <td>
        <button class="btn btn-sm btn-secondary view-member-btn"
                data-id="<?= $m['MemberID'] ?>"
                data-toggle="modal"
                data-target="#memberViewModal">
            <i class="bi bi-person-circle"></i>
        </button>

        <a href="members.php?action=edit&id=<?= $m['MemberID'] ?>"
           class="btn btn-sm btn-info">Edit</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php else: ?>

<?php include 'includes/member_form.php'; ?>

<?php endif; ?>

<?php include 'includes/member_view_modal.php'; ?>
<?php include 'includes/footer.php'; ?>
