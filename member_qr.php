<?php
// member_qr.php?id=12 - printable QR card for a member
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('A valid member ID is required.');
}

$stmt = $pdo->prepare("SELECT MemberID, FirstName, LastName FROM member WHERE MemberID = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();
if (!$m) {
    http_response_code(404);
    exit('Member not found.');
}
?>
<?php include 'includes/header.php'; ?>
<div class="page-header"><h1 class="page-title">Member QR Card</h1></div>
<div class="dashboard-card" style="max-width:340px;text-align:center;">
  <h2 class="card-title"><?php echo htmlspecialchars($m['FirstName'] . ' ' . $m['LastName']); ?></h2>
  <div id="qr" style="
    display: inline-block;
    margin: 16px 0;
    padding: 12px;
    background: #ffffff;
"></div>
  <div style="color:#718096;">Member ID: GYM-<?php echo (int) $m['MemberID']; ?></div>
  <button onclick="window.print()" style="margin-top:14px;padding:8px 16px;border:0;border-radius:6px;background:#4fd1c5;color:#fff;cursor:pointer;">Print card</button>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qr'), {
    text: 'GYM-<?php echo (int) $m['MemberID']; ?>',
    width: 256,
    height: 256,
    colorDark: "#000000",   // Black QR blocks
    colorLight: "#ffffff",  // White background
    correctLevel: QRCode.CorrectLevel.H
});
</script>
<?php include 'includes/footer.php'; ?>
