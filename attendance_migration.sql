-- Run once in phpMyAdmin (database: gym_db), after the base gym schema.
CREATE TABLE IF NOT EXISTS `attendance` (
  `AttendanceID` int(11) NOT NULL AUTO_INCREMENT,
  `MemberID` int(11) NOT NULL,
  `MembershipID` int(11) DEFAULT NULL,
  `CheckIn` datetime NOT NULL DEFAULT current_timestamp(),
  `CheckOut` datetime DEFAULT NULL,
  `StaffID` int(11) DEFAULT NULL,
  PRIMARY KEY (`AttendanceID`),
  KEY `idx_att_member_checkin` (`MemberID`,`CheckIn`),
  KEY `idx_att_checkin` (`CheckIn`),
  CONSTRAINT `fk_att_member` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_membership` FOREIGN KEY (`MembershipID`) REFERENCES `membership` (`MembershipID`) ON DELETE SET NULL,
  CONSTRAINT `fk_att_staff` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
