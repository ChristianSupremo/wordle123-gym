<?php
/**
 * Plan Modal Helper Functions
 * Path: includes/plan_modal_helpers.php
 */

/**
 * Get all plans for dropdowns/selection
 */
function getAllPlans($pdo) {
    $stmt = $pdo->query("
        SELECT 
            PlanID,
            PlanName,
            Description,
            PlanType,
            Duration,
            Rate,
            IsActive
        FROM Plan
        ORDER BY IsActive DESC, PlanName
    ");
    return $stmt->fetchAll();
}

/**
 * Get plan by ID
 */
function getPlanById($pdo, $plan_id) {
    $stmt = $pdo->prepare("
        SELECT 
            PlanID,
            PlanName,
            Description,
            PlanType,
            Duration,
            Rate,
            IsActive
        FROM Plan
        WHERE PlanID = ?
    ");
    $stmt->execute([$plan_id]);
    return $stmt->fetch();
}

/**
 * Get plan statistics
 */
function getPlanStats($pdo, $plan_id) {
    // Get total active memberships
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM Membership
        WHERE PlanID = ? AND Status = 'Active'
    ");
    $stmt->execute([$plan_id]);
    $active_count = $stmt->fetch()['total'];

    // Get total revenue
    $stmt = $pdo->prepare("
        SELECT SUM(p.AmountPaid) as total_revenue
        FROM Payment p
        JOIN Membership m ON p.MembershipID = m.MembershipID
        WHERE m.PlanID = ? AND p.PaymentStatus = 'Completed'
    ");
    $stmt->execute([$plan_id]);
    $total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

    // Get all-time member count
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT m.MemberID) as total
        FROM Membership m
        WHERE m.PlanID = ?
    ");
    $stmt->execute([$plan_id]);
    $total_members = $stmt->fetch()['total'];

    return [
        'active_count' => $active_count,
        'total_revenue' => $total_revenue,
        'total_members' => $total_members
    ];
}

/**
 * Format duration display
 */
function formatDuration($duration, $type) {
    return $duration . ' ' . strtolower($type);
}

/**
 * Get plan type icon
 */
function getPlanTypeIcon($type) {
    $icons = [
        'Days' => 'calendar-day',
        'Months' => 'calendar3',
        'Years' => 'calendar-range'
    ];
    return $icons[$type] ?? 'calendar';
}
?>