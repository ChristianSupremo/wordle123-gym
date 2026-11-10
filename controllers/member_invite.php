<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
check_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (empty($_SESSION['member_invite_csrf'])) {
    $_SESSION['member_invite_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_POST['csrf_token'] ?? '';
if (!is_string($csrf) || !hash_equals($_SESSION['member_invite_csrf'], $csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request token. Refresh and try again.']);
    exit;
}

$memberId = filter_input(INPUT_POST, 'member_id', FILTER_VALIDATE_INT);
if (!$memberId || $memberId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid member ID is required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT MemberID, Email, FirstName, LastName FROM member WHERE MemberID = ? LIMIT 1");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();

    if (!$member || !filter_var($member['Email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'The member must have a valid email address before an invitation can be created.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT MemberAccountID FROM MemberAccounts WHERE MemberID = ? OR Username = ? LIMIT 1");
    $stmt->execute([$memberId, $member['Email']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'A member account already exists or this email is already in use.']);
        exit;
    }

    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    $expiresAt = (new DateTimeImmutable('+48 hours'))->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    // Invalidate any previous unused invitations for this member.
    $pdo->prepare("UPDATE MemberAccountInvitations SET UsedAt = NOW() WHERE MemberID = ? AND UsedAt IS NULL")
        ->execute([$memberId]);
    $insert = $pdo->prepare(
        "INSERT INTO MemberAccountInvitations (MemberID, TokenHash, ExpiresAt, InvitedBy)
         VALUES (?, ?, ?, ?)"
    );
    $insert->execute([$memberId, $tokenHash, $expiresAt, $_SESSION['staff_id']]);
    $pdo->commit();

    // Return only a relative activation path. Staff must deliver it to the verified member email.
    $activationPath = 'member/activate.php?token=' . rawurlencode($rawToken);
    echo json_encode([
        'success' => true,
        'message' => 'Invitation created. Send the activation link to the member using their verified email address.',
        'member_name' => trim($member['FirstName'] . ' ' . $member['LastName']),
        'email' => $member['Email'],
        'expires_at' => $expiresAt,
        'activation_path' => $activationPath
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to create the invitation. Please try again.']);
}
