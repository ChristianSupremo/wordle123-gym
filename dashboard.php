<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

// Fetch dashboard stats
$stmt = $pdo->query("SELECT COUNT(*) as total_members FROM Member");
$total_members = $stmt->fetch()['total_members'];

$stmt = $pdo->query("SELECT COUNT(*) as active_members FROM Member WHERE MembershipStatus = 'Active'");
$active_members = $stmt->fetch()['active_members'];

$stmt = $pdo->query("SELECT COUNT(*) as expired_members FROM Member WHERE MembershipStatus = 'Expired'");
$expired_members = $stmt->fetch()['expired_members'];

// Monthly Revenue (Current Month)
$stmt = $pdo->query("
    SELECT SUM(AmountPaid) AS monthly_revenue
    FROM Payment
    WHERE MONTH(PaymentDate) = MONTH(CURRENT_DATE())
      AND YEAR(PaymentDate) = YEAR(CURRENT_DATE())
      AND PaymentStatus = 'Completed'
");
$total_revenue = $stmt->fetch()['monthly_revenue'] ?? 0;

// New members this month
$stmt = $pdo->query("
    SELECT m.FirstName, m.LastName, mem.StartDate, p.PlanName
    FROM Member m
    JOIN Membership mem ON m.MemberID = mem.MemberID
    JOIN Plan p ON mem.PlanID = p.PlanID
    WHERE MONTH(mem.StartDate) = MONTH(CURRENT_DATE())
      AND YEAR(mem.StartDate) = YEAR(CURRENT_DATE())
    ORDER BY mem.StartDate DESC
    LIMIT 4
");
$new_members = $stmt->fetchAll();

// Upcoming expirations
$stmt = $pdo->query("
    SELECT m.FirstName, m.LastName, mem.EndDate, p.PlanName,
           DATEDIFF(mem.EndDate, CURRENT_DATE()) as days_left
    FROM Member m
    JOIN Membership mem ON m.MemberID = mem.MemberID
    JOIN Plan p ON mem.PlanID = p.PlanID
    WHERE mem.Status = 'Active'
      AND DATEDIFF(mem.EndDate, CURRENT_DATE()) <= 7
      AND DATEDIFF(mem.EndDate, CURRENT_DATE()) >= 0
    ORDER BY mem.EndDate ASC
    LIMIT 4
");
$upcoming_expirations = $stmt->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Members</div>
        <div class="stat-value">
            <?php echo $total_members; ?>
            <span class="stat-change">
                <i class="bi bi-arrow-up"></i> 2.5%
            </span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Active Members</div>
        <div class="stat-value"><?php echo $active_members; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Expired Members</div>
        <div class="stat-value"><?php echo $expired_members; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">₱<?php echo number_format($total_revenue, 0); ?></div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <!-- New Members This Month -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <h2 class="card-title">New Members This Month</h2>
            <a href="members.php" class="see-all-link">See all</a>
        </div>
        
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Plan</th>
                    <th>Joined Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($new_members) > 0): ?>
                    <?php foreach ($new_members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['FirstName'] . ' ' . $member['LastName']); ?></td>
                        <td><span class="plan-badge"><?php echo htmlspecialchars($member['PlanName']); ?></span></td>
                        <td><?php echo date('m/d/Y', strtotime($member['StartDate'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #718096;">No new members this month</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Upcoming Expirations -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <h2 class="card-title">Upcoming Expirations</h2>
            <a href="memberships.php" class="see-all-link">See all</a>
        </div>
        
        <div class="expiration-list">
            <?php if (count($upcoming_expirations) > 0): ?>
                <?php foreach ($upcoming_expirations as $expiring): ?>
                <div class="expiration-item">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($expiring['FirstName'] . ' ' . $expiring['LastName']); ?>&background=4fd1c5&color=fff" 
                         alt="Avatar" class="member-avatar">
                    <div class="member-info">
                        <div class="member-name"><?php echo htmlspecialchars($expiring['FirstName'] . ' ' . $expiring['LastName']); ?></div>
                        <div class="member-plan">Plan: <?php echo htmlspecialchars($expiring['PlanName']); ?></div>
                    </div>
                    <span class="days-badge"><?php echo $expiring['days_left']; ?> Days</span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 20px 0;">No upcoming expirations</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>