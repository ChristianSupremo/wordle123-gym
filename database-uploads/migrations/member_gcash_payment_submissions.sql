-- NEWYOU manual GCash submission and staff review workflow.
-- Additive migration. Run after the member portal Phase 2 migrations.
-- Does not alter Payment.StaffID or create a Payment row until staff approval.

CREATE TABLE IF NOT EXISTS GcashPaymentSettings (
    SettingsID TINYINT UNSIGNED NOT NULL,
    RecipientName VARCHAR(120) NOT NULL DEFAULT '',
    MobileNumber VARCHAR(30) NOT NULL DEFAULT '',
    Instructions TEXT NULL,
    UpdatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (SettingsID),
    CONSTRAINT chk_gcash_settings_singleton CHECK (SettingsID = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO GcashPaymentSettings (SettingsID, RecipientName, MobileNumber, Instructions)
VALUES (1, '', '', 'Send the exact amount to the recipient above. Keep your GCash receipt and submit a clear screenshot for manual review.')
ON DUPLICATE KEY UPDATE SettingsID = VALUES(SettingsID);

CREATE TABLE IF NOT EXISTS MemberGcashPaymentSubmissions (
    SubmissionID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MemberID INT(11) NOT NULL,
    MembershipID INT(11) NOT NULL,
    AmountPaid DECIMAL(10,2) NOT NULL,
    ReferenceNumber VARCHAR(100) NOT NULL,
    ProofFileName VARCHAR(80) NOT NULL,
    Status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    StaffRemarks TEXT NULL,
    SubmittedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ReviewedAt DATETIME NULL,
    ReviewedBy INT(11) NULL,
    PaymentID INT(11) NULL,
    PRIMARY KEY (SubmissionID),
    UNIQUE KEY uq_gcash_submission_reference (ReferenceNumber),
    UNIQUE KEY uq_gcash_submission_payment (PaymentID),
    KEY idx_gcash_submission_member_date (MemberID, SubmittedAt),
    KEY idx_gcash_submission_status_date (Status, SubmittedAt),
    KEY idx_gcash_submission_membership_status (MembershipID, Status),
    KEY idx_gcash_submission_reviewer (ReviewedBy),
    CONSTRAINT chk_gcash_submission_amount CHECK (AmountPaid > 0),
    CONSTRAINT fk_gcash_submission_member FOREIGN KEY (MemberID) REFERENCES member (MemberID) ON DELETE RESTRICT,
    CONSTRAINT fk_gcash_submission_membership FOREIGN KEY (MembershipID) REFERENCES membership (MembershipID) ON DELETE RESTRICT,
    CONSTRAINT fk_gcash_submission_reviewer FOREIGN KEY (ReviewedBy) REFERENCES staff (StaffID) ON DELETE RESTRICT,
    CONSTRAINT fk_gcash_submission_payment FOREIGN KEY (PaymentID) REFERENCES payment (PaymentID) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
