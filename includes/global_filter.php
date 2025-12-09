<?php
/**
 * BACKEND FILTER PROCESSING EXAMPLES
 * How to handle filters in your PHP backend
 */

// ===================================================================
// MEMBERS.PHP - Complete filtering example
// ===================================================================

// Get filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? 'All';
$plan_filter = $_GET['plan_filter'] ?? 'All';
$expiration_filter = $_GET['expiration_filter'] ?? 'All';
$date_joined_filter = $_GET['date_joined_filter'] ?? 'All';

// Build SQL query
$sql = "SELECT m.*, mp.plan_name, mp.duration, mp.duration_unit 
        FROM members m 
        LEFT JOIN memberships ms ON m.member_id = ms.member_id AND ms.status = 'Active'
        LEFT JOIN membership_plans mp ON ms.plan_id = mp.plan_id 
        WHERE 1=1";

$params = [];
$types = "";

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR m.email LIKE ? OR m.phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "ssss";
}

// Apply status filter
if ($status_filter !== 'All') {
    $sql .= " AND m.status = ?";
    $params[] = &$status_filter;
    $types .= "s";
}

// Apply plan filter
if ($plan_filter !== 'All') {
    $sql .= " AND mp.plan_name = ?";
    $params[] = &$plan_filter;
    $types .= "s";
}

// Apply expiration filter
if ($expiration_filter === 'expires_7days') {
    $sql .= " AND ms.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
} elseif ($expiration_filter === 'expired') {
    $sql .= " AND ms.end_date < CURDATE()";
}

// Apply date joined filter
if ($date_joined_filter === 'today') {
    $sql .= " AND DATE(m.created_at) = CURDATE()";
} elseif ($date_joined_filter === 'this_week') {
    $sql .= " AND YEARWEEK(m.created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($date_joined_filter === 'this_month') {
    $sql .= " AND YEAR(m.created_at) = YEAR(CURDATE()) AND MONTH(m.created_at) = MONTH(CURDATE())";
}

$sql .= " ORDER BY m.created_at DESC";

// Execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ===================================================================
// MEMBERSHIPS.PHP - Membership filtering
// ===================================================================

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? 'All';
$plan_filter = $_GET['plan_filter'] ?? 'All';
$expiration_filter = $_GET['expiration_filter'] ?? 'All';

$sql = "SELECT ms.*, m.first_name, m.last_name, m.email, mp.plan_name, mp.price 
        FROM memberships ms
        JOIN members m ON ms.member_id = m.member_id
        JOIN membership_plans mp ON ms.plan_id = mp.plan_id
        WHERE 1=1";

$params = [];
$types = "";

// Search
if (!empty($search)) {
    $sql .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR mp.plan_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "sss";
}

// Status filter
if ($status_filter !== 'All') {
    $sql .= " AND ms.status = ?";
    $params[] = &$status_filter;
    $types .= "s";
}

// Plan filter
if ($plan_filter !== 'All') {
    $sql .= " AND mp.plan_name = ?";
    $params[] = &$plan_filter;
    $types .= "s";
}

// Expiration filter
if ($expiration_filter === 'expires_7days') {
    $sql .= " AND ms.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND ms.status = 'Active'";
} elseif ($expiration_filter === 'expires_30days') {
    $sql .= " AND ms.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND ms.status = 'Active'";
} elseif ($expiration_filter === 'expired') {
    $sql .= " AND ms.end_date < CURDATE()";
}

$sql .= " ORDER BY ms.start_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ===================================================================
// PAYMENTS.PHP - Payment filtering
// ===================================================================

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? 'All';
$method_filter = $_GET['method_filter'] ?? [];
$date_filter = $_GET['date_filter'] ?? 'All';

$sql = "SELECT p.*, m.first_name, m.last_name, mp.plan_name 
        FROM payments p
        JOIN members m ON p.member_id = m.member_id
        LEFT JOIN membership_plans mp ON p.plan_id = mp.plan_id
        WHERE 1=1";

$params = [];
$types = "";

// Search
if (!empty($search)) {
    $sql .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR p.reference_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "sss";
}

// Status filter
if ($status_filter !== 'All') {
    $sql .= " AND p.status = ?";
    $params[] = &$status_filter;
    $types .= "s";
}

