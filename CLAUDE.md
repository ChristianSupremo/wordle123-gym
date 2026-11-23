# CLAUDE.md — NEWYOU Gym Management System

Project guide for Claude Code sessions. Everything here was verified against the code and the `gym_db` database. Update this file when you change architecture, schema, or permissions.

## A. Project overview

- **Name:** NEWYOU Gym Management System (repo: `wordle123-gym`).
- **Purpose:** Front-desk and back-office app for a gym. Covers members, memberships and plans, payments (cash, GCash), QR attendance check-in, member insights, and staff accounts. A separate **member portal** (`member/`) lets members log in, view their membership, attendance, and payments, and submit GCash proof.
- **Stack:** Plain PHP 8.2 (no framework, no Composer), MySQL/MariaDB via PDO, server-rendered HTML, vanilla JS, Bootstrap Icons (CDN), qrcodejs (CDN, `member_qr.php`). No build step, no npm.
- **Local environment:** XAMPP on Windows. The project lives in `C:\xampp\htdocs\wordle123-gym` and is served at `http://localhost/wordle123-gym/`.
  - PHP CLI: `C:\xampp\php\php.exe`. MySQL CLI: `C:\xampp\mysql\bin\mysql.exe -uroot gym_db`.
- **Database:** `gym_db`. Connection settings are hard-coded in `config/db.php` (`localhost`, `root`, empty password, `utf8mb4`). It creates `$pdo` with `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, and real prepared statements (`EMULATE_PREPARES = false`).
- **Run locally:**
  1. Start Apache and MySQL in the XAMPP Control Panel.
  2. Create `gym_db` and import `database-uploads/ay2026-2027/11-10/gym_db.sql` (the newest full dump; it already includes `attendance`, `MemberAccounts`, `MemberAccountInvitations`, and `MemberLoginAttempts`).
  3. Apply `database-uploads/migrations/member_gcash_payment_submissions.sql` (adds `GcashPaymentSettings` and `MemberGcashPaymentSubmissions`). See `docs/member-gcash-payments.md` for the private upload directory (`NEWYOU_PRIVATE_UPLOAD_DIR`).
  4. Open `http://localhost/wordle123-gym/`. The seeded staff logins are `admin`, `manager`, and `ira.flores` (Receptionist). In the demo dump, all of them use the password `password`.
- Older dumps under `database-uploads/ay2025-2026/`, `ay2026-2027/10-26/`, and `11-09/files/` are historical. Don't import them over a live DB.

## B. Architecture

The app uses flat page scripts. Each top-level `*.php` file is both the controller and the view for one screen.

