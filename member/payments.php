<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';
require_once __DIR__ . '/../includes/member_data.php';
check_member_login($pdo);

$memberId = (int) $_SESSION['member_id'];
$payments = member_get_payments($pdo, $memberId);
$total_paid = member_total_paid($pdo, $memberId);

$page_title = 'Payments';
include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">Payments</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value">₱<?= number_format($total_paid, 2) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Payments Made</div>
        <div class="stat-value"><?= count($payments) ?></div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header-flex"><h2 class="card-title">Payment History</h2></div>
    <div class="mp-table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr><th>Date</th><th>Plan</th><th>Method</th><th>Reference</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if ($payments): foreach ($payments as $p): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($p['PaymentDate'])) ?></td>
                    <td><span class="plan-badge"><?= member_h($p['PlanName'] ?? '—') ?></span></td>
                    <td><?= member_h($p['MethodName'] ?? '—') ?></td>
                    <td><?= member_h($p['ReferenceNumber'] ?: '—') ?></td>
                    <td>₱<?= number_format((float) $p['AmountPaid'], 2) ?></td>
                    <td><span class="status-badge <?= strtolower($p['PaymentStatus']) ?>"><?= member_h($p['PaymentStatus']) ?></span></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="mp-empty">No payments found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/member_footer.php'; ?>
