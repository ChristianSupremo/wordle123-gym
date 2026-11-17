<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/member_auth.php';
require_once __DIR__ . '/../includes/member_data.php';
require_once __DIR__ . '/../includes/gcash_submission_helpers.php';
check_member_login($pdo);

$memberId = (int) $_SESSION['member_id']; // Never accept MemberID from the request.
$page_title = 'Payments';
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storedProof = null;
    try {
        if (!verify_member_csrf($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('Your session token expired. Refresh the page and try again.');
        }

        $membershipId = filter_var($_POST['membership_id'] ?? null, FILTER_VALIDATE_INT);
        $referenceNumber = trim((string) ($_POST['reference_number'] ?? ''));
        $amountRaw = trim((string) ($_POST['amount_paid'] ?? ''));
        if (!$membershipId || $membershipId < 1) {
            throw new RuntimeException('Choose a valid membership.');
        }
        if ($referenceNumber === '' || mb_strlen($referenceNumber) > 100) {
            throw new RuntimeException('Enter a valid GCash reference number (up to 100 characters).');
        }
        if (!is_numeric($amountRaw) || (float) $amountRaw <= 0 || (float) $amountRaw > 9999999.99) {
            throw new RuntimeException('Enter an amount greater than ₱0.00.');
        }
        if (empty($_FILES['proof'])) {
            throw new RuntimeException('Upload your GCash proof of payment.');
        }

        $settings = gcash_get_payment_settings($pdo);
        if (empty($settings['RecipientName']) || empty($settings['MobileNumber'])) {
            throw new RuntimeException('GCash payment details are not configured yet. Please contact the gym.');
        }

        $storedProof = gcash_store_proof($_FILES['proof']);

        $pdo->beginTransaction();
        // Lock the member-owned membership row to serialize duplicate pending submissions.
        $membershipStmt = $pdo->prepare(
            "SELECT MembershipID FROM membership
             WHERE MembershipID = ? AND MemberID = ? AND Status IN ('Active','Pending','Expired')
             FOR UPDATE"
        );
        $membershipStmt->execute([$membershipId, $memberId]);
        if (!$membershipStmt->fetchColumn()) {
            throw new RuntimeException('That membership is not eligible for a payment submission.');
        }

        $pendingStmt = $pdo->prepare(
            "SELECT SubmissionID FROM MemberGcashPaymentSubmissions
             WHERE MembershipID = ? AND Status = 'Pending' LIMIT 1"
        );
        $pendingStmt->execute([$membershipId]);
        if ($pendingStmt->fetchColumn()) {
            throw new RuntimeException('There is already a pending GCash submission for this membership. Wait for staff review before submitting again.');
        }

        $insert = $pdo->prepare(
            "INSERT INTO MemberGcashPaymentSubmissions
             (MemberID, MembershipID, AmountPaid, ReferenceNumber, ProofFileName, Status, SubmittedAt)
             VALUES (?, ?, ?, ?, ?, 'Pending', NOW())"
        );
        $insert->execute([
            $memberId,
            $membershipId,
            number_format((float) $amountRaw, 2, '.', ''),
            $referenceNumber,
            $storedProof
        ]);
        $pdo->commit();
        $_SESSION['member_flash'] = ['type' => 'success', 'message' => 'Your GCash payment submission was received and is pending staff review. A reference number alone does not confirm payment.'];
        header('Location: payments.php');
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($storedProof) {
            gcash_delete_proof($storedProof);
        }
        $errorMessage = $e instanceof PDOException && (string) $e->getCode() === '23000'
            ? 'This GCash reference number has already been submitted. Check the number or contact the gym.'
            : $e->getMessage();
    }
}

$payments = member_get_payments($pdo, $memberId);
$total_paid = member_total_paid($pdo, $memberId);
$settings = gcash_get_payment_settings($pdo);
$settingsConfigured = !empty($settings['RecipientName']) && !empty($settings['MobileNumber']);

$eligibleStmt = $pdo->prepare(
    "SELECT ms.MembershipID, ms.Status, p.PlanName, p.Rate
     FROM membership ms
     LEFT JOIN plan p ON p.PlanID = ms.PlanID
     WHERE ms.MemberID = ? AND ms.Status IN ('Active','Pending','Expired')
     ORDER BY ms.StartDate DESC, ms.MembershipID DESC"
);
$eligibleStmt->execute([$memberId]);
$eligibleMemberships = $eligibleStmt->fetchAll();

$submissionStmt = $pdo->prepare(
    "SELECT sub.SubmissionID, sub.MembershipID, sub.AmountPaid, sub.ReferenceNumber,
            sub.Status, sub.StaffRemarks, sub.SubmittedAt, sub.ReviewedAt,
            p.PlanName, CONCAT(s.FirstName, ' ', s.LastName) AS ReviewerName
     FROM MemberGcashPaymentSubmissions sub
     INNER JOIN membership ms ON ms.MembershipID = sub.MembershipID
     LEFT JOIN plan p ON p.PlanID = ms.PlanID
     LEFT JOIN staff s ON s.StaffID = sub.ReviewedBy
     WHERE sub.MemberID = ?
     ORDER BY sub.SubmittedAt DESC, sub.SubmissionID DESC"
);
$submissionStmt->execute([$memberId]);
$submissions = $submissionStmt->fetchAll();

include __DIR__ . '/includes/member_header.php';
?>

<div class="page-header">
    <h1 class="page-title">Payments</h1>
</div>

<?php if ($errorMessage): ?>
    <div class="alert alert-error"><?= member_h($errorMessage) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value">₱<?= number_format($total_paid, 2) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Completed Payments</div>
        <div class="stat-value"><?= count($payments) ?></div>
    </div>