| Path | Role |
|---|---|
| `index.php` | Staff login (POST handled in the same file, CSRF-checked). `logout.php` is POST-only and destroys the session. |
| `dashboard.php`, `members.php`, `memberships.php`, `payments.php`, `plans.php`, `attendance.php`, `insights.php`, `staff.php`, `gcash_submissions.php`, `member_qr.php` | Staff pages. Pattern: `require config/db.php` + `includes/functions.php` → `require_permission(...)` → handle POST/GET actions (Post/Redirect/Get with `$_SESSION['message']`) → query → `include 'includes/header.php'` → HTML → `include 'includes/footer.php'` → include that page's modals. |
| `gcash_proof.php` | Streams a GCash proof image to staff (`payments.view`) or to the member who owns it. |
| `api/*.php` | JSON read endpoints used by modals (`get_members`, `get_membership`, `get_membership_details`, `get_payment_details`, `get_plan_details`, `get_plan_duration`, `get_staff_details`), plus `toggle_staff_status.php` (POST JSON body) and `update_membership.php` (legacy, not called by the UI). |
| `controllers/*.php` | JSON write endpoints called by `fetch()`: `member_create`, `member_update`, `member_invite`, `member_account_status`, `staff_create`, `staff_update`. **Exception:** `member_fetch.php` and `member_save.php` are *included* by `members.php` and refuse direct requests. |
| `includes/functions.php` | Session start, `check_login()` (authentication), `flash_message()`, `get_members()`, `formatMemberDate()`. Loads `helpers/permission_helper.php`. |
| `includes/helpers/permission_helper.php` | **Authorization**: role → permission map and the `has_permission` / `require_permission` helpers. |
| `includes/helpers/csrf_helper.php` | **CSRF**: one token per session (`csrf_token()`, `csrf_field()`, `verify_csrf_request()`, `require_csrf()`). |
| `includes/helpers/*_modal_helpers.php`, `table_sort_helper.php` | Query and format helpers per domain (staff, plans, payments) and sortable table headers. |
| `includes/header.php` / `footer.php` | Shared layout: sidebar (permission-filtered nav, role name), the CSRF `<meta>` and fetch/form wrapper (header), the global JS includes and the POST logout (footer). |
| `includes/*_modal.php` | Modal markup plus inline JS. They load data from `api/` and submit to the page or to `controllers/`. |
| `includes/member_auth.php`, `member/` | Member portal with its own auth (`check_member_login()`, `$_SESSION['member_*']`). It's separate from staff RBAC. |
| `includes/gcash_submission_helpers.php` | GCash upload validation and private storage. |
| `assets/css/style/` | `style.css` (main design system, including dark mode), plus `admin_refinements.css`, `filter_styles.css`, `table_sort.css`, `payment_modals.css`, `plan_modals.css`, `member_portal.css`. |
| `assets/js/` | `script.js` (global `toast` and `confirm.show()` dialog), `sidebar.js`, `global_filter.js`, `table_sort.js`, membership and payment modal scripts, `capslock_detector.js`. |
| `database-uploads/` | SQL dumps (see above) and additive `migrations/`. `attendance_migration.sql` and `seed_demo_attendance.sql` are in the repo root. |
| `docs/` | Feature docs for the member portal and the GCash workflow. |
| `tests/permission_matrix_check.php` | No-DB self-check of the permission map. |

## C. Database (verified in `gym_db`)

MySQL on Windows is case-insensitive for table names. The code mixes `Staff`/`staff`, `Payment`/`payment`, and so on. Keep that in mind before deploying to Linux.

