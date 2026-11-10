<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';
require_once __DIR__ . '/../includes/member_data.php';
check_member_login($pdo);

$memberId = (int) $_SESSION['member_id'];
$counts = member_attendance_counts($pdo, $memberId);
$visits = member_get_attendance($pdo, $memberId, 100);

$page_title = 'Attendance';
include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">Attendance</h1>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Visits This Month</div><div class="stat-value"><?= $counts['this_month'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Last 7 Days</div><div class="stat-value"><?= $counts['last_7_days'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Total Visits</div><div class="stat-value"><?= $counts['total'] ?></div></div>
</div>

<div class="dashboard-card">
    <div class="card-header-flex">
        <h2 class="card-title">Visit History</h2>
        <a href="qr.php" class="see-all-link">Show my QR code</a>
    </div>
    <div class="mp-table-wrap">
        <table class="dashboard-table">
            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th></tr></thead>
            <tbody>
            <?php if ($visits): foreach ($visits as $v): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($v['CheckIn'])) ?></td>
                    <td><?= date('g:i A', strtotime($v['CheckIn'])) ?></td>
                    <td><?= $v['CheckOut'] ? date('g:i A', strtotime($v['CheckOut'])) : '—' ?></td>
                    <td><?= member_h(member_format_duration($v['CheckIn'], $v['CheckOut'])) ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="4" class="mp-empty">No visits recorded yet</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($counts['total'] > count($visits)): ?>
        <p class="mp-subtitle" style="margin-top:14px;">Showing your latest <?= count($visits) ?> visits.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/member_footer.php'; ?>
