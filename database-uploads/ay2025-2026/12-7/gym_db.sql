-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2025 at 05:03 PM
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
(5, 3, 5, '2025-12-03', 'Standard gym membership agreement.', NULL),
(6, 1, 77, '2025-12-06', 'Standard gym membership agreement.', NULL);

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
(1, 'Jane', 'Smith', '456 Oak Ave', 'Anytown', 'ON', 'K1A 0B1', 'Female', '1990-05-15', '555-123-4568', 'jane.smith@email.com', '2025-11-19', 'John Smith', '555-123-4569', 'Active', NULL),
(2, 'Peter', 'Jones', '789 Pine St', 'Sometown', 'BC', 'V6A 1B2', 'Male', '1985-11-22', '555-987-6543', 'peter.jones@email.com', '2025-11-19', 'Mary Jones', '555-987-6544', 'Active', NULL),
(3, 'Christian', 'Supremo', '123 Tilted Towers', 'Fortnite', 'Battlepass', '1212', 'Male', '2004-07-03', '0912345678', 'christian@example.com', '2025-12-12', 'Dean Mata', '0956781234', 'Inactive', NULL),
(4, 'Peter', 'Jones', '123 Main St', 'Cebu City', 'Cebu', '6000', 'Male', '1990-05-12', '09123456701', 'peter.jones@email.com', '2025-09-03', 'Alice Jones', '09123450001', 'Active', ''),
(5, 'Maria', 'Lopez', '456 Park Ave', 'Lapu-Lapu', 'Cebu', '6015', 'Female', '1995-03-10', '09123456702', 'maria.lopez@email.com', '2025-06-21', 'Juan Lopez', '09123450002', 'Active', ''),
(6, 'John', 'Carter', '789 Hillside', 'Mandaue', 'Cebu', '6014', 'Male', '1987-08-22', '09123456703', 'john.carter@email.com', '2025-04-28', 'Tim Carter', '09123450003', 'Active', ''),
(7, 'Sarah', 'Mendoza', 'B9 City Homes', 'Cebu City', 'Cebu', '6000', 'Female', '1998-02-14', '09123456704', 's.mendoza@email.com', '2025-02-10', 'Ana Mendoza', '09123450004', 'Active', ''),
(8, 'Daniel', 'Reyes', 'Sitio Riverside', 'Cebu City', 'Cebu', '6000', 'Male', '1999-11-01', '09123456705', 'dan.reyes@email.com', '2024-11-15', 'Carl Reyes', '09123450005', 'Active', ''),
(9, 'Mark', 'Villanueva', 'P. Burgos St.', 'Mandaue', 'Cebu', '6014', 'Male', '1993-07-18', '09123456706', 'mark.v@email.com', '2024-09-03', 'Lisa Villanueva', '09123450006', 'Inactive', ''),
(10, 'Julia', 'Tan', 'Sunrise Village', 'Cebu City', 'Cebu', '6000', 'Female', '1997-10-30', '09123456707', 'julia.tan@email.com', '2024-07-19', 'Grace Tan', '09123450007', 'Pending', ''),
(11, 'Alex', 'Santos', 'Green Meadows', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1992-12-02', '09123456708', 'alex.santos@email.com', '2024-06-02', 'Paul Santos', '09123450008', 'Active', ''),
(12, 'Lara', 'Gomez', 'San Roque', 'Cebu City', 'Cebu', '6000', 'Female', '1994-01-25', '09123456709', 'lara.gomez@email.com', '2024-04-11', 'Ruth Gomez', '09123450009', 'Active', ''),
(13, 'Chris', 'Anderson', 'M.J Cuenco', 'Cebu City', 'Cebu', '6000', 'Male', '1989-04-15', '09123456710', 'chris.anderson@email.com', '2024-03-07', 'Simon Anderson', '09123450010', 'Active', ''),
(14, 'Nina', 'Ocampo', 'Tisa Labangon', 'Cebu City', 'Cebu', '6000', 'Female', '1996-09-18', '09123456711', 'nina.ocampo@email.com', '2024-02-22', 'Mary Ocampo', '09123450011', 'Active', ''),
(15, 'Ivan', 'Morales', 'Talamban', 'Cebu City', 'Cebu', '6000', 'Male', '1991-01-03', '09123456712', 'ivan.m@email.com', '2024-01-30', 'Carla Morales', '09123450012', 'Active', ''),
(16, 'Donna', 'Chiu', 'Banawa', 'Cebu City', 'Cebu', '6000', 'Female', '1993-08-09', '09123456713', 'donna.chiu@email.com', '2024-01-15', 'Nancy Chiu', '09123450013', 'Active', ''),
(17, 'Jasper', 'Lim', 'Guadalupe', 'Cebu City', 'Cebu', '6000', 'Male', '1995-06-11', '09123456714', 'jasper.lim@email.com', '2024-10-09', 'Tina Lim', '09123450014', 'Inactive', ''),
(18, 'Helena', 'Young', 'Bulacao', 'Cebu City', 'Cebu', '6000', 'Female', '1999-07-22', '09123456715', 'helena.young@email.com', '2025-03-14', 'Mike Young', '09123450015', 'Active', ''),
(19, 'Rico', 'Navarro', 'Jagobiao', 'Mandaue', 'Cebu', '6014', 'Male', '1990-03-16', '09123456716', 'rico.navarro@email.com', '2025-05-23', 'Lito Navarro', '09123450016', 'Active', ''),
(20, 'Paula', 'Sison', 'Canduman', 'Mandaue', 'Cebu', '6014', 'Female', '1996-02-20', '09123456717', 'paula.sison@email.com', '2025-07-05', 'Faye Sison', '09123450017', 'Active', ''),
(21, 'Leo', 'Bautista', 'Opao', 'Mandaue', 'Cebu', '6014', 'Male', '1988-05-03', '09123456718', 'leo.b@email.com', '2025-08-18', 'Gary Bautista', '09123450018', 'Active', ''),
(22, 'Karla', 'Zabala', 'Marigondon', 'Lapu-Lapu', 'Cebu', '6015', 'Female', '1997-01-28', '09123456719', 'karla.z@email.com', '2025-10-26', 'Rose Zabala', '09123450019', 'Active', ''),
(23, 'Shawn', 'Grey', 'Subangdaku', 'Mandaue', 'Cebu', '6014', 'Male', '1994-09-13', '09123456720', 'shawn.grey@email.com', '2025-12-03', 'Jamie Grey', '09123450020', 'Active', '');

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
(4, 2, 1, 2, '2025-11-24', '2025-12-09', NULL, 'Active'),
(5, 3, 3, 2, '2025-11-01', '2025-11-16', NULL, 'Expired'),
(56, 3, 1, 2, '2025-12-01', '2025-12-06', NULL, 'Active'),
(57, 4, 1, 2, '2025-12-02', '2025-12-07', NULL, 'Active'),
(58, 5, 2, 2, '2025-12-03', '2025-12-08', NULL, 'Active'),
(59, 6, 3, 2, '2025-12-04', '2025-12-09', NULL, 'Active'),
(60, 7, 1, 2, '2025-12-05', '2025-12-08', NULL, 'Active'),
(61, 8, 2, 2, '2025-12-05', '2025-12-09', NULL, 'Active'),
(62, 9, 3, 2, '2025-12-05', '2025-12-11', NULL, 'Active'),
(63, 10, 1, 2, '2025-12-05', '2025-12-12', NULL, 'Active'),
(64, 11, 2, 2, '2025-11-01', '2026-01-01', NULL, 'Active'),
(65, 12, 3, 2, '2025-10-01', '2026-03-01', NULL, 'Active'),
(66, 13, 1, 2, '2025-11-20', '2025-12-20', NULL, 'Active'),
(67, 14, 1, 2, '2025-11-18', '2025-12-18', NULL, 'Active'),
(68, 15, 2, 2, '2025-11-05', '2026-01-05', NULL, ''),
(69, 16, 1, 2, '2025-10-10', '2025-10-25', NULL, 'Expired'),
(70, 17, 3, 2, '2025-09-01', '2025-12-30', NULL, 'Active'),
(71, 18, 1, 2, '2025-12-01', '2025-12-31', NULL, 'Active'),
(72, 19, 2, 2, '2025-12-01', '2025-12-31', NULL, 'Active'),
(73, 20, 3, 2, '2025-12-01', '2026-01-15', NULL, 'Active'),
(74, 21, 1, 2, '2025-12-02', '2025-12-28', NULL, 'Active'),
(75, 22, 2, 2, '2025-11-20', '2025-12-15', NULL, 'Active'),
(76, 23, 3, 2, '2025-12-05', '2025-12-25', NULL, 'Active'),
(77, 1, 1, 2, '2025-12-06', '2025-12-21', NULL, 'Active');

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
(4, 5, 1, 2, '2025-12-03 11:51:41', 1000.00, '', 'Completed', ''),
(64, 3, 1, 2, '2025-12-01 00:00:00', 500.00, 'REF1001', 'Completed', 'Monthly payment'),
(65, 4, 2, 2, '2025-12-02 00:00:00', 500.00, 'REF1002', 'Completed', 'Monthly payment'),
(66, 5, 1, 2, '2025-12-03 00:00:00', 750.00, 'REF1003', 'Completed', 'Plan purchase'),
(67, 56, 2, 2, '2025-12-04 00:00:00', 750.00, 'REF1004', 'Completed', 'Plan purchase'),
(68, 57, 1, 2, '2025-12-05 00:00:00', 500.00, 'REF1005', 'Completed', 'Renewal'),
(69, 58, 1, 2, '2025-12-05 00:00:00', 500.00, 'REF1006', 'Pending', 'Awaiting confirmation'),
(70, 59, 1, 2, '2025-12-06 00:00:00', 500.00, 'REF1007', 'Completed', 'Monthly payment'),
(71, 60, 2, 2, '2025-12-06 00:00:00', 500.00, 'REF1008', 'Completed', 'Walk-in payment'),
(72, 61, 1, 2, '2025-11-10 00:00:00', 900.00, 'REF2001', 'Completed', 'Two-month plan'),
(73, 62, 2, 2, '2025-10-15 00:00:00', 1500.00, 'REF2002', 'Completed', 'Three-month plan'),
(74, 77, 1, 2, '2025-12-06 11:28:33', 350.00, '', 'Completed', '');

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
  MODIFY `AgreementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `MembershipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

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
