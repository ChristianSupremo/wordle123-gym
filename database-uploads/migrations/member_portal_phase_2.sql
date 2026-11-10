-- NEWYOU Member Portal - Phase 2
-- Run against gym_db AFTER member_accounts.sql (phase 1). Additive only.

-- Failed/successful member sign-in attempts, used for login throttling.
CREATE TABLE IF NOT EXISTS MemberLoginAttempts (
    AttemptID INT(11) NOT NULL AUTO_INCREMENT,
    Username VARCHAR(100) NOT NULL,
    IPAddress VARCHAR(45) NOT NULL,
    Success TINYINT(1) NOT NULL DEFAULT 0,
    AttemptedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (AttemptID),
    KEY idx_member_login_user_time (Username, AttemptedAt),
    KEY idx_member_login_ip_time (IPAddress, AttemptedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
