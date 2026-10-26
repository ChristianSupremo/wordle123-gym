-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 02:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agreement`
--

CREATE TABLE `agreement` (
  `AgreementID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `MembershipID` int(11) NOT NULL,
  `AgreementDate` date DEFAULT NULL,
  `Terms` text DEFAULT NULL,
  `PhysicalConditionDetails` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agreement`
--

INSERT INTO `agreement` (`AgreementID`, `MemberID`, `MembershipID`, `AgreementDate`, `Terms`, `PhysicalConditionDetails`) VALUES
(1, 2, 1, '2025-11-19', 'Standard gym membership agreement.', NULL),
(3, 1, 3, '2025-11-19', 'Standard gym membership agreement.', NULL),
(4, 2, 4, '2025-12-03', 'Standard gym membership agreement.', NULL),
(5, 3, 5, '2025-12-03', 'Standard gym membership agreement.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `MemberID` int(11) NOT NULL,
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
  `Photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`MemberID`, `FirstName`, `LastName`, `Address`, `City`, `Province`, `Zipcode`, `Gender`, `DateOfBirth`, `PhoneNo`, `Email`, `JoinDate`, `EmergencyContactName`, `EmergencyContactNumber`, `MembershipStatus`, `Photo`) VALUES
(1, 'Jane', 'Smith', '456 Oak Ave', 'Anytown', 'ON', 'K1A 0B1', 'Female', '1990-05-15', '555-123-4568', 'jane.smith@email.com', '2025-11-19', 'John Smith', '555-123-4569', 'Inactive', NULL),
(2, 'Peter', 'Jones', '789 Pine St', 'Sometown', 'BC', 'V6A 1B2', 'Male', '1985-11-22', '555-987-6543', 'peter.jones@email.com', '2025-11-19', 'Mary Jones', '555-987-6544', 'Active', NULL),
(3, 'Christian', 'Supremo', '123 Tilted Towers', 'Fortnite', 'Battlepass', '1212', 'Male', '2004-07-03', '0912345678', 'christian@example.com', '2025-12-03', 'Dean Mata', '0956781234', 'Inactive', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `MembershipID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `PlanID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `RenewalDate` date DEFAULT NULL,
  `Status` enum('Active','Expired','Cancelled','Pending') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership`
--

INSERT INTO `membership` (`MembershipID`, `MemberID`, `PlanID`, `StaffID`, `StartDate`, `EndDate`, `RenewalDate`, `Status`) VALUES
(1, 2, 2, 2, '2025-11-19', '2025-12-19', NULL, 'Cancelled'),
(3, 1, 1, 2, '2025-10-01', '2025-10-16', NULL, 'Expired'),
(4, 2, 1, 2, '2025-12-03', '2025-12-18', NULL, 'Active'),
(5, 3, 3, 2, '2025-11-01', '2025-11-16', NULL, 'Expired');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `MembershipID` int(11) NOT NULL,
  `PaymentMethodID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `PaymentDate` datetime DEFAULT current_timestamp(),
  `AmountPaid` decimal(10,2) NOT NULL,
  `ReferenceNumber` varchar(100) DEFAULT NULL,
  `PaymentStatus` enum('Pending','Completed','Failed','Refunded') DEFAULT 'Completed',
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `MembershipID`, `PaymentMethodID`, `StaffID`, `PaymentDate`, `AmountPaid`, `ReferenceNumber`, `PaymentStatus`, `Remarks`) VALUES
(1, 1, 1, 2, '2025-11-19 13:07:48', 600.00, NULL, 'Completed', NULL),
(3, 3, 2, 2, '2025-11-19 14:19:39', 350.00, '839204715', 'Completed', NULL),
(4, 5, 1, 2, '2025-12-03 11:51:41', 1000.00, '', 'Completed', '');

-- --------------------------------------------------------

--
-- Table structure for table `paymentmethods`
--

CREATE TABLE `paymentmethods` (
  `PaymentMethodID` int(11) NOT NULL,
  `MethodName` varchar(50) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentmethods`
--

INSERT INTO `paymentmethods` (`PaymentMethodID`, `MethodName`, `IsActive`, `Description`) VALUES
(1, 'Cash', 1, 'Standard cash payment'),
(2, 'GCash', 1, 'Mobile QR / wallet payment');

-- --------------------------------------------------------

--
-- Table structure for table `plan`
--

CREATE TABLE `plan` (
  `PlanID` int(11) NOT NULL,
  `PlanName` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `PlanType` varchar(50) DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `Rate` decimal(10,2) DEFAULT NULL,
  `IsActive` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan`
--

INSERT INTO `plan` (`PlanID`, `PlanName`, `Description`, `PlanType`, `Duration`, `Rate`, `IsActive`) VALUES
(1, 'PRT 15Days', 'PT plan valid for 15 days', 'Days', 15, 350.00, 1),
(2, 'PRT 30Days', 'PT plan valid for 30 days', 'Days', 30, 600.00, 1),
(3, 'Cardio 15Days', 'Cardio plan valid for 15 days', 'Days', 15, 500.00, 1),
(4, 'Cardio 30Days', 'Cardio plan valid for 30 days', 'Days', 30, 900.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(50) DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`, `Description`) VALUES
(1, 'Admin', 'Full system access');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `RoleID` int(11) DEFAULT NULL,
  `Username` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `HireDate` date DEFAULT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `FullName`, `RoleID`, `Username`, `Password`, `HireDate`, `Status`) VALUES
(2, 'Admin', 1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-11-19', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreement`
--
ALTER TABLE `agreement`
  ADD PRIMARY KEY (`AgreementID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `MembershipID` (`MembershipID`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`MemberID`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`MembershipID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `PlanID` (`PlanID`),
  ADD KEY `StaffID` (`StaffID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `MembershipID` (`MembershipID`),
  ADD KEY `PaymentMethodID` (`PaymentMethodID`),
  ADD KEY `StaffID` (`StaffID`);

--
-- Indexes for table `paymentmethods`
--
ALTER TABLE `paymentmethods`
  ADD PRIMARY KEY (`PaymentMethodID`);

--
-- Indexes for table `plan`
--
ALTER TABLE `plan`
  ADD PRIMARY KEY (`PlanID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`),
  ADD KEY `RoleID` (`RoleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement`
  MODIFY `AgreementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `MembershipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `paymentmethods`
--
ALTER TABLE `paymentmethods`
  MODIFY `PaymentMethodID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plan`
--
ALTER TABLE `plan`
  MODIFY `PlanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement`
  ADD CONSTRAINT `agreement_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`),
  ADD CONSTRAINT `agreement_ibfk_2` FOREIGN KEY (`MembershipID`) REFERENCES `membership` (`MembershipID`);

--
-- Constraints for table `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`),
  ADD CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`PlanID`) REFERENCES `plan` (`PlanID`),
  ADD CONSTRAINT `membership_ibfk_3` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`MembershipID`) REFERENCES `membership` (`MembershipID`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`PaymentMethodID`) REFERENCES `paymentmethods` (`PaymentMethodID`),
  ADD CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
