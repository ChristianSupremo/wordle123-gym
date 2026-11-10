<?php
// Staff-only: look up a member's portal account and enable/disable it.
//   GET  ?member_id=N  -> { has_account, status, csrf_token }
//   POST member_id, status (Active|Disabled), csrf_token
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
check_login();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['member_invite_csrf'])) {
    $_SESSION['member_invite_csrf'] = bin2hex(random_bytes(32));
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $memberId = filter_input(INPUT_GET, 'member_id', FILTER_VALIDATE_INT);
        if (!$memberId || $memberId < 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'A valid member ID is required.']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT AccountStatus, LastLogin FROM MemberAccounts WHERE MemberID = ? LIMIT 1");
        $stmt->execute([$memberId]);
        $acc = $stmt->fetch();
        echo json_encode([
            'success' => true,
            'has_account' => (bool) $acc,
            'status' => $acc['AccountStatus'] ?? null,
            'last_login' => $acc['LastLogin'] ?? null,
            'csrf_token' => $_SESSION['member_invite_csrf'],
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        exit;
    }

    $csrf = $_POST['csrf_token'] ?? '';
    if (!is_string($csrf) || !hash_equals($_SESSION['member_invite_csrf'], $csrf)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid request token. Refresh and try again.']);
        exit;
    }

    $memberId = filter_input(INPUT_POST, 'member_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    if (!$memberId || $memberId < 1 || !in_array($status, ['Active', 'Disabled'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE MemberAccounts SET AccountStatus = ? WHERE MemberID = ?");
    $stmt->execute([$status, $memberId]);
    if ($stmt->rowCount() === 0) {
        $exists = $pdo->prepare("SELECT 1 FROM MemberAccounts WHERE MemberID = ?");
        $exists->execute([$memberId]);
        if (!$exists->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'This member has no portal account.']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'status' => $status,
        'message' => $status === 'Disabled' ? 'Portal access disabled.' : 'Portal access enabled.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update portal access. Please try again.']);
}
