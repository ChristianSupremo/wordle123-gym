<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['online_payment_csrf'])) {
    $_SESSION['online_payment_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['online_payment_csrf'];
$error = '';
$success = '';
$member = null;
$plans = [];

function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

if (isset($_GET['reset'])) {
    unset($_SESSION['online_payment_member_id']);
    header('Location: online-payment.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } elseif (($_POST['action'] ?? '') === 'verify') {
        $memberId = filter_input(INPUT_POST, 'member_id', FILTER_VALIDATE_INT);
        $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
        $stmt = $pdo->prepare("SELECT MemberID FROM member WHERE MemberID = ? AND REPLACE(REPLACE(REPLACE(PhoneNo, '-', ''), ' ', ''), '+', '') = ? LIMIT 1");
        $stmt->execute([$memberId, $phone]);
        $match = $stmt->fetch();
        if ($match) {
            $_SESSION['online_payment_member_id'] = (int)$match['MemberID'];
            header('Location: online-payment.php');
            exit;
        }
        $error = 'We could not verify those details. Check your Member ID and registered phone number.';
    } elseif (($_POST['action'] ?? '') === 'submit_payment') {
        $memberId = (int)($_SESSION['online_payment_member_id'] ?? 0);
        $planId = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);
        $phone = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
        $memberStmt = $pdo->prepare("SELECT MemberID FROM member WHERE MemberID = ? AND REPLACE(REPLACE(REPLACE(PhoneNo, '-', ''), ' ', ''), '+', '') = ? LIMIT 1");
        $memberStmt->execute([$memberId, $phone]);
        $memberMatch = $memberStmt->fetch();
        $planStmt = $pdo->prepare("SELECT PlanID, Rate, Duration, PlanType FROM plan WHERE PlanID = ? AND IsActive = 1");
        $planStmt->execute([$planId]);
        $plan = $planStmt->fetch();

        if (!$memberMatch || !$plan) {
            $error = 'Please verify your member details and choose a valid plan again.';
            unset($_SESSION['online_payment_member_id']);
        } elseif (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please upload your payment receipt.';
        } elseif ($_FILES['receipt']['size'] > 4 * 1024 * 1024) {
            $error = 'Receipt must be 4 MB or smaller.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['receipt']['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($allowed[$mime])) {
                $error = 'Upload a JPG, PNG, or WEBP image.';
            } else {
                $uploadDir = __DIR__ . '/uploads/payment_receipts/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
                $destination = $uploadDir . $fileName;
                if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $destination)) {
                    $error = 'Could not save your receipt. Please try again.';
                } else {
                    $start = new DateTimeImmutable('today');
                    $end = $start;
                    $duration = max(1, (int)$plan['Duration']);
                    $type = strtolower((string)$plan['PlanType']);
                    if ($type === 'months') { $end = $end->modify('+' . $duration . ' months'); }
                    elseif ($type === 'years') { $end = $end->modify('+' . $duration . ' years'); }
                    else { $end = $end->modify('+' . $duration . ' days'); }

                    try {
                        $pdo->beginTransaction();
                        $membershipStmt = $pdo->prepare("INSERT INTO membership (MemberID, PlanID, StaffID, StartDate, EndDate, Status) VALUES (?, ?, NULL, ?, ?, 'Pending')");
                        $membershipStmt->execute([$memberId, $planId, $start->format('Y-m-d'), $end->format('Y-m-d')]);
                        $membershipId = (int)$pdo->lastInsertId();
                        $paymentStmt = $pdo->prepare("INSERT INTO payment (MembershipID, PaymentMethodID, StaffID, PaymentDate, AmountPaid, PaymentStatus, Remarks, ReceiptImage) VALUES (?, 2, NULL, NOW(), ?, 'Pending', 'Online QR payment - awaiting admin review', ?)");
                        $paymentStmt->execute([$membershipId, $plan['Rate'], 'uploads/payment_receipts/' . $fileName]);
                        $pdo->commit();
                        $success = 'Your receipt was submitted. Your payment is now pending admin approval.';
                        unset($_SESSION['online_payment_member_id']);
                    } catch (Throwable $e) {
                        if ($pdo->inTransaction()) { $pdo->rollBack(); }
                        if (is_file($destination)) { unlink($destination); }
                        $error = 'We could not submit your payment. Please contact the gym administrator.';
                    }
                }
            }
        }
    }
}

