<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/gcash_submission_helpers.php';
check_login();

if (empty($_SESSION['staff_id'])) {
    http_response_code(403);
    exit('Staff access required.');
}
if (empty($_SESSION['gcash_staff_csrf'])) {
    $_SESSION['gcash_staff_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['gcash_staff_csrf'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionId = filter_var($_POST['submission_id'] ?? null, FILTER_VALIDATE_INT);
    $decision = $_POST['decision'] ?? '';
    $remarks = trim((string) ($_POST['remarks'] ?? ''));
    try {
        if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid security token. Refresh the page and try again.');
        }
        if (!$submissionId || !in_array($decision, ['approve', 'reject'], true)) {
            throw new RuntimeException('Invalid review request.');
        }
        if ($decision === 'reject' && $remarks === '') {
            throw new RuntimeException('Add a reason before rejecting a submission.');
        }
        if (mb_strlen($remarks) > 2000) {
            throw new RuntimeException('Remarks must be 2,000 characters or fewer.');
        }

        $pdo->beginTransaction();
        $lock = $pdo->prepare(
            "SELECT SubmissionID, MembershipID, AmountPaid, ReferenceNumber, Status
             FROM MemberGcashPaymentSubmissions WHERE SubmissionID = ? FOR UPDATE"
        );
        $lock->execute([$submissionId]);
        $submission = $lock->fetch();
        if (!$submission || $submission['Status'] !== 'Pending') {
            throw new RuntimeException('This submission has already been reviewed or no longer exists.');
        }

        $staffId = (int) $_SESSION['staff_id'];
        if ($decision === 'approve') {
            $methodStmt = $pdo->prepare("SELECT PaymentMethodID FROM paymentmethods WHERE MethodName = 'GCash' AND IsActive = 1 LIMIT 1");
            $methodStmt->execute();
            $methodId = $methodStmt->fetchColumn();
            if (!$methodId) {
                throw new RuntimeException('GCash is not an active payment method. Activate it in Payment Methods before approving.');
            }

            $paymentRemarks = 'Member-submitted GCash payment approved.';
            if ($remarks !== '') {
                $paymentRemarks .= ' Staff remarks: ' . $remarks;
            }
            $paymentInsert = $pdo->prepare(
                "INSERT INTO payment
                 (MembershipID, PaymentMethodID, StaffID, PaymentDate, AmountPaid, ReferenceNumber, PaymentStatus, Remarks)
                 VALUES (?, ?, ?, NOW(), ?, ?, 'Completed', ?)"
            );
            $paymentInsert->execute([
                $submission['MembershipID'],
                (int) $methodId,
                $staffId,
                $submission['AmountPaid'],
                $submission['ReferenceNumber'],
                $paymentRemarks
            ]);
            $paymentId = (int) $pdo->lastInsertId();

            $update = $pdo->prepare(
                "UPDATE MemberGcashPaymentSubmissions
                 SET Status = 'Approved', StaffRemarks = ?, ReviewedBy = ?, ReviewedAt = NOW(), PaymentID = ?
                 WHERE SubmissionID = ? AND Status = 'Pending'"
            );
            $update->execute([$remarks !== '' ? $remarks : 'Payment approved.', $staffId, $paymentId, $submissionId]);
        } else {
            $update = $pdo->prepare(
                "UPDATE MemberGcashPaymentSubmissions
                 SET Status = 'Rejected', StaffRemarks = ?, ReviewedBy = ?, ReviewedAt = NOW()
                 WHERE SubmissionID = ? AND Status = 'Pending'"
            );
            $update->execute([$remarks, $staffId, $submissionId]);
        }

        if ($update->rowCount() !== 1) {
            throw new RuntimeException('The submission was changed by another staff member. Refresh the queue.');
        }
        $pdo->commit();
        $_SESSION['message'] = htmlspecialchars($decision === 'approve' ? 'GCash submission approved and one completed payment was recorded.' : 'GCash submission rejected.', ENT_QUOTES, 'UTF-8');
        $_SESSION['message_type'] = 'success';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['message'] = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $_SESSION['message_type'] = 'error';
    }
    header('Location: gcash_submissions.php');
    exit;
}

$stmt = $pdo->query(
    "SELECT sub.SubmissionID, sub.MemberID, sub.MembershipID, sub.AmountPaid, sub.ReferenceNumber,
            sub.Status, sub.SubmittedAt, sub.ProofFileName,
            CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
            pl.PlanName
     FROM MemberGcashPaymentSubmissions sub
     INNER JOIN member mem ON mem.MemberID = sub.MemberID
     INNER JOIN membership ms ON ms.MembershipID = sub.MembershipID
     LEFT JOIN plan pl ON pl.PlanID = ms.PlanID
     WHERE sub.Status = 'Pending'
     ORDER BY sub.SubmittedAt ASC, sub.SubmissionID ASC"
);
$pending = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <h1 class="page-title">GCash Payment Review</h1>
</div>
<div class="dashboard-card">
    <div class="card-header-flex">
        <h2 class="card-title">Pending submissions</h2>
        <span class="status-badge pending"><?= count($pending) ?> pending</span>
    </div>
    <?php if ($pending): ?>
        <?php foreach ($pending as $item): ?>
            <section style="border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin:16px 0;">
                <div style="display:flex;flex-wrap:wrap;gap:18px;justify-content:space-between;">
                    <div>
                        <h3><?= htmlspecialchars($item['MemberName'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><strong>Membership:</strong> <?= htmlspecialchars($item['PlanName'] ?? 'Membership', ENT_QUOTES, 'UTF-8') ?> (#<?= (int) $item['MembershipID'] ?>)</p>
                        <p><strong>Member ID:</strong> <?= (int) $item['MemberID'] ?></p>
                        <p><strong>Amount:</strong> ₱<?= number_format((float) $item['AmountPaid'], 2) ?></p>
                        <p><strong>GCash reference:</strong> <?= htmlspecialchars($item['ReferenceNumber'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p><strong>Submitted:</strong> <?= htmlspecialchars(date('M d, Y g:i A', strtotime($item['SubmittedAt'])), ENT_QUOTES, 'UTF-8') ?></p>
                        <p><a href="gcash_proof.php?id=<?= (int) $item['SubmissionID'] ?>" target="_blank" rel="noopener">View proof image</a></p>
                    </div>
                    <form method="POST" action="gcash_submissions.php" style="min-width:260px;max-width:420px;flex:1;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="submission_id" value="<?= (int) $item['SubmissionID'] ?>">
                        <label class="form-label-modern" for="remarks_<?= (int) $item['SubmissionID'] ?>">Staff remarks / rejection reason</label>
                        <textarea class="form-control-modern" id="remarks_<?= (int) $item['SubmissionID'] ?>" name="remarks" rows="3" maxlength="2000" placeholder="Add a note for the member"></textarea>
                        <div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;">
                            <button class="btn-primary-modern" type="submit" name="decision" value="approve" onclick="return confirm('Approve this submission and create one completed GCash payment record?')">Approve payment</button>
                            <button class="btn-secondary-modern" type="submit" name="decision" value="reject" onclick="return confirm('Reject this submission? A reason is required.')">Reject</button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="padding:24px;color:#718096;">There are no pending GCash submissions.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
