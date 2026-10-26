-- =====================================================
-- GYM MANAGEMENT SYSTEM - DATABASE STRUCTURE
-- Complete Schema with All Tables and Relationships
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- CREATE DATABASE (if needed)
-- =====================================================
-- CREATE DATABASE IF NOT EXISTS `gym_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- USE `gym_db`;

-- =====================================================
-- TABLE: roles
-- =====================================================
CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL AUTO_INCREMENT,
  `RoleName` varchar(50) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `AccessLevel` int(11) NOT NULL DEFAULT 1,
  `IsDefault` tinyint(1) DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLE: staff
-- =====================================================
CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Photo` varchar(255) DEFAULT NULL,
  `RoleID` int(11) DEFAULT NULL,
  `Username` varchar(100) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `HireDate` date NOT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `LastLogin` datetime DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `CreatedBy` int(11) DEFAULT NULL,
  `UpdatedBy` int(11) DEFAULT NULL,
  PRIMARY KEY (`StaffID`),
  UNIQUE KEY `Email` (`Email`),
  KEY `fk_staff_role` (`RoleID`),
  KEY `fk_staff_created_by` (`CreatedBy`),
  KEY `fk_staff_updated_by` (`UpdatedBy`),
  CONSTRAINT `fk_staff_created_by` FOREIGN KEY (`CreatedBy`) REFERENCES `staff` (`StaffID`),
  CONSTRAINT `fk_staff_role` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`),
  CONSTRAINT `fk_staff_updated_by` FOREIGN KEY (`UpdatedBy`) REFERENCES `staff` (`StaffID`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLE: member
-- =====================================================
CREATE TABLE `member` (
  `MemberID` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `Zipcode` varchar(20) DEFAULT NULL,
  `Gender` enum('Male','Female','Other') DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `PhoneNo` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `EmergencyContactName` varchar(100) DEFAULT NULL,
  `EmergencyContactNumber` varchar(20) DEFAULT NULL,
  `MembershipStatus` enum('Active','Inactive','Pending') DEFAULT 'Active',
  `Photo` varchar(255) DEFAULT NULL,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MemberID`),
  KEY `fk_member_createdby` (`CreatedBy`),
  CONSTRAINT `fk_member_createdby` FOREIGN KEY (`CreatedBy`) REFERENCES `staff` (`StaffID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLE: plan
-- =====================================================
CREATE TABLE `plan` (
  `PlanID` int(11) NOT NULL AUTO_INCREMENT,
  `PlanName` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `PlanType` varchar(50) DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `Rate` decimal(10,2) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`PlanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLE: paymentmethods
-- =====================================================
CREATE TABLE `paymentmethods` (
  `PaymentMethodID` int(11) NOT NULL AUTO_INCREMENT,
  `MethodName` varchar(50) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`PaymentMethodID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLE: membership
-- =====================================================
CREATE TABLE `membership` (
  `MembershipID` int(11) NOT NULL AUTO_INCREMENT,
  `MemberID` int(11) NOT NULL,
  `PlanID` int(11) DEFAULT NULL,
  `StaffID` int(11) NOT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `RenewalDate` date DEFAULT NULL,
  `Status` enum('Active','Expired','Cancelled','Pending') DEFAULT 'Active',
  PRIMARY KEY (`MembershipID`),
  KEY `MemberID` (`MemberID`),
  KEY `PlanID` (`PlanID`),
  KEY `StaffID` (`StaffID`),
  CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`),
  CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`PlanID`) REFERENCES `plan` (`PlanID`),
  CONSTRAINT `membership_ibfk_3` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLE: payment
-- =====================================================
CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
  `MembershipID` int(11) NOT NULL,
  `PaymentMethodID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `PaymentDate` datetime DEFAULT current_timestamp(),
  `AmountPaid` decimal(10,2) NOT NULL,
  `ReferenceNumber` varchar(100) DEFAULT NULL,
  `PaymentStatus` enum('Pending','Completed','Failed','Refunded') DEFAULT 'Completed',
  `Remarks` text DEFAULT NULL,
  PRIMARY KEY (`PaymentID`),
  KEY `MembershipID` (`MembershipID`),
  KEY `PaymentMethodID` (`PaymentMethodID`),
  KEY `StaffID` (`StaffID`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`MembershipID`) REFERENCES `membership` (`MembershipID`),
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`PaymentMethodID`) REFERENCES `paymentmethods` (`PaymentMethodID`),
  CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABLE: agreement
-- =====================================================
CREATE TABLE `agreement` (
  `AgreementID` int(11) NOT NULL AUTO_INCREMENT,
  `MemberID` int(11) NOT NULL,
  `MembershipID` int(11) NOT NULL,
  `AgreementDate` date DEFAULT NULL,
  `Terms` text DEFAULT NULL,
  `PhysicalConditionDetails` text DEFAULT NULL,
  PRIMARY KEY (`AgreementID`),
  KEY `MemberID` (`MemberID`),
  KEY `MembershipID` (`MembershipID`),
  CONSTRAINT `agreement_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`),
  CONSTRAINT `agreement_ibfk_2` FOREIGN KEY (`MembershipID`) REFERENCES `membership` (`MembershipID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================
-- DATABASE STRUCTURE COMPLETE
-- =====================================================
-- 
-- Tables created: 8
--   1. roles - User role definitions
--   2. staff - Staff/admin accounts
--   3. member - Gym members
--   4. plan - Membership plans
--   5. paymentmethods - Payment method options
--   6. membership - Member subscriptions
--   7. payment - Payment transactions
--   8. agreement - Membership agreements
--
-- All foreign key relationships established
-- Ready for data import
-- =====================================================