- **Roles**: `RoleID`, `RoleName`, `Description`, `AccessLevel`, `IsDefault`, `CreatedAt`. Seeded rows: 1 Admin (100), 2 Manager (50), 3 Receptionist (20, `IsDefault`=1). `database-uploads/migrations/rbac_manager_role_description.sql` corrects the Manager description (Managers can't manage staff). `AccessLevel` is only used for display and sorting. **Authorization never uses it.**
- **Staff**: `StaffID`, names, `Email`, `Phone`, `Photo`, `RoleID`→Roles, `Username`, `PasswordHash` (bcrypt), `HireDate`, `Status` enum(`Active`,`Inactive`), `LastLogin`, `CreatedAt`/`UpdatedAt`, `CreatedBy`/`UpdatedBy`→Staff.
- **Member**: personal and contact fields, `JoinDate`, `MembershipStatus`, `Photo`, `CreatedBy`→Staff, `CreatedAt`.
- **Membership**: `MemberID`→Member, `PlanID`→Plan, `StaffID`→Staff, `StartDate`, `EndDate`, `RenewalDate`, `Status` (Active/Pending/Expired/Cancelled). A member's "current" membership is the latest by `StartDate`/`MembershipID`.
- **Plan**: `PlanName`, `Description`, `PlanType` (Days/Months/Years), `Duration`, `Rate`, `IsActive`.
- **Payment**: `MembershipID`→Membership, `PaymentMethodID`→PaymentMethods, `StaffID`→Staff, `PaymentDate`, `AmountPaid`, `ReferenceNumber`, `PaymentStatus`, `Remarks`.
- **PaymentMethods**: `MethodName` (Cash, GCash), `IsActive`, `Description`.
- **Agreement**: `MemberID`→Member, `MembershipID`→Membership, `AgreementDate`, `Terms`, `PhysicalConditionDetails`. A row is created for each new membership in `memberships.php`.
- **Attendance**: `MemberID`, `MembershipID`, `StaffID`, `CheckIn`, `CheckOut`.
- **Member portal**: `MemberAccounts`, `MemberAccountInvitations` (`InvitedBy`→Staff), `MemberLoginAttempts`.
- **GCash**: `GcashPaymentSettings`, `MemberGcashPaymentSubmissions` (`ReviewedBy`→Staff, `PaymentID`→Payment).

## D. Authentication and authorization

**Login (`index.php`):** the form carries `csrf_field()`, and a POST without a valid token is rejected ("Your login page expired"). Then a prepared `SELECT` on `Staff` with `Status = 'Active'`, then `password_verify`. On success it updates `LastLogin`, calls `session_regenerate_id(true)`, rotates the CSRF token (`csrf_rotate()`), sets `last_activity`, `$_SESSION['staff_id']`, `staff_name`, and `role_id`, clears any member-portal session keys, and redirects to `dashboard.php`.

**Logout (`logout.php`):** POST only, with a valid CSRF token. The sidebar "Log Out" button (`logoutNow()` in `footer.php`) builds and submits that form. A GET just redirects without logging out. A valid POST clears `$_SESSION`, calls `session_destroy()`, then starts a fresh session with a new ID only to carry the toast message, so the old cookie can't be reused.

**`check_login()` (authentication):** re-reads the staff row and role from the DB on **every request** (`current_staff()`, cached per request). The session is wiped, and the user is sent to the login page (pages) or gets a 401 JSON response (endpoints), when:
- the account was deleted or set to Inactive, or
- the session was idle longer than `SESSION_IDLE_TIMEOUT` (7200 s, in `functions.php`).

A role change applies on the next request. `$_SESSION['role_id']` and `staff_name` are refreshed, but **don't authorize from the session**. Always use the helpers below. For POST and other non-GET requests, `check_login()` also enforces CSRF (see below).

**Session security** (top of `includes/functions.php`, applied when it starts the session):
- Cookie: `HttpOnly`, `SameSite=Lax`, and `Secure` **only when the request is HTTPS** (`$_SERVER['HTTPS']` or port 443), so plain `http://localhost` XAMPP still works. Behind a TLS-terminating proxy, make sure `HTTPS=on` reaches PHP or the cookie won't be marked Secure.
- `session.use_strict_mode=1` (session IDs the server never issued are ignored, which blocks fixation via a chosen ID). `session.gc_maxlifetime` is set to the idle timeout.
- The ID is regenerated at login and at logout, and when a session is ended for inactivity or deactivation.
- The member portal (`member/`, `includes/member_auth.php`) starts its own session and keeps its own `member_csrf_token`. It's not covered by these staff settings.

**CSRF (`includes/helpers/csrf_helper.php`):**
- One random token per session: `$_SESSION['csrf_token']` = `bin2hex(random_bytes(32))`, compared with `hash_equals`. Rotated at login. Never put it in a URL.
- **Server:** `check_login()` → `require_csrf()` rejects every non-GET/HEAD/OPTIONS request without a valid token in the `X-CSRF-Token` header or a `csrf_token` POST field. Every endpoint that calls `require_permission()` is therefore covered automatically, with no per-endpoint code. Failures return 403, as JSON `{"success":false,"message":"Your security token is missing or expired. Refresh the page and try again."}` for endpoints and fetch requests, or the in-app "Access denied" page for plain forms. `index.php` and `logout.php` call `verify_csrf_request()` themselves because they don't use `check_login()`.
- **Browser:** `header.php` prints `<meta name="csrf-token">` and a small script that:
  - adds `X-CSRF-Token` plus `X-Requested-With: XMLHttpRequest` to every **same-origin, non-GET `fetch()`** (so existing modals need no changes, and `X-Requested-With` makes errors come back as JSON);
  - appends a hidden `csrf_token` input to every `method="post"` form on `DOMContentLoaded` and on submit (so programmatic `form.submit()` also works, as in attendance).
- **PHP forms:** add `<?= csrf_field() ?>` inside the `<form method="post">`. It's required on pages that don't include `header.php` (for example `index.php`) and recommended everywhere for clarity.
- **fetch/AJAX:** on pages with `header.php`, just call `fetch(url, {method: 'POST', body})` and the wrapper adds the header. Outside such pages, send `headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content}`, or include `csrf_token` in the form body. `XMLHttpRequest` is **not** wrapped, so add the header manually if you ever use it.
- **State changes must be POST.** GET requests are not CSRF-checked, so never change data on GET (plan activate/deactivate and logout were converted to POST for this reason).

**Authorization (`includes/helpers/permission_helper.php`):**
- `ROLE_PERMISSIONS`: role name → list of permission strings. `'*'` means everything (Admin only). An unknown or renamed role gets nothing (fails closed).
- `has_permission($p)`: bool, for UI decisions.
- `require_permission($p)` / `require_any_permission([...])`: call `check_login()` first, then deny with 403.
- `get_current_user_role()`, `is_admin()`, `is_manager()`, `is_receptionist()`.
- `deny_access()`: returns 403. Endpoints in `api/` or `controllers/`, or any response that already sent `Content-Type: application/json`, get `{"success":false,"message":"You do not have permission to perform this action."}`. Pages get an in-app "Access denied" page.
- `describe_role_permissions($role)`: the "Can… / Cannot…" list shown in the staff view modal (from `PERMISSION_LABELS`).

**Permission matrix:**

| Permission | Guards | Admin | Manager | Receptionist |
|---|---|:-:|:-:|:-:|
| `dashboard.view` | dashboard.php | ✓ | ✓ | ✓ |
| `members.view` | members.php, member_qr.php, api/get_members | ✓ | ✓ | ✓ |
| `members.create` | member_create, members.php add | ✓ | ✓ | ✓ |
| `members.edit` | member_update, member_invite, member_account_status | ✓ | ✓ | ✓ |
| `memberships.view` | memberships.php, api/get_membership(_details) | ✓ | ✓ | ✓ |
| `memberships.create` | memberships.php POST without `membership_id` (add/renew) | ✓ | ✓ | ✓ |
| `memberships.edit` | memberships.php POST with `membership_id`, `?action=edit`, api/update_membership | ✓ | ✓ | ✗ |
| `payments.view` | payments.php, gcash_submissions.php, gcash_proof.php, api/get_payment_details | ✓ | ✓ | ✓ |
| `payments.create` | payments.php POST without `payment_id`, GCash approve/reject | ✓ | ✓ | ✓ |
| `payments.edit` | payments.php POST with `payment_id` | ✓ | ✓ | ✗ |
| `plans.view` | plans.php (read-only), api/get_plan_details, api/get_plan_duration | ✓ | ✓ | ✓ |
| `plans.manage` | plans.php POST and `?action=toggle_status` | ✓ | ✓ | ✗ |
| `attendance.manage` | attendance.php | ✓ | ✓ | ✓ |
| `insights.view` | insights.php | ✓ | ✓ | ✗ |
| `staff.view` | staff.php, api/get_staff_details | ✓ | ✗ | ✗ |
| `staff.create` (+ `staff.assign_role`) | controllers/staff_create | ✓ | ✗ | ✗ |
| `staff.edit` | controllers/staff_update | ✓ | ✗ | ✗ |
| `staff.assign_role` | staff_update when `role_id` changes | ✓ | ✗ | ✗ |
| `staff.toggle_status` | api/toggle_staff_status, staff_update when `status` changes | ✓ | ✗ | ✗ |

**Staff safeguards** (`includes/helpers/staff_modal_helpers.php`): `wouldRemoveLastAdmin()` blocks any demotion or deactivation that would leave zero active Admins, whether it comes through toggle or edit. Staff can't deactivate their own account. `role_id` must exist in `Roles`, and `status` must be `Active` or `Inactive`.

**Protecting a new page:**
```php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_permission('thing.view');            // page access
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('thing.manage');      // each write action checked separately
    ...
}
```
Then add the link to `$nav_items` in `includes/header.php` with the same permission, and wrap action buttons in `<?php if (has_permission('thing.manage')): ?>`.

**Protecting an API or controller:** use the same calls (`require_once '../config/db.php'; require_once '../includes/functions.php'; require_permission('...');`). Because the file is in `api/` or `controllers/`, denials come back as JSON 401/403. For a JSON response from a root page, send `header('Content-Type: application/json')` **before** `require_permission` (fetch calls already get JSON via `X-Requested-With`). Writes must check the method (`if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 405 }`) so they're never reachable by GET. CSRF is then enforced automatically. Don't add a second, per-feature token.

**Adding a permission:** add it to the right roles in `ROLE_PERMISSIONS`, add a label in `PERMISSION_LABELS`, and add a row to `$expected` in `tests/permission_matrix_check.php`. Admin gets it automatically through `'*'`.

**Changing a role's permissions:** edit `ROLE_PERMISSIONS` (it's code, so it goes through review). No DB change is needed. Update the matrix above and the test. Role identity is `Roles.RoleName`, so renaming a role in the DB removes its permissions until the map is updated.

