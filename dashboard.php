<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

// Fetch dashboard stats
$stmt = $pdo->query("SELECT COUNT(*) as total_members FROM Member");
$total_members = $stmt->fetch()['total_members'];

// Active members - count members whose current/latest membership is Active
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT m.MemberID) as active_members
    FROM Member m
    INNER JOIN Membership ms ON m.MemberID = ms.MemberID
    WHERE ms.MembershipID = (
        SELECT MembershipID 
        FROM Membership 
        WHERE MemberID = m.MemberID 
        ORDER BY EndDate DESC 
        LIMIT 1
    )
    AND ms.Status = 'Active'
    AND ms.EndDate >= CURDATE()
");
$active_members = $stmt->fetch()['active_members'];

// Expired members - count members whose current/latest membership is Expired
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT m.MemberID) as expired_members
    FROM Member m
    INNER JOIN Membership ms ON m.MemberID = ms.MemberID
    WHERE ms.MembershipID = (
        SELECT MembershipID 
        FROM Membership 
        WHERE MemberID = m.MemberID 
        ORDER BY EndDate DESC 
        LIMIT 1
    )
    AND ms.Status = 'Expired'
    AND ms.EndDate < CURDATE()
");
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

// Members per plan (for pie chart)
$stmt = $pdo->query("
    SELECT 
        p.PlanName,
        COUNT(DISTINCT m.MemberID) as member_count
    FROM Member m
    INNER JOIN Membership ms ON m.MemberID = ms.MemberID
    INNER JOIN Plan p ON ms.PlanID = p.PlanID
    WHERE ms.MembershipID = (
        SELECT MembershipID 
        FROM Membership 
        WHERE MemberID = m.MemberID 
        ORDER BY EndDate DESC 
        LIMIT 1
    )
    AND ms.Status = 'Active'
    GROUP BY p.PlanID, p.PlanName
    ORDER BY member_count DESC
");
$members_per_plan = $stmt->fetchAll();

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

// Upcoming expirations: each member's CURRENT (latest) membership ending within the next 30 days.
// Wrapped in try/catch so a query problem shows a message in the card instead of breaking the page.
$upcoming_expirations = [];
$expirations_error = false;
try {
    $stmt = $pdo->query("
        SELECT m.MemberID, m.FirstName, m.LastName, mem.EndDate, p.PlanName,
               DATEDIFF(mem.EndDate, CURDATE()) AS days_left
        FROM member m
        JOIN membership mem ON mem.MemberID = m.MemberID
        LEFT JOIN plan p ON p.PlanID = mem.PlanID
        WHERE mem.MembershipID = (
                SELECT ms2.MembershipID FROM membership ms2
                WHERE ms2.MemberID = m.MemberID
                ORDER BY ms2.StartDate DESC, ms2.MembershipID DESC
                LIMIT 1
            )
          AND mem.Status = 'Active'
          AND mem.EndDate IS NOT NULL
          AND mem.EndDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY mem.EndDate ASC, m.LastName ASC
        LIMIT 5
    ");
    $upcoming_expirations = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('Dashboard expirations query failed: ' . $e->getMessage());
    $expirations_error = true;
}

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

    <!-- Members per Plan - Pie Chart -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <h2 class="card-title">Members per Plan</h2>
        </div>
        
        <div class="chart-container">
            <?php if (count($members_per_plan) > 0): ?>
                <canvas id="membersPerPlanChart"></canvas>
                
                <!-- Legend -->
                <div class="chart-legend" id="chartLegend">
                    <?php foreach ($members_per_plan as $index => $plan): ?>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: <?php 
                                $colors = ['#4fd1c5', '#63b3ed', '#f6ad55', '#fc8181', '#9f7aea', '#48bb78'];
                                echo $colors[$index % count($colors)];
                            ?>;"></span>
                            <span class="legend-label"><?php echo htmlspecialchars($plan['PlanName']); ?></span>
                            <span class="legend-value"><?php echo $plan['member_count']; ?> members</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 40px 0;">No active memberships</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Expirations -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <h2 class="card-title">Upcoming Expirations <small class="card-title-note">next 30 days</small></h2>
            <a href="memberships.php" class="see-all-link">See all</a>
        </div>
        
        <div class="expiration-list">
            <?php if ($expirations_error): ?>
                <p style="text-align: center; color: #e53e3e; padding: 20px 0;">We couldn't load expirations right now. Please refresh the page.</p>
            <?php elseif (count($upcoming_expirations) > 0): ?>
                <?php foreach ($upcoming_expirations as $expiring): ?>
                <?php
                    $exp_initials = strtoupper(mb_substr($expiring['FirstName'] ?? '', 0, 1) . mb_substr($expiring['LastName'] ?? '', 0, 1));
                    $d = (int) $expiring['days_left'];
                    $d_label = $d === 0 ? 'Today' : $d . ($d === 1 ? ' Day' : ' Days');
                ?>
                <div class="expiration-item">
                    <div class="member-avatar member-avatar-initials"><?php echo htmlspecialchars($exp_initials); ?></div>
                    <div class="member-info">
                        <div class="member-name"><?php echo htmlspecialchars($expiring['FirstName'] . ' ' . $expiring['LastName']); ?></div>
                        <div class="member-plan">
                            <?php echo htmlspecialchars($expiring['PlanName'] ?? 'No plan'); ?> &middot; ends <?php echo date('M j', strtotime($expiring['EndDate'])); ?>
                        </div>
                    </div>
                    <span class="days-badge <?php echo $d <= 7 ? '' : 'later'; ?>"><?php echo $d_label; ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 20px 0;">No memberships ending in the next 30 days</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Members per Plan Pie Chart
<?php if (count($members_per_plan) > 0): ?>
const ctx = document.getElementById('membersPerPlanChart');

const data = {
    labels: <?php echo json_encode(array_column($members_per_plan, 'PlanName')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($members_per_plan, 'member_count')); ?>,
        backgroundColor: [
            '#4fd1c5',
            '#63b3ed',
            '#f6ad55',
            '#fc8181',
            '#9f7aea',
            '#48bb78'
        ],
        borderColor: '#ffffff',
        borderWidth: 3,
        hoverOffset: 10
    }]
};

const config = {
    type: 'doughnut',
    data: data,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#2d3748',
                padding: 12,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} members (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '65%'
    }
};

new Chart(ctx, config);
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>