<?php
// Member-side authentication helpers. Staff authentication remains in functions.php.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Protects a member page. Requires a member session AND an account that is still
 * Active in the database, so staff can cut off access immediately.
 */
function check_member_login(?PDO $pdo = null): void
{
    if (empty($_SESSION['member_account_id']) || empty($_SESSION['member_id'])) {
        header('Location: login.php');
        exit();
    }

    if ($pdo !== null) {
        $stmt = $pdo->prepare("SELECT AccountStatus FROM MemberAccounts WHERE MemberAccountID = ? AND MemberID = ? LIMIT 1");
        $stmt->execute([$_SESSION['member_account_id'], $_SESSION['member_id']]);
        $status = $stmt->fetchColumn();
        if ($status !== 'Active') {
            member_clear_session();
            header('Location: login.php?disabled=1');
            exit();
        }
    }
}

function member_clear_session(): void
{
    unset(
        $_SESSION['member_account_id'],
        $_SESSION['member_id'],
        $_SESSION['member_name'],
        $_SESSION['member_csrf_token'],
        $_SESSION['member_login_success']
    );
}

function member_csrf_token(): string
{
    if (empty($_SESSION['member_csrf_token'])) {
        $_SESSION['member_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['member_csrf_token'];
}

function verify_member_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['member_csrf_token'])
        && hash_equals($_SESSION['member_csrf_token'], $token);
}

function member_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Login throttling: 5 failures per email or 20 per IP within 15 minutes.
 * Degrades gracefully (no throttling) if the phase 2 migration has not been run.
 */
function member_login_is_throttled(PDO $pdo, string $username, string $ip): bool
{
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM MemberLoginAttempts
             WHERE IPAddress = ? AND Success = 0 AND AttemptedAt > (NOW() - INTERVAL 15 MINUTE)"
        );
        $stmt->execute([$ip]);
        $byIp = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM MemberLoginAttempts
             WHERE Username = ? AND Success = 0 AND AttemptedAt > (NOW() - INTERVAL 15 MINUTE)"
        );
        $stmt->execute([$username]);
        $byUser = (int) $stmt->fetchColumn();

        return $byUser >= 5 || $byIp >= 20;
    } catch (Throwable $e) {
        return false;
    }
}

function member_login_record(PDO $pdo, string $username, string $ip, bool $success): void
{
    try {
        $pdo->prepare("INSERT INTO MemberLoginAttempts (Username, IPAddress, Success) VALUES (?, ?, ?)")
            ->execute([mb_substr($username, 0, 100), $ip, $success ? 1 : 0]);
        if ($success) {
            // A successful sign-in clears the failure history for this email.
            $pdo->prepare("DELETE FROM MemberLoginAttempts WHERE Username = ? AND Success = 0")->execute([$username]);
        }
        // Opportunistic cleanup of old rows.
        if (random_int(1, 50) === 1) {
            $pdo->exec("DELETE FROM MemberLoginAttempts WHERE AttemptedAt < (NOW() - INTERVAL 1 DAY)");
        }
    } catch (Throwable $e) {
        // Throttling is best-effort; never block sign-in because logging failed.
    }
}
