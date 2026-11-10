-- NEWYOU Member Portal - Phase 1
-- Run against gym_db after the base schema and attendance migration.
-- This migration is additive: it does not modify existing staff/member records.

CREATE TABLE IF NOT EXISTS MemberAccounts (
    MemberAccountID INT(11) NOT NULL AUTO_INCREMENT,
    MemberID INT(11) NOT NULL,
    Username VARCHAR(100) NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    AccountStatus ENUM('Active', 'Disabled') NOT NULL DEFAULT 'Active',
    LastLogin DATETIME DEFAULT NULL,
    CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MemberAccountID),
    UNIQUE KEY uq_member_accounts_member (MemberID),
    UNIQUE KEY uq_member_accounts_username (Username),
    CONSTRAINT fk_member_accounts_member
        FOREIGN KEY (MemberID) REFERENCES member(MemberID)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS MemberAccountInvitations (
    InvitationID INT(11) NOT NULL AUTO_INCREMENT,
    MemberID INT(11) NOT NULL,
    TokenHash CHAR(64) NOT NULL,
    ExpiresAt DATETIME NOT NULL,
    UsedAt DATETIME DEFAULT NULL,
    InvitedBy INT(11) DEFAULT NULL,
    CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (InvitationID),
    UNIQUE KEY uq_member_invitation_token (TokenHash),
    KEY idx_member_invitation_member (MemberID),
    KEY idx_member_invitation_expiry (ExpiresAt),
    CONSTRAINT fk_member_invitation_member
        FOREIGN KEY (MemberID) REFERENCES member(MemberID)
        ON DELETE CASCADE,
    CONSTRAINT fk_member_invitation_staff
        FOREIGN KEY (InvitedBy) REFERENCES staff(StaffID)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
