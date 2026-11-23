<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';
check_member_login($pdo);

$memberId = (int) $_SESSION['member_id'];
$page_title = 'My QR Code';
include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">My QR Code</h1>
    <p class="mp-subtitle mp-print-hide">Show this code at the front desk to check in and out.</p>
</div>

<div class="dashboard-card mp-qr-card">
    <h2 class="card-title"><?= member_h($_SESSION['member_name']) ?></h2>
    <div class="mp-qr-box"><div id="qr"></div></div>
    <div class="mp-qr-id">Member ID: GYM-<?= $memberId ?></div>
    <button type="button" class="btn-primary-modern mp-print-hide" style="margin-top:14px;" onclick="window.print()">
        <i class="bi bi-printer"></i> Print card
    </button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qr'), {
    text: 'GYM-<?= $memberId ?>',
    width: 256,
    height: 256,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});
</script>
<?php include __DIR__ . '/includes/member_footer.php'; ?>
