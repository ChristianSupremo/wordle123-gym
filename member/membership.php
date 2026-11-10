<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';
require_once __DIR__ . '/../includes/member_data.php';
check_member_login($pdo);

$memberId = (int) $_SESSION['member_id'];
$current = member_get_current_membership($pdo, $memberId);
$history = member_get_membership_history($pdo, $memberId);

$page_title = 'My Membership';
include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">My Membership</h1>
</div>

<div class="mp-stack">
    <div class="dashboard-card">
        <div class="card-header-flex"><h2 class="card-title">Current Plan</h2></div>
        <?php if ($current): ?>
            <dl class="mp-info-list">
                <div><dt>Plan</dt><dd><?= member_h($current['PlanName'] ?? 'No Plan Assigned') ?></dd></div>
                <div><dt>Plan Type</dt><dd><?= member_h($current['PlanType'] ?: '—') ?></dd></div>
                <div><dt>Start Date</dt><dd><?= $current['StartDate'] ? date('M d, Y', strtotime($current['StartDate'])) : '—' ?></dd></div>
                <div><dt>End Date</dt><dd><?= $current['EndDate'] ? date('M d, Y', strtotime($current['EndDate'])) : '—' ?></dd></div>
                <div><dt>Time Remaining</dt><dd><?= member_days_left_html($current) ?></dd></div>
                <div><dt>Status</dt><dd><span class="status-badge <?= strtolower($current['EffectiveStatus']) ?>"><?= member_h($current['EffectiveStatus']) ?></span></dd></div>
            </dl>
            <p class="mp-subtitle" style="margin-top:18px;">To renew or change your plan, please visit the front desk.</p>
        <?php else: ?>
            <p class="mp-empty">No membership on record. Please visit the front desk to get started.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <div class="card-header-flex"><h2 class="card-title">Membership History</h2></div>
        <div class="mp-table-wrap">
            <table class="dashboard-table">
                <thead>
                    <tr><th>Plan</th><th>Start Date</th><th>End Date</th><th>Time Remaining</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php if ($history): foreach ($history as $h): ?>
                    <tr>
                        <td><?= $h['PlanName'] ? member_h($h['PlanName']) : '<span style="color:#718096;">No Plan Assigned</span>' ?></td>
                        <td><?= $h['StartDate'] ? date('m/d/y', strtotime($h['StartDate'])) : '—' ?></td>
                        <td><?= $h['EndDate'] ? date('m/d/y', strtotime($h['EndDate'])) : '—' ?></td>
                        <td><?= member_days_left_html($h) ?></td>
                        <td><span class="status-badge <?= strtolower($h['EffectiveStatus']) ?>"><?= member_h($h['EffectiveStatus']) ?></span></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="mp-empty">No memberships found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/member_footer.php'; ?>
