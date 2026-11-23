<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';
require_once __DIR__ . '/../includes/member_data.php';
check_member_login($pdo);

$memberId = (int) $_SESSION['member_id'];
$membership = member_get_current_membership($pdo, $memberId);
$counts = member_attendance_counts($pdo, $memberId);
$total_paid = member_total_paid($pdo, $memberId);
$recent_visits = member_get_attendance($pdo, $memberId, 5);
$recent_payments = member_get_payments($pdo, $memberId, 4);

$status = $membership['EffectiveStatus'] ?? 'No Membership';
$days_left = $membership ? (int) $membership['DaysLeft'] : null;

$progress = 0;
if ($membership && (int) $membership['TotalDays'] > 0) {
    $progress = max(0, min(100, round(((int) $membership['ElapsedDays'] / (int) $membership['TotalDays']) * 100)));
}

$page_title = 'Dashboard';
include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="mp-subtitle">Welcome back, <?= member_h(explode(' ', trim($_SESSION['member_name']))[0]) ?>.</p>
</div>

<?php if ($membership && $status === 'Expired'): ?>
    <div class="mp-notice danger"><i class="bi bi-exclamation-triangle"></i>
        <span>Your membership ended on <?= date('M d, Y', strtotime($membership['EndDate'])) ?>. Please visit the front desk to renew.</span></div>
<?php elseif ($membership && $status === 'Active' && $days_left <= 7): ?>
    <div class="mp-notice"><i class="bi bi-bell"></i>
        <span>Your membership ends <?= $days_left === 0 ? 'today' : 'in ' . $days_left . ' day' . ($days_left !== 1 ? 's' : '') ?>. Visit the front desk to renew.</span></div>
<?php elseif (!$membership): ?>
    <div class="mp-notice"><i class="bi bi-info-circle"></i>
        <span>You don't have a membership on record yet. Please visit the front desk to get started.</span></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Membership Status</div>
        <div class="stat-value" style="font-size:22px;">
            <span class="status-badge <?= strtolower(str_replace(' ', '-', $status)) ?>"><?= member_h($status) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Days Left</div>
        <div class="stat-value"><?= ($membership && $status === 'Active') ? max(0, $days_left) : '—' ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Visits This Month</div>
        <div class="stat-value"><?= $counts['this_month'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value">₱<?= number_format($total_paid, 0) ?></div>
    </div>
</div>

<div class="content-grid">
    <div class="dashboard-card">
        <div class="card-header-flex">
            <h2 class="card-title">Current Membership</h2>
            <a href="membership.php" class="see-all-link">Details</a>
        </div>
        <?php if ($membership): ?>
            <p class="mp-plan-name"><?= member_h($membership['PlanName'] ?? 'No Plan Assigned') ?></p>
            <span class="status-badge <?= strtolower($status) ?>"><?= member_h($status) ?></span>
            <div class="mp-progress"><span style="width: <?= $progress ?>%;"></span></div>
            <div class="mp-progress-meta">
                <span><?= $membership['StartDate'] ? date('M d, Y', strtotime($membership['StartDate'])) : '—' ?></span>
                <span><?= $membership['EndDate'] ? date('M d, Y', strtotime($membership['EndDate'])) : '—' ?></span>
            </div>
        <?php else: ?>
            <p class="mp-empty">No membership on record</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <div class="card-header-flex">
            <h2 class="card-title">Recent Visits</h2>
            <a href="attendance.php" class="see-all-link">See all</a>
        </div>
        <table class="dashboard-table">
            <thead><tr><th>Date</th><th>In</th><th>Out</th></tr></thead>
            <tbody>
            <?php if ($recent_visits): foreach ($recent_visits as $v): ?>
                <tr>
                    <td><?= date('m/d/Y', strtotime($v['CheckIn'])) ?></td>
                    <td><?= date('g:i A', strtotime($v['CheckIn'])) ?></td>
                    <td><?= $v['CheckOut'] ? date('g:i A', strtotime($v['CheckOut'])) : '—' ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3" class="mp-empty">No visits yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="dashboard-card">
        <div class="card-header-flex">
            <h2 class="card-title">Recent Payments</h2>
            <a href="payments.php" class="see-all-link">See all</a>
        </div>
        <table class="dashboard-table">
            <thead><tr><th>Date</th><th>Plan</th><th>Amount</th></tr></thead>
            <tbody>
            <?php if ($recent_payments): foreach ($recent_payments as $p): ?>
                <tr>
                    <td><?= date('m/d/Y', strtotime($p['PaymentDate'])) ?></td>
                    <td><span class="plan-badge"><?= member_h($p['PlanName'] ?? '—') ?></span></td>
                    <td>₱<?= number_format((float) $p['AmountPaid'], 2) ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="3" class="mp-empty">No payments yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/member_footer.php'; ?>
