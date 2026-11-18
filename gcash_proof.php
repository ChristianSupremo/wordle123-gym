<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/member_auth.php';
require_once __DIR__ . '/includes/gcash_submission_helpers.php';

$isStaff = !empty($_SESSION['staff_id']);
$isMember = !$isStaff && !empty($_SESSION['member_account_id']) && !empty($_SESSION['member_id']);
if ($isStaff) {
    check_login();
} elseif ($isMember) {
    check_member_login($pdo);
} else {
    http_response_code(403);
    exit('Access denied.');
}

$submissionId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$submissionId || $submissionId < 1) {
    http_response_code(404);
    exit('Proof not found.');
}
$sql = "SELECT sub.MemberID, sub.ProofFileName
        FROM MemberGcashPaymentSubmissions sub
        WHERE sub.SubmissionID = ?";
$params = [$submissionId];
if ($isMember) {
    $sql .= " AND sub.MemberID = ?";
    $params[] = (int) $_SESSION['member_id'];
}
$sql .= " LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$row = $stmt->fetch();
if (!$row || !preg_match('/^[a-f0-9]{48}\.(jpg|png|webp)$/', $row['ProofFileName'])) {
    http_response_code(404);
    exit('Proof not found.');
}

$path = gcash_private_upload_dir() . DIRECTORY_SEPARATOR . $row['ProofFileName'];
if (!is_file($path) || !is_readable($path)) {
    http_response_code(404);
    exit('Proof image is unavailable.');
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($path);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(404);
    exit('Proof image is unavailable.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="payment-proof.' . ($mime === 'image/jpeg' ? 'jpg' : ($mime === 'image/png' ? 'png' : 'webp')) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
readfile($path);
exit;
