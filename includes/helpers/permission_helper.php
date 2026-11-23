<?php
/**
 * Role-based authorization for staff.
 * Path: includes/helpers/permission_helper.php
 *
 * Permissions live in code, keyed by Roles.RoleName. Roles.AccessLevel is
 * display/sort metadata only and is never used for authorization.
 * Loaded automatically by includes/functions.php.
 */

const ROLE_PERMISSIONS = [
    'Admin' => ['*'],
    'Manager' => [
        'dashboard.view',
        'members.view', 'members.create', 'members.edit',
        'memberships.view', 'memberships.create', 'memberships.edit',
        'payments.view', 'payments.create', 'payments.edit',
        'plans.view', 'plans.manage',
        'attendance.manage',
        'insights.view',
    ],
    'Receptionist' => [
        'dashboard.view',
        'members.view', 'members.create', 'members.edit',
        'memberships.view', 'memberships.create',
        'payments.view', 'payments.create',
        'plans.view',
        'attendance.manage',
    ],
];

// Human-readable labels, also the source of the staff "Permissions" tab.
const PERMISSION_LABELS = [
    'dashboard.view'     => 'view the dashboard',
    'members.view'       => 'view members',
    'members.create'     => 'register members',
    'members.edit'       => 'edit members and portal access',
    'memberships.view'   => 'view memberships',
    'memberships.create' => 'create and renew memberships',
    'memberships.edit'   => 'edit existing memberships',
    'payments.view'      => 'view payments and GCash submissions',
    'payments.create'    => 'record payments and review GCash submissions',
    'payments.edit'      => 'edit recorded payments',
    'plans.view'         => 'view membership plans',
    'plans.manage'       => 'add, edit, and activate/deactivate plans',
    'attendance.manage'  => 'check members in and out',
    'insights.view'      => 'view member insights and reports',
    'staff.view'         => 'view staff records',
    'staff.create'       => 'create staff accounts',
    'staff.edit'         => 'edit staff accounts',
    'staff.assign_role'  => 'assign staff roles',
    'staff.toggle_status'=> 'activate and deactivate staff accounts',
];

const ADMIN_ROLE_NAME = 'Admin';

/**
 * Current staff row (StaffID, names, Status, RoleID, RoleName), loaded fresh
 * from the DB once per request so role changes and deactivation apply
 * immediately. Returns null when not logged in or the account is gone.
 */
function current_staff($refresh = false) {
    static $staff = false;
    if ($staff !== false && !$refresh) {
        return $staff;
    }
    global $pdo;
    $staff = null;
    if (!empty($_SESSION['staff_id']) && isset($pdo)) {
        $stmt = $pdo->prepare(
            "SELECT s.StaffID, s.FirstName, s.LastName, s.Status, s.RoleID, r.RoleName
             FROM Staff s LEFT JOIN Roles r ON r.RoleID = s.RoleID
             WHERE s.StaffID = ?"
        );
        $stmt->execute([(int) $_SESSION['staff_id']]);
        $staff = $stmt->fetch() ?: null;
    }
    return $staff;
}

function get_current_user_role() {
    $staff = current_staff();
    return $staff['RoleName'] ?? null;
}

function role_has_permission($role_name, $permission) {
    $granted = ROLE_PERMISSIONS[$role_name] ?? [];
    return in_array('*', $granted, true) || in_array($permission, $granted, true);
}

function has_permission($permission) {
    $staff = current_staff();
    return $staff !== null && $staff['Status'] === 'Active'
        && role_has_permission($staff['RoleName'], $permission);
}

function is_admin() {
    return get_current_user_role() === ADMIN_ROLE_NAME;
}

function is_manager() {
    return get_current_user_role() === 'Manager';
}

function is_receptionist() {
    return get_current_user_role() === 'Receptionist';
}

/** True when the response is JSON: api/ and controllers/ endpoints, fetch() requests
 * (X-Requested-With, set by the wrapper in header.php), or a JSON Content-Type already sent. */
function wants_json_response() {
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        return true;
    }
    $dir = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
    if (in_array($dir, ['api', 'controllers'], true)) {
        return true;
    }
    foreach (headers_list() as $h) {
        if (stripos($h, 'Content-Type: application/json') === 0) {
            return true;
        }
    }
    return false;
}

/** Sends a 403 (JSON for endpoints, an in-app page otherwise) and stops. */
function deny_access($message = null) {
    http_response_code(403);
    $is_permission = $message === null;
    $message = $message ?? 'You do not have permission to perform this action.';
    if (wants_json_response()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
    include __DIR__ . '/../header.php';
    echo '<div class="page-header"><h1 class="page-title">Access denied</h1></div>'
       . '<div class="dashboard-card" style="max-width:560px;">'
       . '<p style="color: var(--text-primary);">' . htmlspecialchars($message) . ($is_permission ? ' If you need access, ask an administrator.' : '') . '</p>'
       . '<p style="margin-top:14px;"><a href="dashboard.php" class="add-member-btn" style="display:inline-flex;">Back to dashboard</a></p>'
       . '</div>';
    include __DIR__ . '/../footer.php';
    exit();
}

function require_permission($permission) {
    check_login();
    if (!has_permission($permission)) {
        deny_access();
    }
}

function require_any_permission(array $permissions) {
    check_login();
    foreach ($permissions as $permission) {
        if (has_permission($permission)) {
            return;
        }
    }
    deny_access();
}

/** "Can ..." / "Cannot ..." lines for a role, used by the staff view modal. */
function describe_role_permissions($role_name) {
    $lines = [];
    foreach (PERMISSION_LABELS as $permission => $label) {
        $lines[] = (role_has_permission($role_name, $permission) ? 'Can ' : 'Cannot ') . $label;
    }
    return $lines;
}
