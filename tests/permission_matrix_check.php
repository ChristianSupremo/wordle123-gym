<?php
// Self-check for the role permission map. Run: C:\xampp\php\php.exe tests\permission_matrix_check.php
// No database or session needed; exits 1 on any mismatch.
require __DIR__ . '/../includes/helpers/permission_helper.php';

// permission => [Admin, Manager, Receptionist]
$expected = [
    'dashboard.view'      => [1, 1, 1],
    'members.view'        => [1, 1, 1],
    'members.create'      => [1, 1, 1],
    'members.edit'        => [1, 1, 1],
    'memberships.view'    => [1, 1, 1],
    'memberships.create'  => [1, 1, 1],
    'memberships.edit'    => [1, 1, 0],
    'payments.view'       => [1, 1, 1],
    'payments.create'     => [1, 1, 1],
    'payments.edit'       => [1, 1, 0],
    'plans.view'          => [1, 1, 1],
    'plans.manage'        => [1, 1, 0],
    'attendance.manage'   => [1, 1, 1],
    'insights.view'       => [1, 1, 0],
    'staff.view'          => [1, 0, 0],
    'staff.create'        => [1, 0, 0],
    'staff.edit'          => [1, 0, 0],
    'staff.assign_role'   => [1, 0, 0],
    'staff.toggle_status' => [1, 0, 0],
];

$failures = 0;
foreach ($expected as $permission => $flags) {
    foreach (['Admin', 'Manager', 'Receptionist'] as $i => $role) {
        if (role_has_permission($role, $permission) !== (bool) $flags[$i]) {
            echo "FAIL: $role / $permission\n";
            $failures++;
        }
    }
}
// Every permission granted anywhere must be in the matrix above and have a label.
foreach (ROLE_PERMISSIONS as $role => $granted) {
    foreach ($granted as $permission) {
        if ($permission !== '*' && (!isset($expected[$permission]) || !isset(PERMISSION_LABELS[$permission]))) {
            echo "FAIL: $role grants unlisted permission $permission\n";
            $failures++;
        }
    }
}
if (role_has_permission('Unknown', 'dashboard.view') || role_has_permission(null, 'dashboard.view')) {
    echo "FAIL: unknown roles must get no permissions\n";
    $failures++;
}

echo $failures ? "$failures failure(s)\n" : "OK: permission matrix matches\n";
exit($failures ? 1 : 0);
