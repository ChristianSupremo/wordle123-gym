<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['payment_review_csrf'] ?? '', $_POST['csrf_token'] ?? '')) {
        $_SESSION['message'] = 'Invalid request. Please try again.';
        $_SESSION['message_type'] = 'error';
        header('Location: payment_review.php');
        exit;
    }
    $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
    $decision = $_POST['decision'] ?? '';
    if ($paymentId && in_array($decision, ['approve', 'reject'], true)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT p.MembershipID, p.PaymentStatus FROM payment p WHERE p.PaymentID = ? FOR UPDATE");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch();
            if (!$payment || $payment['PaymentStatus'] !== 'Pending') {
                throw new RuntimeException('This payment is no longer pending.');
            }
            if ($decision === 'approve') {
                $update = $pdo->prepare("UPDATE payment SET PaymentStatus = 'Completed', StaffID = ?, Remarks = 'Online QR payment approved by admin' WHERE PaymentID = ? AND PaymentStatus = 'Pending'");
                $update->execute([$_SESSION['staff_id'], $paymentId]);
                $membership = $pdo->prepare("UPDATE membership SET Status = 'Active', StaffID = ? WHERE MembershipID = ?");
                $membership->execute([$_SESSION['staff_id'], $payment['MembershipID']]);
                $member = $pdo->prepare("UPDATE member SET MembershipStatus = 'Active' WHERE MemberID = (SELECT MemberID FROM membership WHERE MembershipID = ?)");
                $member->execute([$payment['MembershipID']]);
                $_SESSION['message'] = 'Payment approved. Membership is now active.';
                $_SESSION['message_type'] = 'success';
            } else {
                $update = $pdo->prepare("UPDATE payment SET PaymentStatus = 'Failed', StaffID = ?, Remarks = 'Online QR payment rejected by admin' WHERE PaymentID = ? AND PaymentStatus = 'Pending'");
                $update->execute([$_SESSION['staff_id'], $paymentId]);
                $membership = $pdo->prepare("UPDATE membership SET Status = 'Cancelled', StaffID = ? WHERE MembershipID = ?");
                $membership->execute([$_SESSION['staff_id'], $payment['MembershipID']]);
                $_SESSION['message'] = 'Payment rejected. The pending membership was cancelled.';
                $_SESSION['message_type'] = 'info';
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $_SESSION['message'] = $e instanceof RuntimeException ? $e->getMessage() : 'Could not update this payment.';
            $_SESSION['message_type'] = 'error';
        }
    }
    header('Location: payment_review.php');
    exit;
}

if (empty($_SESSION['payment_review_csrf'])) {
    $_SESSION['payment_review_csrf'] = bin2hex(random_bytes(32));
}
$stmt = $pdo->query("SELECT p.PaymentID, p.AmountPaid, p.PaymentDate, p.ReferenceNumber, p.ReceiptImage, m.MembershipID, mem.MemberID, mem.FirstName, mem.LastName, mem.PhoneNo, pl.PlanName FROM payment p JOIN membership m ON p.MembershipID = m.MembershipID JOIN member mem ON m.MemberID = mem.MemberID JOIN plan pl ON m.PlanID = pl.PlanID WHERE p.PaymentStatus = 'Pending' ORDER BY p.PaymentDate ASC");
$pending = $stmt->fetchAll();
include 'includes/header.php';
?>
<div class="page-header"><h1 class="page-title">Payment Approval</h1></div>
<p style="color:#64748b;margin-bottom:20px">Review the uploaded receipt before approving. Only approved payments activate the membership.</p>
<div class="members-table-container">
<table class="members-table">
<thead><tr><th>Member</th><th>Plan</th><th>Amount</th><th>Submitted</th><th>Receipt</th><th>Action</th></tr></thead>
<tbody>
<?php if ($pending): foreach ($pending as $p): ?>
<tr>
<td><?= h($p['FirstName'] . ' ' . $p['LastName']) ?><small style="display:block;color:#64748b">ID #<?= (int)$p['MemberID'] ?> · <?= h($p['PhoneNo']) ?></small></td>
<td><?= h($p['PlanName']) ?></td>
<td><strong>₱<?= number_format((float)$p['AmountPaid'], 2) ?></strong></td>
<td><?= h($p['PaymentDate']) ?></td>
<td><?php if ($p['ReceiptImage']): ?><a href="<?= h($p['ReceiptImage']) ?>" target="_blank" rel="noopener">View receipt</a><?php else: ?>No receipt<?php endif; ?></td>
<td>
<form method="post" style="display:flex;gap:6px;flex-wrap:wrap">
<input type="hidden" name="csrf_token" value="<?= h($_SESSION['payment_review_csrf']) ?>">
<input type="hidden" name="payment_id" value="<?= (int)$p['PaymentID'] ?>">
<button class="action-btn" name="decision" value="approve" onclick="return confirm('Approve this payment and activate the membership?')">Approve</button>
<button class="action-btn" name="decision" value="reject" onclick="return confirm('Reject this receipt?')">Reject</button>
</form>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:35px;color:#64748b">No payments are waiting for approval.</td></tr>
<?php endif; ?>
</tbody></table></div>
<?php include 'includes/footer.php'; ?>
