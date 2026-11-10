<?php
// Read-only data helpers for the member portal.
// EVERY query is scoped to the member id passed in, which must come from $_SESSION['member_id'].

function member_get_profile(PDO $pdo, int $memberId): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM member WHERE MemberID = ? LIMIT 1");
    $stmt->execute([$memberId]);
    return $stmt->fetch() ?: null;
}

/** Latest membership (same rule the attendance kiosk uses). */
function member_get_current_membership(PDO $pdo, int $memberId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT ms.MembershipID, ms.StartDate, ms.EndDate, ms.Status,
                p.PlanName, p.PlanType, p.Duration, p.Rate,
                DATEDIFF(ms.EndDate, CURDATE()) AS DaysLeft,
                DATEDIFF(ms.EndDate, ms.StartDate) AS TotalDays,
                DATEDIFF(CURDATE(), ms.StartDate) AS ElapsedDays
         FROM membership ms
         LEFT JOIN plan p ON p.PlanID = ms.PlanID
         WHERE ms.MemberID = ?
         ORDER BY ms.StartDate DESC, ms.MembershipID DESC
         LIMIT 1"
    );
    $stmt->execute([$memberId]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['EffectiveStatus'] = member_effective_status($row);
    return $row;
}

function member_effective_status(array $m): string
{
    if ($m['Status'] === 'Active' && $m['EndDate'] !== null && (int) $m['DaysLeft'] < 0) {
        return 'Expired';
    }
    return $m['Status'];
}

function member_get_membership_history(PDO $pdo, int $memberId): array
{
    $stmt = $pdo->prepare(
        "SELECT ms.MembershipID, ms.StartDate, ms.EndDate, ms.Status,
                p.PlanName, p.Rate, DATEDIFF(ms.EndDate, CURDATE()) AS DaysLeft
         FROM membership ms
         LEFT JOIN plan p ON p.PlanID = ms.PlanID
         WHERE ms.MemberID = ?
         ORDER BY ms.StartDate DESC, ms.MembershipID DESC"
    );
    $stmt->execute([$memberId]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['EffectiveStatus'] = member_effective_status($r);
    }
    return $rows;
}

function member_get_payments(PDO $pdo, int $memberId, ?int $limit = null): array
{
    $sql = "SELECT pay.PaymentID, pay.PaymentDate, pay.AmountPaid, pay.ReferenceNumber, pay.PaymentStatus,
                   pm.MethodName, pl.PlanName
            FROM payment pay
            INNER JOIN membership ms ON ms.MembershipID = pay.MembershipID
            LEFT JOIN plan pl ON pl.PlanID = ms.PlanID
            LEFT JOIN paymentmethods pm ON pm.PaymentMethodID = pay.PaymentMethodID
            WHERE ms.MemberID = ?
            ORDER BY pay.PaymentDate DESC, pay.PaymentID DESC";
    if ($limit !== null) {
        $sql .= " LIMIT " . (int) $limit;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);
    return $stmt->fetchAll();
}

function member_total_paid(PDO $pdo, int $memberId): float
{
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(pay.AmountPaid), 0)
         FROM payment pay
         INNER JOIN membership ms ON ms.MembershipID = pay.MembershipID
         WHERE ms.MemberID = ? AND pay.PaymentStatus = 'Completed'"
    );
    $stmt->execute([$memberId]);
    return (float) $stmt->fetchColumn();
}

/** Attendance helpers. Return safe defaults if the attendance migration was never applied. */
function member_get_attendance(PDO $pdo, int $memberId, int $limit = 50): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT AttendanceID, CheckIn, CheckOut
             FROM attendance WHERE MemberID = ?
             ORDER BY CheckIn DESC LIMIT " . (int) $limit
        );
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function member_attendance_counts(PDO $pdo, int $memberId): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(YEAR(CheckIn) = YEAR(CURDATE()) AND MONTH(CheckIn) = MONTH(CURDATE())) AS this_month,
                    SUM(CheckIn >= (CURDATE() - INTERVAL 6 DAY)) AS last_7_days
             FROM attendance WHERE MemberID = ?"
        );
        $stmt->execute([$memberId]);
        $r = $stmt->fetch();
        return [
            'total' => (int) ($r['total'] ?? 0),
            'this_month' => (int) ($r['this_month'] ?? 0),
            'last_7_days' => (int) ($r['last_7_days'] ?? 0),
        ];
    } catch (Throwable $e) {
        return ['total' => 0, 'this_month' => 0, 'last_7_days' => 0];
    }
}

function member_format_duration(?string $in, ?string $out): string
{
    if (!$in || !$out) {
        return '—';
    }
    $mins = (int) round((strtotime($out) - strtotime($in)) / 60);
    if ($mins < 1) {
        return '< 1 min';
    }
    return $mins >= 60 ? intdiv($mins, 60) . 'h ' . ($mins % 60) . 'm' : $mins . ' min';
}

function member_days_left_html(array $m): string
{
    $status = $m['EffectiveStatus'] ?? $m['Status'];
    $d = (int) $m['DaysLeft'];
    if ($status === 'Pending') return '<span style="color:#718096;">—</span>';
    if ($status === 'Cancelled') return '<span style="color:#718096;">Cancelled</span>';
    if ($status === 'Expired') return '<span style="color:#e53e3e;">Expired</span>';
    if ($d === 0) return '<span style="color:#dd6b20;">Expires today</span>';
    $label = $d . ' day' . ($d !== 1 ? 's' : '') . ' left';
    return '<span style="color:' . ($d <= 7 ? '#dd6b20' : '#38a169') . ';">' . $label . '</span>';
}
