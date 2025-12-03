<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

 $action = $_GET['action'] ?? 'list';
 $payment_id = $_GET['id'] ?? null;

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $membership_id = $_POST['MembershipID'];
    $payment_method_id = $_POST['PaymentMethodID'];
    $amount_paid = $_POST['AmountPaid'];
    $reference_number = $_POST['ReferenceNumber'];
    $remarks = $_POST['Remarks'];
    $staff_id = $_SESSION['staff_id'];

    $sql = "INSERT INTO Payment (MembershipID, PaymentMethodID, StaffID, AmountPaid, ReferenceNumber, Remarks) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt= $pdo->prepare($sql);
    $stmt->execute([$membership_id, $payment_method_id, $staff_id, $amount_paid, $reference_number, $remarks]);

    $_SESSION['message'] = "Payment recorded successfully!";
    $_SESSION['message_type'] = "success";
    header('Location: payments.php');
    exit();
}

// --- Handle Page Rendering ---
if ($action === 'add') {
    // Fetch active memberships for the dropdown
    $stmt = $pdo->query("
        SELECT m.MembershipID,
            CONCAT(
                mem.FirstName, ' ', mem.LastName,
                ' - ', p.PlanName, ' (', p.Duration, ')',
                ' - ₱', FORMAT(p.Rate, 2)
            ) AS Details
        FROM Membership m
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN Plan p ON m.PlanID = p.PlanID
        WHERE m.Status = 'Active'
        ORDER BY mem.LastName
    ");
    $active_memberships = $stmt->fetchAll();

    // Fetch payment methods for the dropdown
    $payment_methods = $pdo->query("SELECT PaymentMethodID, MethodName FROM PaymentMethods WHERE IsActive = 1")->fetchAll();
}

// If action is list, show the list of payments
if ($action === 'list') {
    $stmt = $pdo->query("
        SELECT p.PaymentID, p.PaymentDate, p.AmountPaid, p.ReferenceNumber, p.PaymentStatus,
               CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
               pm.MethodName
        FROM Payment p
        JOIN Membership m ON p.MembershipID = m.MembershipID
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN PaymentMethods pm ON p.PaymentMethodID = pm.PaymentMethodID
        ORDER BY p.PaymentDate DESC
    ");
    $payments = $stmt->fetchAll();
}

?>

<?php include 'includes/header.php'; ?>

<?php if ($action === 'list'): ?>
    <h2>Payment History</h2>
    <a href="payments.php?action=add" class="btn btn-primary mb-3">Record New Payment</a>
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Date</th>
                <th>Member</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Reference</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?php echo date('M j, Y g:i A', strtotime($p['PaymentDate'])); ?></td>
                <td><?php echo htmlspecialchars($p['MemberName']); ?></td>
                <td><?php echo htmlspecialchars($p['MethodName']); ?></td>
                <td>₱<?php echo number_format($p['AmountPaid'], 2); ?></td>
                <td><?php echo htmlspecialchars($p['ReferenceNumber']); ?></td>
                <td><span class="badge badge-<?php echo $p['PaymentStatus'] == 'Completed' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($p['PaymentStatus']); ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($action === 'add'): ?>
    <h2>Record New Payment</h2>
    <form action="payments.php" method="post">
        <div class="form-group">
            <label for="MembershipID">Membership</label>
            <select name="MembershipID" class="form-control" required>
                <option value="">Select Active Membership...</option>
                <?php foreach ($active_memberships as $membership): ?>
                    <option value="<?php echo $membership['MembershipID']; ?>">
                        <?php echo htmlspecialchars($membership['Details']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="PaymentMethodID">Payment Method</label>
                <select name="PaymentMethodID" class="form-control" required>
                    <?php foreach ($payment_methods as $method): ?>
                        <option value="<?php echo $method['PaymentMethodID']; ?>"><?php echo htmlspecialchars($method['MethodName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="AmountPaid">Amount Paid</label>
                <input type="number" step="0.01" class="form-control" name="AmountPaid" placeholder="0.00" required>
            </div>
        </div>
        <div class="form-group">
            <label for="ReferenceNumber">Reference Number (e.g., Cheque #, Transaction ID)</label>
            <input type="text" class="form-control" name="ReferenceNumber">
        </div>
        <div class="form-group">
            <label for="Remarks">Remarks</label>
            <textarea class="form-control" name="Remarks" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Record Payment</button>
        <a href="payments.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>