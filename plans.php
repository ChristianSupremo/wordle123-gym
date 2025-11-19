<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
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
        // Redirect back to the form to show errors
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


// --- Handle Page Rendering ---
if ($action === 'edit' && $plan_id) {
    $stmt = $pdo->prepare("SELECT * FROM Plan WHERE PlanID = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    if (!$plan) {
        $_SESSION['message'] = "Plan not found.";
        $_SESSION['message_type'] = "error";
        header('Location: plans.php');
        exit();
    }
} elseif ($action === 'add') {
    // Set up empty plan object for the form
    $plan = [
        'PlanID' => null, 'PlanName' => '', 'Description' => '', 'PlanType' => 'Days',
        'Duration' => 1, 'Rate' => 0.00, 'IsActive' => 1
    ];
}

// If action is list or anything else, show the list of plans
if ($action === 'list') {
    // --- CHANGE: Add filtering logic ---
    $status_filter = $_GET['status_filter'] ?? '1'; // Default to showing Active plans (1)

    // Base query
    $sql = "SELECT * FROM Plan WHERE 1=1";
    
    // Add filter condition if not 'All'
    $params = [];
    if ($status_filter !== 'All') {
        $sql .= " AND IsActive = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY PlanName";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $plans = $stmt->fetchAll();
}

?>

<?php include 'includes/header.php'; ?>

<?php if ($action === 'list'): ?>
    <h2>Plan Management</h2>
    
    <!-- CHANGE: Add the filter form -->
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" class="form-inline">
                <input type="hidden" name="action" value="list">
                <label for="status_filter" class="mr-2">Show:</label>
                <select name="status_filter" id="status_filter" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="1" <?php echo ($status_filter == '1') ? 'selected' : ''; ?>>Active Plans</option>
                    <option value="0" <?php echo ($status_filter == '0') ? 'selected' : ''; ?>>Inactive Plans</option>
                    <option value="All" <?php echo ($status_filter == 'All') ? 'selected' : ''; ?>>All Plans</option>
                </select>
            </form>
        </div>
        <div class="col-md-6 text-right">
            <a href="plans.php?action=add" class="btn btn-primary">Add New Plan</a>
        </div>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Plan Name</th>
                <th>Description</th>
                <th>Type</th>
                <th>Duration</th>
                <th>Rate</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['PlanName']); ?></td>
                <td><?php echo htmlspecialchars($p['Description']); ?></td>
                <td><?php echo htmlspecialchars($p['PlanType']); ?></td>
                <td><?php echo htmlspecialchars($p['Duration']); ?> day(s)</td>
                <td>₱<?php echo number_format($p['Rate'], 2); ?></td>
                <td>
                    <span class="badge badge-<?php echo $p['IsActive'] ? 'success' : 'secondary'; ?>">
                        <?php echo $p['IsActive'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td>
                    <a href="plans.php?action=edit&id=<?php echo $p['PlanID']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="plans.php?action=toggle_status&id=<?php echo $p['PlanID']; ?>" class="btn btn-sm <?php echo $p['IsActive'] ? 'btn-warning' : 'btn-success'; ?>">
                        <?php echo $p['IsActive'] ? 'Deactivate' : 'Activate'; ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <h2><?php echo $plan['PlanID'] ? 'Edit Plan' : 'Add New Plan'; ?></h2>
    <form action="plans.php" method="post">
        <input type="hidden" name="plan_id" value="<?php echo $plan['PlanID']; ?>">
        <div class="form-group">
            <label for="PlanName">Plan Name</label>
            <input type="text" class="form-control" name="PlanName" value="<?php echo htmlspecialchars($plan['PlanName']); ?>" required>
        </div>
        <div class="form-group">
            <label for="Description">Description</label>
            <textarea class="form-control" name="Description" rows="3" required><?php echo htmlspecialchars($plan['Description']); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="PlanType">Plan Type</label>
                <select name="PlanType" class="form-control" required>
                    <option value="Days" <?php echo ($plan['PlanType'] == 'Days') ? 'selected' : ''; ?>>Days</option>
                    <option value="Months" <?php echo ($plan['PlanType'] == 'Months') ? 'selected' : ''; ?>>Months</option>
                    <option value="Years" <?php echo ($plan['PlanType'] == 'Years') ? 'selected' : ''; ?>>Years</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="Duration">Duration</label>
                <input type="number" class="form-control" name="Duration" value="<?php echo htmlspecialchars($plan['Duration']); ?>" min="1" required>
            </div>
            <div class="form-group col-md-4">
            <label for="Rate">Rate (₱)</label>
                <input type="number" step="0.01" class="form-control" name="Rate" value="<?php echo htmlspecialchars($plan['Rate']); ?>" min="0" required>
            </div>
        </div>
        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" <?php echo $plan['IsActive'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="IsActive">
                    Active
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-success"><?php echo $plan['PlanID'] ? 'Update Plan' : 'Add Plan'; ?></button>
        <a href="plans.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>