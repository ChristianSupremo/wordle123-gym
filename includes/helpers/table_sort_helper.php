<?php
/**
 * Table Sorting Helper Functions
 * Handles sorting logic for database queries
 */

/**
 * Get sort parameters and validate against allowed columns
 */
function get_sort_params($config = null) {
    $sort_by = $_GET['sort_by'] ?? null;
    $sort_order = $_GET['sort_order'] ?? 'ASC';
    
    // Validate sort order
    $sort_order = strtoupper($sort_order);
    if (!in_array($sort_order, ['ASC', 'DESC'])) {
        $sort_order = 'ASC';
    }
    
    $mapped_column = null;
    
    // If config provided, validate and map column
    if ($config && $sort_by) {
        if (in_array($sort_by, $config['allowed_columns'])) {
            // Column is valid, map it if mapping exists
            $mapped_column = $config['column_mapping'][$sort_by] ?? $sort_by;
        } else {
            // Invalid column, set both to null
            $sort_by = null;
        }
    }
    
    return [
        'column' => $mapped_column,      // Mapped column for SQL
        'original' => $sort_by,          // Original parameter for HTML
        'order' => $sort_order
    ];
}

/**
 * Render sortable table header
 * 
 * @param string $label - Column label to display
 * @param string $column - Database column name
 * @param string $current_sort_by - Currently sorted column (original, not mapped)
 * @param string $current_sort_order - Current sort order
 * @param string $page - Page name (for JS)
 * @return string HTML for sortable header
 */
function render_sortable_header($label, $column, $current_sort_by = null, $current_sort_order = null, $page = 'members.php') {
    $is_sorted = ($current_sort_by === $column);
    $sorted_class = $is_sorted ? 'sorted ' . strtolower($current_sort_order) : '';
    
    // Determine sort hint
    $hint = '';
    if ($is_sorted) {
        if ($current_sort_order === 'ASC') {
            $hint = 'Click to sort descending';
        } else {
            $hint = 'Click to reset sorting';
        }
    } else {
        $hint = 'Click to sort ascending';
    }
    
    return sprintf(
        '<th class="sortable %s" data-column="%s" data-page="%s" data-sort-hint="%s">%s</th>',
        $sorted_class,
        htmlspecialchars($column),
        htmlspecialchars($page),
        htmlspecialchars($hint),
        htmlspecialchars($label)
    );
}

/**
 * Get sort indicator HTML (for page header display)
 * 
 * @param string|null $sort_column - Currently sorted column (original, not mapped)
 * @param string|null $sort_order - Current sort order
 * @param array $column_labels - Map of column names to display labels
 * @return string HTML for sort indicator
 */
function get_sort_indicator($sort_column, $sort_order, $column_labels = []) {
    if (empty($sort_column) || empty($sort_order)) {
        return '';
    }
    
    $label = $column_labels[$sort_column] ?? ucwords(str_replace('_', ' ', $sort_column));
    $icon = $sort_order === 'ASC' ? 'bi-sort-up' : 'bi-sort-down';
    $order_text = $sort_order === 'ASC' ? 'ascending' : 'descending';
    
    return sprintf(
        '<span class="sort-indicator"><i class="bi %s"></i> Sorted by %s (%s)</span>',
        $icon,
        htmlspecialchars($label),
        $order_text
    );
}

/**
 * Generate clear sort button
 * 
 * @return string HTML for clear sort button
 */
function render_clear_sort_button() {
    $sort_by = $_GET['sort_by'] ?? null;
    
    if (empty($sort_by)) {
        return '';
    }
    
    return '<button class="sort-clear-btn" onclick="clearSort()"><i class="bi bi-x-circle"></i> Clear Sort</button>';
}

/**
 * Members-specific sorting configuration
 */
function get_members_sort_config() {
    return [
        'allowed_columns' => [
            'MemberID',
            'FullName',
            'PhoneNo',
            'JoinDate'
        ],
        'column_mapping' => [
            'MemberID' => 'MemberID',
            'FullName' => 'FullName',
            'PhoneNo' => 'PhoneNo',
            'JoinDate' => 'JoinDate'
        ],
        'column_labels' => [
            'MemberID' => 'Member ID',
            'FullName' => 'Full Name',
            'PhoneNo' => 'Contact Number',
            'JoinDate' => 'Join Date'
        ],
        'default_order' => 'JoinDate DESC'
    ];
}

/**
 * Memberships-specific sorting configuration
 */
function get_memberships_sort_config() {
    return [
        'allowed_columns' => [
            'MembershipID',
            'MemberName',
            'PlanName',
            'StartDate',
            'EndDate',
            'DaysLeft',
            'Amount'
        ],
        'column_mapping' => [
            'MembershipID' => 'm.MembershipID',
            'MemberName' => 'MemberName',
            'PlanName' => 'pl.PlanName',
            'StartDate' => 'm.StartDate',
            'EndDate' => 'm.EndDate',
            'DaysLeft' => 'DaysLeft',
            'Amount' => 'pl.Rate'
        ],
        'column_labels' => [
            'MembershipID' => 'Membership ID',
            'MemberName' => 'Member Name',
            'PlanName' => 'Plan',
            'StartDate' => 'Start Date',
            'EndDate' => 'End Date',
            'Amount' => 'Amount'
        ],
        'default_order' => 'StartDate DESC'
    ];
}

/**
 * Payments-specific sorting configuration
 */
function get_payments_sort_config() {
    return [
        'allowed_columns' => [
            'PaymentID',
            'MemberName',
            'Amount',
            'PaymentDate',
            'PaymentMethod'
        ],
        'column_mapping' => [
            'PaymentID' => 'p.PaymentID',
            'MemberName' => 'MemberName',
            'Amount' => 'p.AmountPaid',
            'PaymentDate' => 'p.PaymentDate',
            'PaymentMethod' => 'pm.MethodName'
        ],
        'column_labels' => [
            'PaymentID' => 'Payment ID',
            'MemberName' => 'Member',
            'Amount' => 'Amount',
            'PaymentDate' => 'Date',
            'PaymentMethod' => 'Method'
        ],
        'default_order' => 'PaymentDate DESC'
    ];
}

/**
 * Staff-specific sorting configuration
 */
function get_staff_sort_config() {
    return [
        'allowed_columns' => [
            'StaffID',
            'FullName',
            'Email',
            'Role',
            'DateHired'
        ],
        'column_mapping' => [
            'StaffID' => 'StaffID',
            'FullName' => 'FullName',
            'Email' => 'Email',
            'Role' => 'Role',
            'DateHired' => 'DateHired'
        ],
        'column_labels' => [
            'StaffID' => 'Staff ID',
            'FullName' => 'Full Name',
            'Email' => 'Email',
            'Role' => 'Role',
            'DateHired' => 'Date Hired'
        ],
        'default_order' => 'DateHired DESC'
    ];
}

/**
 * Plans-specific sorting configuration
 */
function get_plans_sort_config() {
    return [
        'allowed_columns' => [
            'PlanID',
            'PlanName',
            'Duration',
            'Price'
        ],
        'column_mapping' => [
            'PlanID' => 'PlanID',
            'PlanName' => 'PlanName',
            'Duration' => 'Duration',
            'Price' => 'Price'
        ],
        'column_labels' => [
            'PlanID' => 'Plan ID',
            'PlanName' => 'Plan Name',
            'Duration' => 'Duration',
            'Price' => 'Price'
        ],
        'default_order' => 'PlanName ASC'
    ];
}
?>