## E. Coding conventions (as found in the code)

- PDO only, always `prepare()` + `execute([...])` with `?` placeholders. Dynamic `ORDER BY` columns must come from an allow-list (see `get_members()` and `table_sort_helper.php`).
- Pages handle their own POST at the top and use Post/Redirect/Get: set `$_SESSION['message']` and `message_type`, `header('Location: ...')`, `exit()`. `flash_message()` renders it in the header.
- JSON endpoints return `{"success": bool, "message": string, ...data}` (legacy `get_members.php` returns `{member, memberships, payments}`, and `update_membership.php` uses `status`). JS shows results with `toast.success/error` and asks via `await confirm.show({...})` from `assets/js/script.js`.
- **`payments.php` POST contract** (used by the add and edit payment modals): HTTP 200 `{"success":true,"message"}` on success. Otherwise `422` (invalid amount, status, method, or membership), `404` (payment no longer exists), `403`/`401` (permission, CSRF, or session), or `500` (logged with `error_log`, generic message), each with `{"success":false,"message":"<text to show>"}`. Clients must parse the JSON and check `response.ok && result.success`. Don't expect a redirect. Edits are idempotent UPDATEs by `PaymentID`. Raw exception text is never returned.
- **`toast` and `confirm` are top-level `const`s in `assets/js/script.js`, not `window` properties.** `window.toast` / `window.confirm` checks are always false or undefined. Guard with `typeof toast !== 'undefined'`. The global `confirm` is the custom `ConfirmDialog` and **shadows the browser's `confirm()`**, so `onclick="return confirm('…')"` throws and the form submits unconfirmed. Use `await confirm.show({title, message, confirmText, cancelText, type})`, which resolves `true`/`false`.
- **Confirming a form submit** (pattern in `gcash_submissions.php`): in a `submit` listener, call `e.preventDefault()`, read `e.submitter`, `await confirm.show(...)`, and return on cancel. On confirm, set a "submitting" flag, copy the clicked button's `name`/`value` into a hidden input (because `form.submit()` doesn't send the submitter), disable the buttons, then call `form.submit()`. The form's `csrf_token` field travels with it.
- Page filter dropdowns use `assets/js/global_filter.js`: `onclick="toggleFilter('<key>')"` on the button and `id="filterDropdown_<key>"` on the content. Don't redefine `toggleFilter` or declare `searchTimeout`/`currentOpenFilter` in page scripts, because a duplicate top-level `let` makes the whole `global_filter.js` fail to load.
- Values from the request that go into inline `<script>` must use `json_encode(..., JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)`, never a bare `<?= $var ?>`.
- CSRF is centralized (section D). Use `csrf_field()` in forms. Don't create per-feature tokens (the old `attendance_csrf`, `member_invite_csrf`, and `gcash_staff_csrf` were folded into `csrf_token()`; `member_invite.php` and `member_account_status.php` still return `csrf_token` from GET for their existing JS).
- Escape all output with `htmlspecialchars()`. When putting values into inline JS (`onclick`), use `htmlspecialchars(json_encode($v), ENT_QUOTES, 'UTF-8')`, not a quoted string.
- Uploads: validate extension, size, and (for GCash) MIME type. Use generated filenames. GCash proofs are stored outside the web root.
- Styling: reuse existing classes (`page-header`, `page-title`, `page-actions`, `members-table`, `action-btn`, `add-member-btn`, `status-badge`, `role-badge`, `dashboard-card`, `*-modern` modal classes). Dark mode is driven by `body.dark-mode`, which redefines the theme variables (`--text-primary`, `--text-secondary`, `--bg-primary`, `--bg-secondary`, `--border-color`, …) in `style.css`. New markup, including inline styles and JS-built HTML, must use those variables, never hard-coded hex text, background, or border colours, or it will be unreadable in dark mode. Check new UI with dark mode on (`localStorage.darkMode = 'enabled'`).

