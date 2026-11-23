-- RBAC follow-up: correct the Manager role description (Managers can no longer manage staff).
-- Non-destructive: updates one text column on one existing row. No IDs, permissions, or other rows change.
-- Apply: C:\xampp\mysql\bin\mysql.exe -uroot gym_db < database-uploads/migrations/rbac_manager_role_description.sql
UPDATE Roles
SET Description = 'Manages gym operations, members, memberships, payments, plans, attendance, and reports. Cannot manage staff accounts or role permissions.'
WHERE RoleID = 2 AND RoleName = 'Manager';
