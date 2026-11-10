# NEWYOU Member Portal - Phase 2

Builds on Phase 1 (member accounts, invitations, sign-in). Phase 2 adds the member-facing portal itself, plus staff controls for portal access.

## What's new

**Member portal (`/member/`)** - same layout, sidebar, cards, tables, badges, toasts and dark mode as the admin side (it reuses `style.css`, `script.js` and the admin sidebar markup).

| Page | What it shows |
| --- | --- |
| `dashboard.php` | Membership status, days left, visits this month, total paid, current plan progress, recent visits and payments, expiry notices |
| `membership.php` | Current plan details and full membership history |
| `payments.php` | Payment history (read-only) and totals |
| `attendance.php` | Visit history with check-in/out and duration, monthly/weekly counts |
| `qr.php` | The member's own check-in QR code (`GYM-<id>`, same payload the front-desk kiosk scans) |
| `profile.php` | Account info (read-only), editable contact/emergency details, change password |

Members are read-only on memberships, payments and attendance. They can only edit phone, address, city, province, zip code and emergency contact. Name, email, date of birth and status stay staff-managed.

**Sign-in hardening**
- Throttling: 5 failed attempts per email or 20 per IP in 15 minutes locks sign-in for the remainder of that window (`MemberLoginAttempts` table).
- Every member page re-checks that the account is still `Active`, so disabling an account takes effect on the next request.
- Member login clears any staff session keys and staff login clears member keys.
- `member/login.php` and `member/activate.php` now use the admin login layout.

**Staff side**
- `members.php` has a new "portal access" button per member to enable/disable their portal account (`controllers/member_account_status.php`, CSRF protected, staff-session only).

## Installation

1. Back up `gym_db`.
2. Make sure Phase 1's `database-uploads/migrations/member_accounts.sql` has been applied.
3. Run `database-uploads/migrations/member_portal_phase_2.sql` in phpMyAdmin against `gym_db`. (The portal works without it, but login throttling is skipped.)
4. Members sign in at `member/login.php` after activating via a staff-created invitation link.

## Security model

- Every member query uses `$_SESSION['member_id']` only. No member-facing endpoint accepts a member ID from the request.
- All POST forms use the member CSRF token. Output is escaped with `member_h()`.
- Passwords use `password_hash()` / `password_verify()`; changing a password regenerates the session ID.

## Not included yet

- Forgot-password / email delivery (invitation links are still shared manually).
- Online payment submission and approval workflow.
- Profile photo upload.
- Staff-side password reset for members.
