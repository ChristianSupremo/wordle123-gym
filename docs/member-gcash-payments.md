# NEWYOU manual GCash payment submission

This feature adds a manual, staff-reviewed GCash workflow to the Phase 2 member portal. It does not connect to a GCash API, automatically verify transfers, or activate/extend memberships.

## Files added or updated

- `member/payments.php`: member form, submission status, and existing payment history.
- `gcash_submissions.php`: authenticated staff review queue and approve/reject actions.
- `gcash_proof.php`: controlled proof-image delivery; members can only view their own proof.
- `includes/gcash_submission_helpers.php`: private upload validation/storage and settings lookup.
- `database-uploads/migrations/member_gcash_payment_submissions.sql`: additive settings and submission tables.
- `includes/header.php`: staff navigation link to the review queue.

## Installation

1. Back up `gym_db`.
2. Apply the member-account and member-portal Phase 2 migrations first.
3. Run `database-uploads/migrations/member_gcash_payment_submissions.sql` against `gym_db`.
4. Configure the actual gym recipient details. Replace the sample values below with the gym's verified GCash details; do not use invented account information:

   ```sql
   UPDATE GcashPaymentSettings
   SET RecipientName = 'YOUR VERIFIED GCash RECIPIENT NAME',
       MobileNumber = 'YOUR VERIFIED GCash MOBILE NUMBER',
       Instructions = 'Send the exact amount. Save your receipt and upload a clear screenshot for manual review.'
   WHERE SettingsID = 1;
   ```

5. Configure a writable directory outside the public web root for uploaded proofs. Set `NEWYOU_PRIVATE_UPLOAD_DIR` in the PHP-FPM/Apache environment to an absolute path, for example `/var/www/newyou_private/gcash_proofs`. Ensure the PHP worker can write to it. The application rejects a configured path inside `DOCUMENT_ROOT`.
6. Set PHP upload limits to at least `upload_max_filesize=6M` and `post_max_size=8M`. The application enforces a 5 MB per-image limit.
7. Confirm that `paymentmethods` contains an active row named exactly `GCash`. The migration does not invent or insert payment method records.

## Workflow and safeguards

- The member ID is always taken from the authenticated `$_SESSION['member_id']`; request-supplied member IDs are ignored.
- A member may submit for their own Active, Pending, or Expired membership. Cancelled memberships are not eligible.
- A membership may have only one Pending submission at a time. The membership row is locked while checking and inserting to prevent concurrent duplicate submissions. A GCash reference number can only be submitted once.
- Proof images are restricted to JPEG, PNG, or WebP, max 5 MB, MIME-sniffed server-side, checked as raster images, assigned random server-side filenames, and stored outside the public web root.
- `gcash_proof.php` checks staff authentication or the owning member session before streaming an image. Changing the URL ID does not grant access.
- Member and staff forms use CSRF tokens. Database queries use prepared statements and output is escaped.
- A submission remains Pending until staff review. Approval uses a transaction and row lock, creates one Completed `payment` row with the reviewing staff ID and active GCash method, then links that payment back to the submission. Repeated approval is rejected.
- Rejection records staff remarks and review timestamps but creates no payment row.
- Membership status and dates are not changed.

## Local test plan

1. Sign in as a member with an eligible membership. Submit a valid GCash reference, amount, and image. Confirm it appears as Pending and no `payment` row was created.
2. Try a different member's MembershipID in the form request. Confirm submission is rejected and no row is created.
3. Try missing/invalid CSRF, empty/zero amount, blank/oversized reference, unsupported file, spoofed MIME, corrupt image, and image larger than 5 MB. Confirm each request is rejected and no submission remains.
4. Submit twice for the same membership while the first is Pending. Confirm the second is rejected. Reuse a reference number and confirm the unique constraint rejects it.
5. Sign in as staff. Confirm the submission appears in `gcash_submissions.php` with member, plan, amount, reference, submission date, and proof image.
6. Attempt to open another member's proof using a member session and a changed URL ID. Confirm HTTP 404. Confirm staff can view the queue's proof.
7. Approve once. Confirm exactly one `payment` row exists with the correct MembershipID, active GCash PaymentMethodID, amount, reference, reviewing StaffID, and Completed status; confirm the submission is Approved and linked to that PaymentID.
8. Attempt a second approval or a stale approval POST. Confirm it is rejected and no second payment row is created.
9. Reject a separate Pending submission with remarks. Confirm the member sees Rejected, the remarks and review date, and no payment row exists.
10. Confirm membership status/dates are unchanged and existing staff payment records and member portal pages still load.

## Verification status

The repository changes were prepared against `feat/member-portal-phase-2`. Execute the local PHP/database test plan above and attach screenshots of the member form and staff queue before marking the pull request ready for review.