## F. Security rules (always follow)

- Never trust client-side permissions. Every page, API, controller, and write action must call `require_permission()` on the server. Hiding a button doesn't count as protection.
- Every state-changing request must be POST and pass the central CSRF check. Never change data on GET, and never weaken or bypass `require_csrf()`.
- Don't weaken the session settings (HttpOnly, SameSite, strict mode, regeneration at login and logout, idle timeout, per-request account and role check).
- Never authorize from `$_SESSION['role_id']`, `AccessLevel`, or any request field such as a posted role, staff ID, or permission name. Validate posted IDs against the DB.
- Use prepared statements. Escape user-generated output. Validate input at every endpoint.
- Don't expose passwords, hashes, or internal details in responses (some older endpoints still echo `$e->getMessage()`; don't copy that pattern).
- Don't weaken authentication or authorization, and don't remove the last-admin safeguard.
- Don't run destructive DB operations (DROP, TRUNCATE, mass DELETE or UPDATE, reseeding, or re-importing a dump over `gym_db`) without explicit approval. Schema changes go in a new additive file under `database-uploads/migrations/`.

## G. Development workflow

1. Read the relevant files (page, its modals, its api/controllers, its helper) before editing.
2. Understand and reuse the existing helpers and components. Don't add parallel ones.
3. Keep changes minimal. Avoid refactors and redesigns nobody asked for.
4. Preserve existing behavior for all three roles.
5. Explain any migration: what it changes, that it's additive, and how to apply it (`mysql -uroot gym_db < file.sql` or phpMyAdmin import, after a backup).
6. Verify:
   - `C:\xampp\php\php.exe -l <file>` on every changed PHP file.
   - `C:\xampp\php\php.exe tests\permission_matrix_check.php` after any permission change.
   - Exercise changed endpoints with `curl` as each role. Do it against a **copy** of the database and code, never `gym_db`, because tests change data:
     ```bash
     mysql -uroot -e "CREATE DATABASE gym_db_test"; mysqldump -uroot gym_db | mysql -uroot gym_db_test
     # copy the code elsewhere, point its config/db.php at gym_db_test, then:
     C:\xampp\php\php.exe -S 127.0.0.1:8099 -t <copy>
     ```
     Scripted logins must first GET `index.php` and post its `csrf_token` field. Later POSTs need the token from `<meta name="csrf-token">`, sent as the `X-CSRF-Token` header (AJAX-style) or the `csrf_token` field (form-style). Cover these cases: missing token, invalid token, another session's token, the wrong role with a valid token, plus logout and idle expiry (edit `last_activity` in `C:\xampp\tmp\sess_<id>`). Drop the test DB afterwards.
   - For UI changes, a real-browser pass is possible here: Python Playwright is installed and can drive the installed Chrome (`p.chromium.launch(channel="chrome")`). Test the confirm flows by clicking `#confirmOk` / `#confirmCancel` (the custom dialog). Pages reload about 1 s after a successful save, so capture toasts with a `MutationObserver` init script that stores `.toast` `textContent` in `sessionStorage`, and wait for the reload (`page.expect_navigation()`) before navigating again. Check the database for every write you exercise. The only known remaining console error is the `/favicon.ico` 404 (the repo has no favicon asset).
7. Summarize every modified and created file.
8. Clearly list what still needs manual browser testing.

## H. Project-specific rules

- This is an existing PHP/MySQL app. Don't migrate it to a framework, replace PDO, add Composer or npm dependencies, or introduce a frontend framework.
- Keep the existing design system, sidebar, and modal patterns. No unrelated redesigns.
- Don't duplicate helpers. Authentication lives in `includes/functions.php`, authorization in `includes/helpers/permission_helper.php`, and the member portal auth in `includes/member_auth.php`.
- Keep the three roles (Admin, Manager, Receptionist) and their RoleIDs (1, 2, 3).