if (!empty($_SESSION['online_payment_member_id'])) {
    $stmt = $pdo->prepare("SELECT MemberID, FirstName, LastName, PhoneNo FROM member WHERE MemberID = ?");
    $stmt->execute([$_SESSION['online_payment_member_id']]);
    $member = $stmt->fetch();
    if (!$member) { unset($_SESSION['online_payment_member_id']); }
}
if ($member) {
    $plans = $pdo->query("SELECT PlanID, PlanName, Rate, Duration, PlanType FROM plan WHERE IsActive = 1 ORDER BY Rate ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Online Payment | New You Fitness</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f4f6f8;color:#1f2937;font-family:Arial,sans-serif}.wrap{max-width:760px;margin:40px auto;padding:24px}.panel{background:#fff;border-radius:16px;padding:28px;box-shadow:0 8px 30px #17255412}.brand{font-size:13px;color:#64748b;letter-spacing:1.5px;font-weight:bold}.title{margin:8px 0 6px;font-size:28px}.muted{color:#64748b;line-height:1.5}.field{margin:18px 0}.field label{display:block;font-weight:600;margin-bottom:8px}.field input,.field select{width:100%;padding:13px;border:1px solid #cbd5e1;border-radius:8px;font-size:15px}.btn{display:inline-block;border:0;background:#111827;color:white;padding:13px 20px;border-radius:8px;font-weight:bold;cursor:pointer;text-decoration:none}.alert{padding:13px;border-radius:8px;margin:18px 0;background:#fff1f2;color:#9f1239}.success{background:#ecfdf5;color:#166534}.qr{border:1px dashed #94a3b8;border-radius:12px;padding:24px;text-align:center;margin:18px 0;background:#f8fafc}.qr img{max-width:230px;width:100%;height:auto}.steps{display:flex;gap:8px;margin:20px 0;color:#64748b;font-size:13px}.step{padding:8px 10px;border-radius:20px;background:#f1f5f9}.note{font-size:13px;color:#64748b}.member{padding:12px;background:#f8fafc;border-radius:8px}
</style>
</head>
<body>
<div class="wrap"><div class="panel">
<div class="brand">NEW YOU FITNESS</div>
<h1 class="title">Online Membership Payment</h1>
<p class="muted">Verify your membership, pay using the gym QR code, and submit your receipt for approval.</p>
<?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
<?php if ($success): ?>
<div class="alert success"><strong>Submission received!</strong><br><?= h($success) ?></div>
<p class="muted">You can close this page. The gym will review your receipt before activating your membership.</p>
<a class="btn" href="online-payment.php?reset=1">Submit another payment</a>
<?php elseif (!$member): ?>
<div class="steps"><span class="step">1. Verify member</span><span class="step">2. Pay and upload receipt</span></div>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
<input type="hidden" name="action" value="verify">
<div class="field"><label for="member_id">Member ID</label><input id="member_id" type="number" name="member_id" min="1" required></div>
<div class="field"><label for="phone">Registered phone number</label><input id="phone" type="tel" name="phone" placeholder="09XXXXXXXXX" required></div>
<button class="btn" type="submit">Continue</button>
</form>
<?php else: ?>
<div class="steps"><span class="step">1. Verified</span><span class="step">2. Pay and upload receipt</span></div>
<div class="member">Member: <strong><?= h($member['FirstName'] . ' ' . $member['LastName']) ?></strong> · ID #<?= (int)$member['MemberID'] ?></div>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
<input type="hidden" name="action" value="submit_payment">
<input type="hidden" name="phone" value="<?= h($member['PhoneNo']) ?>">
<div class="field"><label for="plan_id">Choose membership plan</label><select id="plan_id" name="plan_id" required><option value="">Select a plan</option><?php foreach ($plans as $plan): ?><option value="<?= (int)$plan['PlanID'] ?>"><?= h($plan['PlanName']) ?> - ₱<?= number_format((float)$plan['Rate'],2) ?></option><?php endforeach; ?></select></div>
<div class="qr">
<strong>Scan the gym's payment QR code</strong>
<p class="note">The official bank QR image will appear here once the gym uploads it.</p>
<img src="assets/images/bank-qr.png" alt="Gym bank QR code" onerror="this.style.display='none'">
<p class="note">Pay the exact amount shown for your selected plan.</p>
</div>
<div class="field"><label for="receipt">Upload payment receipt (JPG, PNG, WEBP; max 4 MB)</label><input id="receipt" type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required></div>
<button class="btn" type="submit">Submit for Admin Approval</button>
</form>
<p class="note" style="margin-top:16px"><a href="online-payment.php?reset=1">Not you? Verify a different member</a></p>
<?php endif; ?>
</div></div>
</body></html>
