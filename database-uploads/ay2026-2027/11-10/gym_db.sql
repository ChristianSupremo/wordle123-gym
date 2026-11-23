-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 09:03 AM
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
(1, 1, 1, '2026-10-01', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No pre-existing conditions reported'),
(2, 2, 2, '2026-11-15', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'Mild knee discomfort - advised to avoid heavy squats'),
(3, 3, 3, '2026-09-01', 'Elite membership agreement with personal training sessions included.', 'Good health - regular fitness routine'),
(4, 4, 4, '2026-12-01', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No medical concerns'),
(5, 5, 5, '2026-01-01', 'Annual VIP membership with full access to all facilities and classes.', 'Excellent physical condition'),
(6, 6, 6, '2026-11-20', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'Previous shoulder injury - cleared for gym activities'),
(7, 7, 7, '2026-10-15', 'Premium membership with access to group classes.', 'No restrictions'),
(8, 8, 8, '2026-12-05', 'Basic membership agreement.', 'First-time gym member'),
(9, 9, 9, '2026-11-01', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No health issues reported'),
(10, 10, 10, '2026-08-01', 'Elite 6-month membership with personal training.', 'Training for marathon'),
(11, 11, 11, '2026-12-03', 'Basic membership agreement.', 'No medical history provided'),
(12, 12, 12, '2026-09-20', 'Premium membership agreement.', 'Good overall health'),
(13, 13, 13, '2026-11-10', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No concerns'),
(14, 14, 14, '2026-02-01', 'Annual VIP membership with full benefits.', 'Regular gym-goer'),
(15, 15, 15, '2026-12-08', 'Basic membership agreement.', 'Beginner - needs orientation'),
(16, 16, 16, '2026-10-01', 'Premium membership with class access.', 'No medical issues'),
(17, 17, 17, '2026-07-01', 'Elite membership with PT sessions.', 'Advanced fitness level'),
(18, 18, 18, '2026-11-25', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No restrictions'),
(19, 19, 19, '2026-12-06', 'Basic membership agreement.', 'Good health'),
(20, 20, 20, '2026-10-10', 'Premium membership agreement.', 'No health concerns'),
(21, 28, 31, '2026-12-09', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'Pending medical clearance'),
(22, 29, 32, '2026-12-10', 'Basic membership agreement.', 'Student member - active lifestyle'),
(23, 30, 33, '2026-12-10', 'Premium membership agreement.', 'No medical history on file');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `AttendanceID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `MembershipID` int(11) DEFAULT NULL,
  `CheckIn` datetime NOT NULL DEFAULT current_timestamp(),
  `CheckOut` datetime DEFAULT NULL,
  `StaffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`AttendanceID`, `MemberID`, `MembershipID`, `CheckIn`, `CheckOut`, `StaffID`) VALUES
(1, 1, NULL, '2026-10-15 11:00:00', '2026-10-15 12:09:00', NULL),
(2, 1, NULL, '2026-10-05 11:00:00', '2026-10-05 11:58:00', NULL),
(3, 9, NULL, '2026-10-07 11:00:00', '2026-10-07 12:21:00', NULL),
(4, 18, NULL, '2026-10-08 07:00:00', '2026-10-08 08:27:00', NULL),
(5, 18, NULL, '2026-10-04 07:00:00', '2026-10-04 08:24:00', NULL),
(6, 24, NULL, '2026-10-08 17:00:00', '2026-10-08 17:56:00', NULL),
(7, 27, NULL, '2026-10-03 11:00:00', '2026-10-03 12:17:00', NULL),
(8, 2, NULL, '2026-10-04 12:00:00', '2026-10-04 12:43:00', NULL),
(9, 5, NULL, '2026-10-04 18:00:00', '2026-10-04 19:28:00', NULL),
(10, 8, NULL, '2026-10-17 20:00:00', '2026-10-17 20:43:00', NULL),
(11, 14, NULL, '2026-10-01 18:00:00', '2026-10-01 19:36:00', NULL),
(12, 17, NULL, '2026-10-05 08:00:00', '2026-10-05 08:51:00', NULL),
(13, 17, NULL, '2026-10-03 09:00:00', '2026-10-03 10:01:00', NULL),
(14, 20, NULL, '2026-10-14 08:00:00', '2026-10-14 08:52:00', NULL),
(15, 20, NULL, '2026-10-03 20:00:00', '2026-10-03 20:46:00', NULL),
(16, 20, NULL, '2026-10-01 10:00:00', '2026-10-01 11:13:00', NULL),
(17, 22, NULL, '2026-10-01 15:00:00', '2026-10-01 15:53:00', NULL),
(18, 25, NULL, '2026-10-08 19:00:00', '2026-10-08 19:50:00', NULL),
(19, 25, NULL, '2026-10-05 20:00:00', '2026-10-05 21:03:00', NULL),
(20, 25, NULL, '2026-10-11 13:00:00', '2026-10-11 13:46:00', NULL),
(21, 28, NULL, '2026-10-15 08:00:00', '2026-10-15 09:23:00', NULL),
(22, 28, NULL, '2026-10-01 09:00:00', '2026-10-01 09:45:00', NULL),
(23, 4, NULL, '2026-10-05 14:00:00', '2026-10-05 15:33:00', NULL),
(24, 4, NULL, '2026-10-03 07:00:00', '2026-10-03 08:31:00', NULL),
(25, 4, NULL, '2026-10-11 16:00:00', '2026-10-11 17:28:00', NULL),
(26, 7, NULL, '2026-10-18 16:00:00', '2026-10-18 17:12:00', NULL),
(27, 7, NULL, '2026-10-07 20:00:00', '2026-10-07 21:37:00', NULL),
(28, 7, NULL, '2026-10-05 07:00:00', '2026-10-05 08:09:00', NULL),
(29, 7, NULL, '2026-10-13 14:00:00', '2026-10-13 14:53:00', NULL),
(30, 7, NULL, '2026-10-03 11:00:00', '2026-10-03 12:35:00', NULL),
(31, 10, NULL, '2026-10-08 16:00:00', '2026-10-08 17:06:00', NULL),
(32, 10, NULL, '2026-10-07 09:00:00', '2026-10-07 09:56:00', NULL),
(33, 10, NULL, '2026-10-11 16:00:00', '2026-10-11 17:18:00', NULL),
(34, 13, NULL, '2026-10-08 14:00:00', '2026-10-08 15:03:00', NULL),
(35, 13, NULL, '2026-10-15 12:00:00', '2026-10-15 13:17:00', NULL),
(36, 16, NULL, '2026-10-04 15:00:00', '2026-10-04 16:15:00', NULL),
(37, 19, NULL, '2026-10-14 20:00:00', '2026-10-14 21:36:00', NULL),
(38, 19, NULL, '2026-10-03 13:00:00', '2026-10-03 14:39:00', NULL),
(39, 23, NULL, '2026-10-04 12:00:00', '2026-10-04 12:54:00', NULL),
(40, 26, NULL, '2026-10-04 08:00:00', '2026-10-04 09:15:00', NULL),
(41, 26, NULL, '2026-10-13 19:00:00', '2026-10-13 19:45:00', NULL),
(42, 29, NULL, '2026-10-18 10:00:00', '2026-10-18 11:11:00', NULL),
(43, 29, NULL, '2026-10-17 06:00:00', '2026-10-17 07:16:00', NULL),
(44, 29, NULL, '2026-10-01 06:00:00', '2026-10-01 07:10:00', NULL),
(64, 1, NULL, '2026-10-07 14:00:00', '2026-10-07 14:45:00', NULL),
(65, 1, NULL, '2026-10-01 10:00:00', '2026-10-01 11:39:00', NULL),
(66, 3, NULL, '2026-10-02 11:00:00', '2026-10-02 12:29:00', NULL),
(67, 3, NULL, '2026-10-01 15:00:00', '2026-10-01 15:53:00', NULL),
(68, 9, NULL, '2026-10-09 16:00:00', '2026-10-09 17:32:00', NULL),
(69, 9, NULL, '2026-10-07 08:00:00', '2026-10-07 08:50:00', NULL),
(70, 12, NULL, '2026-10-09 15:00:00', '2026-10-09 16:38:00', NULL),
(71, 15, NULL, '2026-10-09 15:00:00', '2026-10-09 15:45:00', NULL),
(72, 15, NULL, '2026-10-07 09:00:00', '2026-10-07 10:27:00', NULL),
(73, 18, NULL, '2026-10-08 16:00:00', '2026-10-08 17:26:00', NULL),
(74, 18, NULL, '2026-10-01 12:00:00', '2026-10-01 13:13:00', NULL),
(75, 21, NULL, '2026-10-10 15:00:00', '2026-10-10 15:44:00', NULL),
(76, 24, NULL, '2026-10-07 07:00:00', '2026-10-07 08:32:00', NULL),
(77, 27, NULL, '2026-10-09 14:00:00', '2026-10-09 15:02:00', NULL),
(78, 30, NULL, '2026-10-09 11:00:00', '2026-10-09 11:40:00', NULL),
(79, 30, NULL, '2026-10-02 16:00:00', '2026-10-02 16:44:00', NULL),
(80, 30, NULL, '2026-10-01 10:00:00', '2026-10-01 11:17:00', NULL),
(81, 2, NULL, '2026-10-07 11:00:00', '2026-10-07 11:43:00', NULL),
(82, 11, NULL, '2026-10-09 11:00:00', '2026-10-09 12:33:00', NULL),
(83, 14, NULL, '2026-10-10 11:00:00', '2026-10-10 11:41:00', NULL),
(84, 14, NULL, '2026-10-08 10:00:00', '2026-10-08 11:07:00', NULL),
(85, 14, NULL, '2026-10-01 16:00:00', '2026-10-01 17:07:00', NULL),
(86, 17, NULL, '2026-10-10 16:00:00', '2026-10-10 17:13:00', NULL),
(87, 20, NULL, '2026-10-10 12:00:00', '2026-10-10 13:14:00', NULL),
(88, 20, NULL, '2026-10-08 18:00:00', '2026-10-08 19:33:00', NULL),
(89, 20, NULL, '2026-10-07 15:00:00', '2026-10-07 16:17:00', NULL),
(90, 25, NULL, '2026-10-09 06:00:00', '2026-10-09 06:53:00', NULL),
(91, 28, NULL, '2026-10-07 06:00:00', '2026-10-07 07:31:00', NULL),
(92, 7, NULL, '2026-10-02 20:00:00', '2026-10-02 20:53:00', NULL),
(93, 10, NULL, '2026-10-08 08:00:00', '2026-10-08 09:01:00', NULL),
(94, 10, NULL, '2026-10-03 13:00:00', '2026-10-03 14:26:00', NULL),
(95, 10, NULL, '2026-10-02 15:00:00', '2026-10-02 16:11:00', NULL),
(96, 19, NULL, '2026-10-10 08:00:00', '2026-10-10 08:44:00', NULL),
(97, 19, NULL, '2026-10-07 17:00:00', '2026-10-07 18:35:00', NULL),
(98, 19, NULL, '2026-10-01 12:00:00', '2026-10-01 13:19:00', NULL),
(99, 23, NULL, '2026-10-09 08:00:00', '2026-10-09 08:59:00', NULL),
(100, 23, NULL, '2026-10-08 20:00:00', '2026-10-08 21:03:00', NULL),
(101, 26, NULL, '2026-10-10 15:00:00', '2026-10-10 16:21:00', NULL),
(102, 29, NULL, '2026-10-03 10:00:00', '2026-10-03 11:22:00', NULL),
(103, 29, NULL, '2026-10-01 09:00:00', '2026-10-01 09:46:00', NULL),
(127, 2, 2, '2026-11-09 15:06:10', NULL, 1),
(128, 2, 2, '2026-11-10 15:27:35', '2026-11-10 15:27:50', 1);

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
  `Photo` varchar(255) DEFAULT NULL,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`MemberID`, `FirstName`, `LastName`, `Address`, `City`, `Province`, `Zipcode`, `Gender`, `DateOfBirth`, `PhoneNo`, `Email`, `JoinDate`, `EmergencyContactName`, `EmergencyContactNumber`, `MembershipStatus`, `Photo`, `CreatedBy`, `CreatedAt`) VALUES
(1, 'John', 'Reyes', '123 Mango Street', 'Cebu City', 'Cebu', '6000', 'Male', '1995-03-15', '09171234567', 'john.reyes@email.com', '2026-06-15', 'Anna Reyes', '09171234568', 'Active', NULL, 1, '2026-06-15 10:30:00'),
(2, 'Maria', 'Garcia', '456 Sampaguita Ave', 'Mandaue', 'Cebu', '6014', 'Female', '1998-07-22', '09181234567', 'maria.garcia@email.com', '2026-08-20', 'Pedro Garcia', '09181234568', 'Active', NULL, 2, '2026-08-20 11:15:00'),
(3, 'Carlos', 'Mendoza', '789 Orchid Road', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1992-11-08', '09191234567', 'carlos.mendoza@email.com', '2026-09-10', 'Linda Mendoza', '09191234568', 'Active', NULL, 1, '2026-09-10 09:45:00'),
(4, 'Ana', 'Cruz', '234 Rose Lane', 'Cebu City', 'Cebu', '6000', 'Female', '1996-05-19', '09201234567', 'ana.cruz@email.com', '2026-10-05', 'Miguel Cruz', '09201234568', 'Active', NULL, 3, '2026-10-05 14:20:00'),
(5, 'Rafael', 'Santos', '567 Jasmine St', 'Talisay', 'Cebu', '6045', 'Male', '1990-02-28', '09211234567', 'rafael.santos@email.com', '2026-11-01', 'Sofia Santos', '09211234568', 'Active', NULL, 2, '2026-11-01 10:00:00'),
(6, 'Isabella', 'Torres', '890 Lily Court', 'Cebu City', 'Cebu', '6000', 'Female', '1999-09-14', '09221234567', 'isabella.torres@email.com', '2026-11-15', 'Diego Torres', '09221234568', 'Active', NULL, 1, '2026-11-15 13:30:00'),
(7, 'Diego', 'Flores', '123 Sunflower Drive', 'Mandaue', 'Cebu', '6014', 'Male', '1994-12-03', '09231234567', 'diego.flores@email.com', '2026-12-01', 'Carmen Flores', '09231234568', 'Active', NULL, 3, '2026-12-01 08:45:00'),
(8, 'Sofia', 'Ramos', '456 Tulip Boulevard', 'Cebu City', 'Cebu', '6000', 'Female', '1997-04-17', '09241234567', 'sofia.ramos@email.com', '2026-01-10', 'Luis Ramos', '09241234568', 'Active', NULL, 2, '2026-01-10 11:00:00'),
(9, 'Miguel', 'Diaz', '789 Dahlia Street', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1993-08-25', '09251234567', 'miguel.diaz@email.com', '2026-02-14', 'Elena Diaz', '09251234568', 'Active', NULL, 1, '2026-02-14 15:20:00'),
(10, 'Elena', 'Morales', '234 Violet Avenue', 'Mandaue', 'Cebu', '6014', 'Female', '1991-01-30', '09261234567', 'elena.morales@email.com', '2026-03-20', 'Roberto Morales', '09261234568', 'Active', NULL, 3, '2026-03-20 09:30:00'),
(11, 'Luis', 'Jimenez', '567 Carnation Road', 'Cebu City', 'Cebu', '6000', 'Male', '1989-06-12', '09271234567', 'luis.jimenez@email.com', '2026-04-15', 'Patricia Jimenez', '09271234568', 'Active', NULL, 2, '2026-04-15 14:45:00'),
(12, 'Carmen', 'Ruiz', '890 Peony Lane', 'Talisay', 'Cebu', '6045', 'Female', '2000-10-05', '09281234567', 'carmen.ruiz@email.com', '2026-05-22', 'Fernando Ruiz', '09281234568', 'Active', NULL, 1, '2026-05-22 10:15:00'),
(13, 'Fernando', 'Alvarez', '123 Marigold St', 'Cebu City', 'Cebu', '6000', 'Male', '1988-03-18', '09291234567', 'fernando.alvarez@email.com', '2026-06-10', 'Gloria Alvarez', '09291234568', 'Active', NULL, 3, '2026-06-10 13:00:00'),
(14, 'Gloria', 'Herrera', '456 Iris Drive', 'Mandaue', 'Cebu', '6014', 'Female', '1995-11-27', '09301234567', 'gloria.herrera@email.com', '2026-07-18', 'Oscar Herrera', '09301234568', 'Active', NULL, 2, '2026-07-18 11:30:00'),
(15, 'Oscar', 'Medina', '789 Poppy Court', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1992-09-09', '09311234567', 'oscar.medina@email.com', '2026-08-25', 'Natalia Medina', '09311234568', 'Active', NULL, 1, '2026-08-25 08:20:00'),
(16, 'Natalia', 'Castro', '234 Azalea Boulevard', 'Cebu City', 'Cebu', '6000', 'Female', '1998-02-14', '09321234567', 'natalia.castro@email.com', '2026-09-05', 'Antonio Castro', '09321234568', 'Active', NULL, 3, '2026-09-05 15:45:00'),
(17, 'Antonio', 'Ortiz', '567 Hibiscus Avenue', 'Talisay', 'Cebu', '6045', 'Male', '1987-07-21', '09331234567', 'antonio.ortiz@email.com', '2026-10-12', 'Valentina Ortiz', '09331234568', 'Active', NULL, 2, '2026-10-12 09:00:00'),
(18, 'Valentina', 'Gomez', '890 Lavender Road', 'Mandaue', 'Cebu', '6014', 'Female', '1996-12-06', '09341234567', 'valentina.gomez@email.com', '2026-11-08', 'Gabriel Gomez', '09341234568', 'Active', NULL, 1, '2026-11-08 14:15:00'),
(19, 'Gabriel', 'Silva', '123 Chrysanthemum St', 'Cebu City', 'Cebu', '6000', 'Male', '1994-04-29', '09351234567', 'gabriel.silva@email.com', '2026-11-20', 'Lucia Silva', '09351234568', 'Active', NULL, 3, '2026-11-20 10:45:00'),
(20, 'Lucia', 'Vargas', '456 Gardenia Lane', 'Lapu-Lapu', 'Cebu', '6015', 'Female', '1990-08-16', '09361234567', 'lucia.vargas@email.com', '2026-12-01', 'Javier Vargas', '09361234568', 'Active', NULL, 2, '2026-12-01 13:20:00'),
(21, 'Javier', 'Rojas', '789 Begonia Court', 'Cebu City', 'Cebu', '6000', 'Male', '1993-01-11', '09371234567', 'javier.rojas@email.com', '2026-03-15', 'Monica Rojas', '09371234568', 'Inactive', NULL, 1, '2026-03-15 11:00:00'),
(22, 'Monica', 'Navarro', '234 Petunia Drive', 'Mandaue', 'Cebu', '6014', 'Female', '1997-05-23', '09381234567', 'monica.navarro@email.com', '2026-05-20', 'Eduardo Navarro', '09381234568', 'Inactive', NULL, 2, '2026-05-20 14:30:00'),
(23, 'Eduardo', 'Gutierrez', '567 Camellia Blvd', 'Talisay', 'Cebu', '6045', 'Male', '1991-10-07', '09391234567', 'eduardo.gutierrez@email.com', '2026-07-10', 'Daniela Gutierrez', '09391234568', 'Inactive', NULL, 3, '2026-07-10 09:15:00'),
(24, 'Daniela', 'Ramirez', '890 Zinnia Avenue', 'Cebu City', 'Cebu', '6000', 'Female', '1999-03-19', '09401234567', 'daniela.ramirez@email.com', '2026-09-05', 'Alejandro Ramirez', '09401234568', 'Inactive', NULL, 1, '2026-09-05 15:45:00'),
(25, 'Alejandro', 'Fuentes', '123 Magnolia Road', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1986-11-28', '09411234567', 'alejandro.fuentes@email.com', '2026-10-18', 'Victoria Fuentes', '09411234568', 'Inactive', NULL, 2, '2026-10-18 10:30:00'),
(26, 'Victoria', 'Soto', '456 Cosmos Street', 'Mandaue', 'Cebu', '6014', 'Female', '1995-06-14', '09421234567', 'victoria.soto@email.com', '2026-11-22', 'Mateo Soto', '09421234568', 'Inactive', NULL, 3, '2026-11-22 13:00:00'),
(27, 'Mateo', 'Delgado', '789 Clover Lane', 'Cebu City', 'Cebu', '6000', 'Male', '1992-02-09', '09431234567', 'mateo.delgado@email.com', '2026-01-05', 'Camila Delgado', '09431234568', 'Inactive', NULL, 1, '2026-01-05 11:45:00'),
(28, 'Camila', 'Blanco', '234 Primrose Court', 'Talisay', 'Cebu', '6045', 'Female', '2001-09-30', '09441234567', 'camila.blanco@email.com', '2026-12-08', 'Sebastian Blanco', '09441234568', 'Pending', NULL, 2, '2026-12-08 14:20:00'),
(29, 'Sebastian', 'Vega', '567 Daffodil Drive', 'Cebu City', 'Cebu', '6000', 'Male', '1994-12-25', '09451234567', 'sebastian.vega@email.com', '2026-12-09', 'Adriana Vega', '09451234568', 'Pending', NULL, 3, '2026-12-09 09:30:00'),
(30, 'Adriana', 'Romero', '890 Freesia Avenue', 'Mandaue', 'Cebu', '6014', 'Female', '1996-07-04', '09461234567', 'adriana.romero@email.com', '2026-12-10', 'Francisco Romero', '09461234568', 'Pending', NULL, 1, '2026-12-10 10:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `memberaccountinvitations`
--

CREATE TABLE `memberaccountinvitations` (
  `InvitationID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `TokenHash` char(64) NOT NULL,
  `ExpiresAt` datetime NOT NULL,
  `UsedAt` datetime DEFAULT NULL,
  `InvitedBy` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberaccountinvitations`
--

INSERT INTO `memberaccountinvitations` (`InvitationID`, `MemberID`, `TokenHash`, `ExpiresAt`, `UsedAt`, `InvitedBy`, `CreatedAt`) VALUES
(1, 29, '417e34b4339323abcac1fadc4e17569051bd24e68ad172702d3226d759c852bb', '2026-11-12 07:58:33', '2026-11-10 14:59:30', 1, '2026-11-10 06:58:33');

-- --------------------------------------------------------

--
-- Table structure for table `memberaccounts`
--

CREATE TABLE `memberaccounts` (
  `MemberAccountID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `AccountStatus` enum('Active','Disabled') NOT NULL DEFAULT 'Active',
  `LastLogin` datetime DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberaccounts`
--

INSERT INTO `memberaccounts` (`MemberAccountID`, `MemberID`, `Username`, `PasswordHash`, `AccountStatus`, `LastLogin`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 29, 'sebastian.vega@email.com', '$2y$10$KHrcrBj9ODNkR0b4fxdxZODv9qHZO5205Wd7eFCcvxFN.s/jDMpUW', 'Active', '2026-11-10 15:44:56', '2026-11-10 06:59:30', '2026-11-10 07:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `memberloginattempts`
--

CREATE TABLE `memberloginattempts` (
  `AttemptID` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `IPAddress` varchar(45) NOT NULL,
  `Success` tinyint(1) NOT NULL DEFAULT 0,
  `AttemptedAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberloginattempts`
--

INSERT INTO `memberloginattempts` (`AttemptID`, `Username`, `IPAddress`, `Success`, `AttemptedAt`) VALUES
(1, 'sebastian.vega@email.com', '127.0.0.1', 1, '2026-11-10 15:16:17'),
(2, 'sebastian.vega@email.com', '127.0.0.1', 1, '2026-11-10 15:29:35'),
(3, 'sebastian.vega@email.com', '127.0.0.1', 1, '2026-11-10 15:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `MembershipID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `PlanID` int(11) DEFAULT NULL,
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
(1, 1, 3, 1, '2026-10-01', '2027-01-01', NULL, 'Active'),
(2, 2, 2, 2, '2026-11-15', '2026-12-15', NULL, 'Active'),
(3, 3, 4, 1, '2026-09-01', '2027-03-01', NULL, 'Active'),
(4, 4, 1, 3, '2026-12-01', '2026-12-16', NULL, 'Active'),
(5, 5, 5, 2, '2026-01-01', '2027-01-01', NULL, 'Active'),
(6, 6, 2, 1, '2026-11-20', '2026-12-20', NULL, 'Active'),
(7, 7, 3, 3, '2026-10-15', '2027-01-15', NULL, 'Active'),
(8, 8, 1, 2, '2026-12-05', '2026-12-20', NULL, 'Active'),
(9, 9, 2, 1, '2026-11-01', '2026-12-01', NULL, 'Active'),
(10, 10, 4, 3, '2026-08-01', '2027-02-01', NULL, 'Active'),
(11, 11, 1, 2, '2026-12-03', '2026-12-18', NULL, 'Active'),
(12, 12, 3, 1, '2026-09-20', '2026-12-20', NULL, 'Active'),
(13, 13, 2, 3, '2026-11-10', '2026-12-10', NULL, 'Active'),
(14, 14, 5, 2, '2026-02-01', '2027-02-01', NULL, 'Active'),
(15, 15, 1, 1, '2026-12-08', '2026-12-23', NULL, 'Active'),
(16, 16, 3, 3, '2026-10-01', '2027-01-01', NULL, 'Active'),
(17, 17, 4, 2, '2026-07-01', '2027-01-01', NULL, 'Active'),
(18, 18, 2, 1, '2026-11-25', '2026-12-25', NULL, 'Active'),
(19, 19, 1, 3, '2026-12-06', '2026-12-21', NULL, 'Active'),
(20, 20, 3, 2, '2026-10-10', '2027-01-10', NULL, 'Active'),
(21, 21, 2, 1, '2026-09-15', '2026-10-15', NULL, 'Expired'),
(22, 22, 1, 2, '2026-11-01', '2026-11-16', NULL, 'Active'),
(23, 23, 3, 3, '2026-06-01', '2026-09-01', NULL, 'Expired'),
(24, 24, 2, 1, '2026-03-15', '2026-04-15', NULL, 'Expired'),
(25, 25, 1, 2, '2026-05-20', '2026-06-04', NULL, 'Expired'),
(26, 26, 2, 3, '2026-07-10', '2026-08-10', NULL, 'Expired'),
(27, 27, 1, 1, '2026-08-15', '2026-08-30', NULL, 'Expired'),
(28, 1, 1, 2, '2026-12-01', '2026-12-16', NULL, 'Cancelled'),
(29, 3, 2, 1, '2026-05-15', '2026-06-15', NULL, 'Cancelled'),
(30, 5, 1, 3, '2026-10-20', '2026-11-04', NULL, 'Cancelled'),
(31, 28, 2, 2, '2026-12-15', '2027-01-15', NULL, 'Pending'),
(32, 29, 1, 3, '2026-12-12', '2026-12-27', NULL, 'Pending'),
(33, 30, 3, 1, '2026-12-20', '2027-03-20', NULL, 'Pending'),
(34, 2, 1, 2, '2026-08-20', '2026-09-04', NULL, 'Expired'),
(35, 4, 2, 3, '2026-09-01', '2026-10-01', NULL, 'Expired'),
(36, 6, 1, 1, '2026-09-20', '2026-10-05', NULL, 'Expired'),
(37, 8, 2, 2, '2026-10-05', '2026-11-05', NULL, 'Expired'),
(38, 10, 3, 3, '2026-04-01', '2026-07-01', NULL, 'Expired'),
(39, 15, 2, 1, '2026-10-08', '2026-11-08', NULL, 'Expired'),
(40, 19, 2, 3, '2026-10-06', '2026-11-06', NULL, 'Expired');

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
(1, 1, 1, 1, '2026-10-01 10:30:00', 1500.00, '', 'Completed', 'Full payment - 3 months plan'),
(2, 2, 2, 2, '2026-11-15 14:15:00', 600.00, 'GC20261115001', 'Completed', 'GCash payment verified'),
(4, 4, 1, 3, '2026-12-01 11:20:00', 350.00, '', 'Completed', 'Cash payment'),
(5, 5, 2, 2, '2026-01-01 13:00:00', 5000.00, 'GC20260101001', 'Completed', 'Annual VIP membership - GCash'),
(6, 6, 1, 1, '2026-11-20 10:45:00', 600.00, '', 'Completed', 'Walk-in payment'),
(7, 7, 2, 3, '2026-10-15 15:30:00', 1500.00, 'GC20261015001', 'Completed', 'GCash transfer'),
(8, 8, 1, 2, '2026-12-05 09:15:00', 350.00, '', 'Completed', 'Cash - basic plan'),
(10, 10, 1, 3, '2026-08-01 11:25:00', 2800.00, '', 'Completed', 'Cash payment for 6-month plan'),
(11, 11, 2, 2, '2026-12-03 10:10:00', 350.00, 'GC20261203001', 'Completed', 'Mobile payment'),
(12, 12, 1, 1, '2026-09-20 13:40:00', 1500.00, '', 'Completed', 'Cash - premium plan'),
(13, 13, 2, 3, '2026-11-10 15:20:00', 600.00, 'GC20261110001', 'Completed', 'GCash verified'),
(15, 15, 1, 1, '2026-12-08 14:05:00', 350.00, '', 'Completed', 'New member - cash'),
(16, 16, 2, 3, '2026-10-01 10:55:00', 1500.00, 'GC20261001001', 'Completed', 'GCash payment'),
(17, 17, 1, 2, '2026-07-01 11:40:00', 2800.00, '', 'Completed', 'Elite plan - cash'),
(18, 18, 2, 1, '2026-11-25 13:15:00', 600.00, 'GC20261125001', 'Completed', 'Standard plan GCash'),
(19, 19, 1, 3, '2026-12-06 09:50:00', 350.00, '', 'Completed', 'Walk-in payment'),
(21, 21, 1, 1, '2026-09-15 10:00:00', 600.00, '', 'Completed', 'Historical payment'),
(22, 22, 2, 2, '2026-11-01 11:30:00', 350.00, 'GC20261101001', 'Completed', 'Previous membership'),
(23, 23, 1, 3, '2026-06-01 14:15:00', 1500.00, '', 'Completed', 'Expired plan payment'),
(24, 24, 2, 1, '2026-03-15 10:45:00', 600.00, 'GC20260315001', 'Completed', 'Past payment record'),
(25, 25, 1, 2, '2026-05-20 13:20:00', 350.00, '', 'Completed', 'Historical data'),
(26, 26, 2, 3, '2026-07-10 09:40:00', 600.00, 'GC20260710001', 'Completed', 'Expired membership'),
(27, 27, 1, 1, '2026-08-15 15:10:00', 350.00, '', 'Completed', 'Past member payment'),
(28, 28, 1, 2, '2026-12-01 10:30:00', 350.00, '', 'Completed', 'Initial payment'),
(29, 28, 1, 2, '2026-12-10 14:20:00', -150.00, '', 'Refunded', 'Partial refund due to cancellation'),
(30, 29, 2, 1, '2026-05-15 11:15:00', 600.00, 'GC20260515001', 'Completed', 'Original payment'),
(31, 30, 1, 3, '2026-10-20 09:50:00', 350.00, '', 'Completed', 'Cancelled plan - paid'),
(32, 31, 2, 2, '2026-12-09 15:30:00', 600.00, 'GC20261209001', 'Pending', 'Awaiting GCash confirmation'),
(33, 32, 1, 3, '2026-12-10 10:20:00', 350.00, '', 'Pending', 'Payment being processed'),
(35, 2, 2, 2, '2026-11-14 16:45:00', 600.00, 'GC20261114001', 'Failed', 'GCash transaction timeout'),
(36, 9, 2, 1, '2026-10-31 17:30:00', 600.00, 'GC20261031001', 'Failed', 'Insufficient funds'),
(37, 34, 1, 2, '2026-08-20 10:00:00', 350.00, '', 'Completed', 'First membership payment'),
(38, 35, 2, 3, '2026-09-01 11:30:00', 600.00, 'GC20260901002', 'Completed', 'Renewal payment'),
(39, 36, 1, 1, '2026-09-20 14:15:00', 350.00, '', 'Completed', 'Previous membership'),
(40, 37, 2, 2, '2026-10-05 09:45:00', 600.00, 'GC20261005001', 'Completed', 'Membership renewal'),
(41, 38, 1, 3, '2026-04-01 10:30:00', 1500.00, '', 'Completed', 'Premium plan payment'),
(42, 39, 2, 1, '2026-10-08 13:20:00', 600.00, 'GC20261008001', 'Completed', 'Standard renewal'),
(43, 40, 2, 3, '2026-10-06 15:40:00', 600.00, 'GC20261006001', 'Completed', 'Renewal completed'),
(44, 5, 2, 2, '2026-06-01 10:15:00', 2500.00, 'GC20260601001', 'Completed', 'Mid-year top-up payment'),
(46, 1, 1, 1, '2026-12-01 09:30:00', 750.00, '', 'Completed', 'Additional 1.5 months extension'),
(47, 3, 2, 1, '2026-12-05 14:45:00', 1400.00, 'GC20261205001', 'Completed', 'Membership extension'),
(48, 7, 1, 3, '2026-12-08 10:50:00', 750.00, '', 'Completed', 'Extension payment - cash'),
(49, 11, 1, 2, '2026-12-09 11:15:00', 175.00, '', 'Completed', 'Partial payment 1/2'),
(50, 11, 1, 2, '2026-12-10 09:30:00', 175.00, '', 'Completed', 'Partial payment 2/2 - completed'),
(51, 15, 2, 1, '2026-12-10 10:45:00', 175.00, 'GC20261210002', 'Completed', 'Additional week extension'),
(52, 19, 1, 3, '2026-12-10 11:30:00', 175.00, '', 'Completed', 'Extension - 7 days');

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
(1, 'Cash', 1, 'Cash payment'),
(2, 'GCash', 1, 'Mobile wallet payment via QR code');

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
(1, 'Basic 15 Days', 'Perfect for beginners - 15 days full gym access', 'Days', 15, 350.00, 1),
(2, 'Standard 1 Month', 'Most popular - 30 days with all amenities', 'Days', 30, 600.00, 1),
(3, 'Premium 3 Months', 'Best value - 3 months unlimited access', 'Months', 3, 1500.00, 1),
(4, 'Elite 6 Months', 'Serious commitment - 6 months with personal training', 'Months', 6, 2800.00, 1),
(5, 'Annual VIP', 'Ultimate fitness - 1 year all-inclusive membership', 'Months', 12, 5000.00, 1),
(6, 'Student Special', '1 month discounted rate for students', 'Days', 30, 450.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(50) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `AccessLevel` int(11) NOT NULL DEFAULT 1,
  `IsDefault` tinyint(1) DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`, `Description`, `AccessLevel`, `IsDefault`, `CreatedAt`) VALUES
(1, 'Admin', 'Full system access with all privileges', 100, 0, '2026-10-26 10:35:42'),
(2, 'Manager', 'Manages staff, payments, plans, and reports', 50, 0, '2026-10-26 10:35:42'),
(3, 'Receptionist', 'Handles memberships, check-ins, and basic operations', 20, 1, '2026-10-26 10:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
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
  `UpdatedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `FirstName`, `LastName`, `Email`, `Phone`, `Photo`, `RoleID`, `Username`, `PasswordHash`, `HireDate`, `Status`, `LastLogin`, `CreatedAt`, `UpdatedAt`, `CreatedBy`, `UpdatedBy`) VALUES
(1, 'Christian Jason', 'Supremo', 'christian.supremo@newyou.com', '09175551001', NULL, 1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-10-01', 'Active', '2026-11-10 15:47:17', '2026-10-01 09:00:00', '2026-11-10 07:47:17', NULL, NULL),
(2, 'Dean Jesus', 'Mata', 'dean.mata@newyou.com', '09175551002', NULL, 2, 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-10-02', 'Active', '2026-10-25 16:45:00', '2026-10-02 09:00:00', '2026-10-26 10:35:42', NULL, NULL),
(3, 'Ira Vistas', 'Flores', 'ira.flores@newyou.com', '09175551003', NULL, 3, 'ira.flores', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-10-03', 'Active', '2026-10-26 09:15:00', '2026-10-03 09:00:00', '2026-10-26 10:35:42', NULL, NULL);

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
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`AttendanceID`),
  ADD KEY `idx_att_member_checkin` (`MemberID`,`CheckIn`),
  ADD KEY `idx_att_checkin` (`CheckIn`),
  ADD KEY `fk_att_membership` (`MembershipID`),
  ADD KEY `fk_att_staff` (`StaffID`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`MemberID`),
  ADD KEY `fk_member_createdby` (`CreatedBy`);

--
-- Indexes for table `memberaccountinvitations`
--
ALTER TABLE `memberaccountinvitations`
  ADD PRIMARY KEY (`InvitationID`),
  ADD UNIQUE KEY `uq_member_invitation_token` (`TokenHash`),
  ADD KEY `idx_member_invitation_member` (`MemberID`),
  ADD KEY `idx_member_invitation_expiry` (`ExpiresAt`),
  ADD KEY `fk_member_invitation_staff` (`InvitedBy`);

--
-- Indexes for table `memberaccounts`
--
ALTER TABLE `memberaccounts`
  ADD PRIMARY KEY (`MemberAccountID`),
  ADD UNIQUE KEY `uq_member_accounts_member` (`MemberID`),
  ADD UNIQUE KEY `uq_member_accounts_username` (`Username`);

--
-- Indexes for table `memberloginattempts`
--
ALTER TABLE `memberloginattempts`
  ADD PRIMARY KEY (`AttemptID`),
  ADD KEY `idx_member_login_user_time` (`Username`,`AttemptedAt`),
  ADD KEY `idx_member_login_ip_time` (`IPAddress`,`AttemptedAt`);

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
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `fk_staff_role` (`RoleID`),
  ADD KEY `fk_staff_created_by` (`CreatedBy`),
  ADD KEY `fk_staff_updated_by` (`UpdatedBy`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement`
  MODIFY `AgreementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `memberaccountinvitations`
--
ALTER TABLE `memberaccountinvitations`
  MODIFY `InvitationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `memberaccounts`
--
ALTER TABLE `memberaccounts`
  MODIFY `MemberAccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `memberloginattempts`
--
ALTER TABLE `memberloginattempts`
  MODIFY `AttemptID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `MembershipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `paymentmethods`
--
ALTER TABLE `paymentmethods`
  MODIFY `PaymentMethodID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plan`
--
ALTER TABLE `plan`
  MODIFY `PlanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_att_member` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_att_membership` FOREIGN KEY (`MembershipID`) REFERENCES `membership` (`MembershipID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_att_staff` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`) ON DELETE SET NULL;

--
-- Constraints for table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `fk_member_createdby` FOREIGN KEY (`CreatedBy`) REFERENCES `staff` (`StaffID`) ON DELETE SET NULL;

--
-- Constraints for table `memberaccountinvitations`
--
ALTER TABLE `memberaccountinvitations`
  ADD CONSTRAINT `fk_member_invitation_member` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_member_invitation_staff` FOREIGN KEY (`InvitedBy`) REFERENCES `staff` (`StaffID`) ON DELETE SET NULL;

--
-- Constraints for table `memberaccounts`
--
ALTER TABLE `memberaccounts`
  ADD CONSTRAINT `fk_member_accounts_member` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_staff_created_by` FOREIGN KEY (`CreatedBy`) REFERENCES `staff` (`StaffID`),
  ADD CONSTRAINT `fk_staff_role` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`),
  ADD CONSTRAINT `fk_staff_updated_by` FOREIGN KEY (`UpdatedBy`) REFERENCES `staff` (`StaffID`),
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