</div>

<div class="dashboard-card" style="margin-bottom:24px;">
    <div class="card-header-flex"><h2 class="card-title">Submit GCash Payment</h2></div>
    <p class="mp-subtitle">Pay the gym through GCash, then send your reference number and proof for manual staff review. Your submission stays pending until staff reviews it.</p>

    <?php if ($settingsConfigured): ?>
        <div class="mp-info-list" style="margin:16px 0;">
            <div><dt>Recipient</dt><dd><?= member_h($settings['RecipientName']) ?></dd></div>
            <div><dt>GCash number</dt><dd><?= member_h($settings['MobileNumber']) ?></dd></div>
            <div><dt>Instructions</dt><dd><?= nl2br(member_h($settings['Instructions'] ?? 'Send the exact amount, then upload a clear screenshot of your receipt.')) ?></dd></div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">GCash recipient details have not been configured. Please contact the gym before sending money.</div>
    <?php endif; ?>

    <?php if ($eligibleMemberships && $settingsConfigured): ?>
        <form method="POST" action="payments.php" enctype="multipart/form-data" class="mp-stack" style="max-width:680px;">
            <input type="hidden" name="csrf_token" value="<?= member_h(member_csrf_token()) ?>">
            <div class="form-group-modern">
                <label class="form-label-modern" for="membership_id">Membership / plan</label>
                <select class="form-control-modern" id="membership_id" name="membership_id" required>
                    <option value="">Choose your membership</option>
                    <?php foreach ($eligibleMemberships as $membership): ?>
                        <option value="<?= (int) $membership['MembershipID'] ?>">
                            <?= member_h(($membership['PlanName'] ?? 'Membership') . ' · ' . $membership['Status'] . ' · ₱' . number_format((float) $membership['Rate'], 2)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group-modern">
                <label class="form-label-modern" for="amount_paid">Amount paid (₱)</label>
                <input class="form-control-modern" id="amount_paid" name="amount_paid" type="number" min="0.01" max="9999999.99" step="0.01" required>
            </div>
            <div class="form-group-modern">
                <label class="form-label-modern" for="reference_number">GCash reference number</label>
                <input class="form-control-modern" id="reference_number" name="reference_number" type="text" maxlength="100" required>
            </div>
            <div class="form-group-modern">
                <label class="form-label-modern" for="proof">Proof of payment (JPG, PNG, or WebP; max 5 MB)</label>
                <input class="form-control-modern" id="proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <p class="mp-subtitle">Submitting a reference number or screenshot does not automatically verify payment. Do not submit again while the same membership has a pending review.</p>
            <button class="btn-primary-modern" type="submit"><i class="bi bi-upload"></i> Submit for Review</button>
        </form>
    <?php elseif (!$eligibleMemberships): ?>
        <p class="mp-empty">There are no eligible memberships available for payment submission. Please contact the front desk.</p>
    <?php endif; ?>
</div>

<div class="dashboard-card" style="margin-bottom:24px;">
    <div class="card-header-flex"><h2 class="card-title">GCash Submission Status</h2></div>
    <div class="mp-table-wrap">
        <table class="dashboard-table">
            <thead><tr><th>Submitted</th><th>Membership</th><th>Reference</th><th>Amount</th><th>Status</th><th>Review details</th></tr></thead>
            <tbody>
            <?php if ($submissions): foreach ($submissions as $submission): ?>
                <tr>
                    <td><?= member_h(date('M d, Y g:i A', strtotime($submission['SubmittedAt']))) ?></td>
                    <td><?= member_h($submission['PlanName'] ?? 'Membership') ?></td>
                    <td><?= member_h($submission['ReferenceNumber']) ?></td>
                    <td>₱<?= number_format((float) $submission['AmountPaid'], 2) ?></td>
                    <td><span class="status-badge <?= strtolower($submission['Status']) ?>"><?= $submission['Status'] === 'Approved' ? 'Approved / Completed' : member_h($submission['Status']) ?></span></td>
                    <td>
                        <?php if ($submission['ReviewedAt']): ?>
                            <small>Reviewed <?= member_h(date('M d, Y g:i A', strtotime($submission['ReviewedAt']))) ?><?= $submission['ReviewerName'] ? ' by ' . member_h($submission['ReviewerName']) : '' ?></small>
                        <?php else: ?>
                            <small>Awaiting staff review</small>
                        <?php endif; ?>
                        <?php if (!empty($submission['StaffRemarks'])): ?>
                            <div><?= nl2br(member_h($submission['StaffRemarks'])) ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="mp-empty">No GCash submissions yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header-flex"><h2 class="card-title">Payment History</h2></div>
    <div class="mp-table-wrap">
        <table class="dashboard-table">
            <thead><tr><th>Date</th><th>Plan</th><th>Method</th><th>Reference</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($payments): foreach ($payments as $p): ?>
                <tr>
                    <td><?= member_h(date('M d, Y', strtotime($p['PaymentDate']))) ?></td>
                    <td><span class="plan-badge"><?= member_h($p['PlanName'] ?? '—') ?></span></td>
                    <td><?= member_h($p['MethodName'] ?? '—') ?></td>
                    <td><?= member_h($p['ReferenceNumber'] ?: '—') ?></td>
                    <td>₱<?= number_format((float) $p['AmountPaid'], 2) ?></td>
                    <td><span class="status-badge <?= strtolower($p['PaymentStatus']) ?>"><?= member_h($p['PaymentStatus']) ?></span></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="6" class="mp-empty">No completed payment records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/member_footer.php'; ?>
