<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/gcash_submission_helpers.php';
require_permission('payments.view');

if (empty($_SESSION['staff_id'])) {
    http_response_code(403);
    exit('Staff access required.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('payments.create');
    $submissionId = filter_var($_POST['submission_id'] ?? null, FILTER_VALIDATE_INT);
    $decision = $_POST['decision'] ?? '';
    $remarks = trim((string) ($_POST['remarks'] ?? ''));
    try {
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
$pendingTotal = array_sum(array_map(static fn($row) => (float) $row['AmountPaid'], $pending));

include __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <div>
        <h1 class="page-title">GCash Payment Review</h1>
        <p class="gcash-subtitle">Check each member's GCash proof, then approve it to record the payment or reject it with a reason.</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card gcash-stat">
        <span class="gcash-stat-icon" aria-hidden="true"><i class="bi bi-hourglass-split"></i></span>
        <div>
            <div class="stat-label">Pending submissions</div>
            <div class="stat-value"><?= count($pending) ?></div>
        </div>
    </div>
    <div class="stat-card gcash-stat">
        <span class="gcash-stat-icon" aria-hidden="true"><i class="bi bi-cash-stack"></i></span>
        <div>
            <div class="stat-label">Total amount awaiting review</div>
            <div class="stat-value">₱<?= number_format($pendingTotal, 2) ?></div>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header-flex">
        <h2 class="card-title">Pending submissions <span class="card-title-note">Oldest first</span></h2>
        <span class="status-badge pending"><?= count($pending) ?> pending</span>
    </div>
    <?php if ($pending): ?>
        <div class="gcash-list">
        <?php foreach ($pending as $item):
            $sid = (int) $item['SubmissionID'];
            $initials = strtoupper(implode('', array_map(static fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($item['MemberName'])), 0, 2))));
        ?>
            <article class="gcash-item">
                <header class="gcash-item-head">
                    <div class="member-avatar member-avatar-initials" aria-hidden="true"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="gcash-item-who">
                        <h3><?= htmlspecialchars($item['MemberName'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <span>Member #<?= (int) $item['MemberID'] ?> · Submitted <?= htmlspecialchars(date('M d, Y g:i A', strtotime($item['SubmittedAt'])), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <span class="payment-method-badge"><i class="bi bi-phone"></i> GCash</span>
                </header>

                <div class="gcash-item-body">
                    <dl class="gcash-details">
                        <div><dt>Amount</dt><dd class="gcash-amount">₱<?= number_format((float) $item['AmountPaid'], 2) ?></dd></div>
                        <div><dt>Reference #</dt><dd class="gcash-ref"><?= htmlspecialchars($item['ReferenceNumber'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt>Membership</dt><dd><?= htmlspecialchars($item['PlanName'] ?? 'Membership', ENT_QUOTES, 'UTF-8') ?> <span class="gcash-muted">#<?= (int) $item['MembershipID'] ?></span></dd></div>
                        <div><dt>Proof of payment</dt><dd><a class="gcash-proof-link" href="gcash_proof.php?id=<?= $sid ?>" target="_blank" rel="noopener"><i class="bi bi-image"></i> View proof image <i class="bi bi-box-arrow-up-right"></i></a></dd></div>
                    </dl>

                    <form class="gcash-review" method="POST" action="gcash_submissions.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="submission_id" value="<?= $sid ?>">
                        <label class="form-label-modern" for="remarks_<?= $sid ?>">Staff remarks <span class="gcash-muted">(required to reject)</span></label>
                        <textarea class="form-control-modern" id="remarks_<?= $sid ?>" name="remarks" rows="3" maxlength="2000" placeholder="Add a note for the member"></textarea>
                        <div class="gcash-actions">
                            <button class="btn-secondary-modern gcash-reject" type="submit" name="decision" value="reject"><i class="bi bi-x-circle"></i> Reject</button>
                            <button class="btn-primary-modern" type="submit" name="decision" value="approve"><i class="bi bi-check-circle"></i> Approve payment</button>
                        </div>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="gcash-empty">
            <i class="bi bi-check2-circle" aria-hidden="true"></i>
            <p><strong>All caught up</strong></p>
            <p>There are no pending GCash submissions.</p>
        </div>
    <?php endif; ?>
</div>
<script>
// Approve/Reject confirmation. script.js declares a global `const confirm` (ConfirmDialog) that shadows window.confirm,
// so inline `return confirm(...)` threw and the form submitted without asking.
document.querySelectorAll('form.gcash-review').forEach(function (form) {
    form.addEventListener('submit', async function (e) {
        e.preventDefault(); // nothing is sent until the user confirms
        const button = e.submitter;
        if (!button || form.dataset.submitting === '1') return;

        const reject = button.value === 'reject';
        const remarks = form.querySelector('textarea[name="remarks"]');
        if (reject && remarks.value.trim() === '') {
            toast.error('Add a reason before rejecting a submission.');
            remarks.focus();
            return;
        }

        const ok = await confirm.show({
            title: reject ? 'Reject GCash Submission' : 'Approve GCash Payment',
            message: reject
                ? 'Reject this submission? The member will see your remarks.'
                : 'Approve this submission and create one completed GCash payment record?',
            confirmText: reject ? 'Reject' : 'Approve',
            cancelText: 'Cancel',
            type: reject ? 'danger' : 'info'
        });
        if (!ok || form.dataset.submitting === '1') return;

        // form.submit() skips the clicked button's name/value, so carry the decision in a hidden field.
        form.dataset.submitting = '1';
        let decision = form.querySelector('input[type="hidden"][name="decision"]');
        if (!decision) {
            decision = document.createElement('input');
            decision.type = 'hidden';
            decision.name = 'decision';
            form.appendChild(decision);
        }
        decision.value = button.value;
        form.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = true; });
        form.submit(); // csrf_token is already a field in this form
    });
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
