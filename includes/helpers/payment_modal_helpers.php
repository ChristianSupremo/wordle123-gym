<?php
/**
 * Payment Modal Helper Functions
 * Shared functions used across payment modals
 */

/**
 * Get active AND pending memberships for payment dropdowns
 */
function getActiveMemberships($pdo) {
    $stmt = $pdo->query("
        SELECT 
            m.MembershipID,
            m.MemberID,
            CONCAT(mem.FirstName, ' ', mem.LastName) AS MemberName,
            p.PlanName,
            p.Rate,
            m.StartDate,
            m.EndDate,
            m.Status
        FROM Membership m
        JOIN Member mem ON m.MemberID = mem.MemberID
        JOIN Plan p ON m.PlanID = p.PlanID
        WHERE m.Status IN ('Active', 'Pending')
        ORDER BY 
            CASE m.Status
                WHEN 'Pending' THEN 1
                WHEN 'Active' THEN 2
            END,
            mem.LastName, 
            mem.FirstName
    ");
    return $stmt->fetchAll();
}
/**
 * Get active payment methods
 */
function getPaymentMethods($pdo) {
    $stmt = $pdo->query("
        SELECT PaymentMethodID, MethodName, Description
        FROM PaymentMethods
        WHERE IsActive = 1
        ORDER BY MethodName
    ");
    return $stmt->fetchAll();
}

/**
 * Render payment method options
 */
function renderPaymentMethodOptions($payment_methods, $selected_id = null) {
    foreach ($payment_methods as $method) {
        $selected = ($selected_id && $method['PaymentMethodID'] == $selected_id) ? 'selected' : '';
        echo '<option value="' . $method['PaymentMethodID'] . '" 
                      data-method-name="' . htmlspecialchars($method['MethodName']) . '" 
                      ' . $selected . '>';
        echo htmlspecialchars($method['MethodName']);
        echo '</option>';
    }
}

/**
 * Render membership dropdown options
 */
function renderMembershipOptions($active_memberships) {
    foreach ($active_memberships as $membership) {
        $statusBadge = $membership['Status'] === 'Pending' 
            ? '<span class="status-badge-small pending">Pending</span>' 
            : '<span class="status-badge-small active">Active</span>';
        
        echo '<div class="searchable-select-option membership-option" 
                   data-value="' . $membership['MembershipID'] . '"
                   data-text="' . htmlspecialchars($membership['MemberName'] . ' - ' . $membership['PlanName']) . '"
                   data-rate="' . $membership['Rate'] . '">';
        echo '<div class="membership-option-header">';
        echo htmlspecialchars($membership['MemberName']);
        echo ' ' . $statusBadge;
        echo '</div>';
        echo '<div class="membership-option-details">';
        echo '<span class="plan-name">' . htmlspecialchars($membership['PlanName']) . '</span>';
        echo '<span class="plan-rate">₱' . number_format($membership['Rate'], 2) . '</span>';
        echo '</div>';
        echo '<div class="membership-option-dates">';
        echo date('M d, Y', strtotime($membership['StartDate'])) . ' - ' . date('M d, Y', strtotime($membership['EndDate']));
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Get payment method icon based on method name
 */
function getPaymentMethodIcon($method_name) {
    $icons = [
        'GCash' => 'phone',
        'Bank Transfer' => 'bank',
        'Credit Card' => 'credit-card',
        'Debit Card' => 'credit-card',
        'Cash' => 'cash'
    ];
    return $icons[$method_name] ?? 'cash';
}

/**
 * Format payment date for display
 */
function formatPaymentDate($date_string, $format = 'full') {
    if (!$date_string) return 'N/A';
    
    $date = new DateTime($date_string);
    
    if ($format === 'full') {
        return $date->format('M d, Y g:i A');
    } elseif ($format === 'short') {
        return $date->format('m/d/y g:i A');
    }
    
    return $date->format('Y-m-d H:i:s');
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'Completed' => 'completed',
        'Pending' => 'pending',
        'Failed' => 'failed'
    ];
    return $classes[$status] ?? 'pending';
}
?>