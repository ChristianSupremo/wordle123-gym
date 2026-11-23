# NEWYOU Member Portal — Phase 1

## Scope

Phase 1 establishes a separate member authentication path while preserving the existing staff login and staff session model.

Included:
- Additive database migration for member accounts and one-time invitations.
- Staff-authenticated invitation endpoint.
- Invitation activation with a 48-hour expiry and one-time token.
- Password hashing using PHP `password_hash()`.
- Member sign-in and sign-out.
- Separate member session identifiers.

Not included yet:
- Staff UI button for creating/sending invitations.
- SMTP/email delivery. The staff-only endpoint returns a relative activation path that must currently be delivered manually.
- Member dashboard and member-facing data pages.
- Payment submission and approval workflow.

## Database audit

The base schema defines:
- `member.MemberID` as `INT(11)` primary key.
- `member.Email` as `VARCHAR(100)`, nullable and not unique.
- `staff.StaffID` as `INT(11)` primary key.
- `membership.MemberID` references `member.MemberID`.
- `payment.MembershipID` references `membership.MembershipID`.
- Attendance is added separately by `attendance_migration.sql`, with `attendance.MemberID` referencing `member.MemberID`.

The new migration therefore uses matching integer foreign keys and enforces uniqueness at the account layer, not on the existing member email column. A member email must be valid before staff can invite the member.

## Authentication audit

The existing `index.php` authenticates staff against `Staff.Username` and `Staff.PasswordHash`, checks `Status = 'Active'`, and sets `staff_id`, `staff_name`, and `role_id` session values. `includes/functions.php` starts the PHP session and `check_login()` protects staff pages.

The existing staff login is intentionally unchanged in Phase 1. Members use `member/login.php`, `member/activate.php`, and `member/logout.php`. Member pages must call `check_member_login()` and scope every query using the server-side `$_SESSION['member_id']`.

## Installation

1. Back up the `gym_db` database.
2. Confirm the base schema and attendance migration have been applied.
3. Run `database-uploads/migrations/member_accounts.sql` in phpMyAdmin against `gym_db`.
4. Open `member/login.php` for member sign-in.
5. To create an invitation, send a POST request to `controllers/member_invite.php` from an authenticated staff session with `member_id` and the session's `member_invite_csrf` token. The response includes a relative `activation_path`; deliver it only to the member's verified email.

## Security notes / follow-up

- Invitation tokens are generated with `random_bytes(32)`; only SHA-256 token hashes are stored.
- Invitations expire after 48 hours and are consumed transactionally.
- Passwords are stored using `PASSWORD_DEFAULT`.
- The invitation endpoint is staff-session protected and CSRF protected.
- Configure HTTPS and secure PHP session cookie settings before production deployment.
- Add rate limiting / login throttling and email delivery before public deployment.
- Consider adding a staff UI for invitation creation and an audit log for invitation resend/revoke actions.
- Do not expose raw member IDs as authorization. Member-facing SQL must always use the authenticated session's member ID.
