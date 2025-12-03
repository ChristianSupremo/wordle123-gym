<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

// Fetch dashboard stats
 $stmt = $pdo->query("SELECT COUNT(*) as total_members FROM Member WHERE MembershipStatus = 'Active'");
 $total_members = $stmt->fetch()['total_members'];

 $stmt = $pdo->query("SELECT COUNT(*) as active_memberships FROM Membership WHERE Status = 'Active'");
 $active_memberships = $stmt->fetch()['active_memberships'];

 $stmt = $pdo->query("SELECT COUNT(*) as total_staff FROM Staff WHERE Status = 'Active'");
 $total_staff = $stmt->fetch()['total_staff'];

// Monthly Revenue (Current Month)
 $stmt = $pdo->query("
    SELECT SUM(AmountPaid) AS monthly_revenue
    FROM Payment
    WHERE MONTH(PaymentDate) = MONTH(CURRENT_DATE())
      AND YEAR(PaymentDate) = YEAR(CURRENT_DATE())
      AND PaymentStatus = 'Completed'
");
$monthly_revenue = $stmt->fetch()['monthly_revenue'] ?? 0;

// Fetch recent payments
 $stmt = $pdo->query("
    SELECT p.PaymentDate, p.AmountPaid, m.FirstName, m.LastName
    FROM Payment p
    JOIN Membership mem ON p.MembershipID = mem.MembershipID
    JOIN Member m ON mem.MemberID = m.MemberID
    ORDER BY p.PaymentDate DESC
    LIMIT 5
");
 $recent_payments = $stmt->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Active Members</h5>
                <p class="card-text display-4"><?php echo $total_members; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Active Memberships</h5>
                <p class="card-text display-4"><?php echo $active_memberships; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Active Staff</h5>
                <p class="card-text display-4"><?php echo $total_staff; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Revenue (This Month)</h5>
                <p class="card-text display-4">₱<?php echo number_format($monthly_revenue, 2); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Recent Payments</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Member</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_payments as $payment): ?>
                <tr>
                    <td><?php echo date('M j, Y g:i A', strtotime($payment['PaymentDate'])); ?></td>
                    <td><?php echo htmlspecialchars($payment['FirstName'] . ' ' . $payment['LastName']); ?></td>
                    <td>₱<?php echo number_format($payment['AmountPaid'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>