// Method filter (checkbox - multiple values)
if (!empty($method_filter) && is_array($method_filter)) {
    $placeholders = str_repeat('?,', count($method_filter) - 1) . '?';
    $sql .= " AND p.payment_method IN ($placeholders)";
    foreach ($method_filter as &$method) {
        $params[] = &$method;
        $types .= "s";
    }
}

// Date filter
if ($date_filter === 'today') {
    $sql .= " AND DATE(p.payment_date) = CURDATE()";
} elseif ($date_filter === 'this_week') {
    $sql .= " AND YEARWEEK(p.payment_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($date_filter === 'this_month') {
    $sql .= " AND YEAR(p.payment_date) = YEAR(CURDATE()) AND MONTH(p.payment_date) = MONTH(CURDATE())";
} elseif ($date_filter === 'last_month') {
    $sql .= " AND YEAR(p.payment_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
              AND MONTH(p.payment_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
}

$sql .= " ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ===================================================================
// PLANS.PHP - Plan filtering
// ===================================================================

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? 'All';
$duration_filter = $_GET['duration_filter'] ?? 'All';
$price_filter = $_GET['price_filter'] ?? 'All';

$sql = "SELECT * FROM membership_plans WHERE 1=1";

$params = [];
$types = "";

// Search
if (!empty($search)) {
    $sql .= " AND (plan_name LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "ss";
}

// Status filter
if ($status_filter !== 'All') {
    $sql .= " AND status = ?";
    $params[] = &$status_filter;
    $types .= "s";
}

// Duration filter
if ($duration_filter !== 'All') {
    // Assuming you have a duration_type column
    $sql .= " AND duration_unit = ?";
    $duration_map = [
        'Monthly' => 'month',
        'Quarterly' => 'quarter',
        'Yearly' => 'year',
        'Lifetime' => 'lifetime'
    ];
    $duration_value = $duration_map[$duration_filter] ?? $duration_filter;
    $params[] = &$duration_value;
    $types .= "s";
}

// Price filter
if ($price_filter === 'under_500') {
    $sql .= " AND price < 500";
} elseif ($price_filter === '500_1000') {
    $sql .= " AND price BETWEEN 500 AND 1000";
} elseif ($price_filter === '1000_2000') {
    $sql .= " AND price BETWEEN 1000 AND 2000";
} elseif ($price_filter === 'over_2000') {
    $sql .= " AND price > 2000";
}

$sql .= " ORDER BY price ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ===================================================================
// STAFF.PHP - Staff filtering
// ===================================================================

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? 'All';
$role_filter = $_GET['role_filter'] ?? 'All';
$date_hired_filter = $_GET['date_hired_filter'] ?? 'All';

$sql = "SELECT * FROM staff WHERE 1=1";

$params = [];
$types = "";

// Search
if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "sss";
}

// Status filter
if ($status_filter !== 'All') {
    $sql .= " AND status = ?";
    $params[] = &$status_filter;
    $types .= "s";
}

// Role filter
if ($role_filter !== 'All') {
    $sql .= " AND role = ?";
    $params[] = &$role_filter;
    $types .= "s";
}

// Date hired filter
if ($date_hired_filter === 'this_month') {
    $sql .= " AND YEAR(date_hired) = YEAR(CURDATE()) AND MONTH(date_hired) = MONTH(CURDATE())";
} elseif ($date_hired_filter === 'this_year') {
    $sql .= " AND YEAR(date_hired) = YEAR(CURDATE())";
}

$sql .= " ORDER BY date_hired DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ===================================================================
// HELPER FUNCTION: Build filter summary for display
// ===================================================================

function getActiveFilters() {
    $active = [];
    
    foreach ($_GET as $key => $value) {
        if (strpos($key, '_filter') !== false && $value !== 'All' && !empty($value)) {
            $label = str_replace('_filter', '', $key);
            $label = ucwords(str_replace('_', ' ', $label));
            
            if (is_array($value)) {
                $active[] = "$label: " . implode(', ', $value);
            } else {
                $active[] = "$label: $value";
            }
        }
    }
    
    return $active;
}

// Display active filters
$active_filters = getActiveFilters();
if (!empty($active_filters)) {
    echo '<div class="active-filters">';
    echo '<span>Active filters:</span> ';
    echo implode(' | ', $active_filters);
    echo ' <a href="?' . (isset($_GET['action']) ? 'action=' . $_GET['action'] : '') . '">Clear all</a>';
    echo '</div>';
}
?>