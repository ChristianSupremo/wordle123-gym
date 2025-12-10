-- =====================================================
-- GYM MANAGEMENT SYSTEM - DEMONSTRATION DATABASE
-- Clean, Professional Sample Data for Presentation
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. CLEAR EXISTING DATA (in correct order)
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

-- Delete in reverse dependency order
DELETE FROM `agreement`;
DELETE FROM `payment`;
DELETE FROM `membership`;
DELETE FROM `member`;
DELETE FROM `staff`; -- Staff has self-referencing FK, but FOREIGN_KEY_CHECKS=0 handles it
DELETE FROM `plan`;
DELETE FROM `paymentmethods`;
DELETE FROM `roles`;

-- Reset auto increment counters
ALTER TABLE `agreement` AUTO_INCREMENT = 1;
ALTER TABLE `payment` AUTO_INCREMENT = 1;
ALTER TABLE `membership` AUTO_INCREMENT = 1;
ALTER TABLE `member` AUTO_INCREMENT = 1;
ALTER TABLE `staff` AUTO_INCREMENT = 1;
ALTER TABLE `plan` AUTO_INCREMENT = 1;
ALTER TABLE `paymentmethods` AUTO_INCREMENT = 1;
ALTER TABLE `roles` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 2. INSERT ROLES
-- =====================================================
INSERT INTO `roles` (`RoleID`, `RoleName`, `Description`, `AccessLevel`, `IsDefault`) VALUES
(1, 'Admin', 'Full system access with all privileges', 100, 0),
(2, 'Manager', 'Manages staff, payments, plans, and reports', 50, 0),
(3, 'Receptionist', 'Handles memberships, check-ins, and basic operations', 20, 1);

-- =====================================================
-- 3. INSERT STAFF
-- =====================================================
-- Password for all: "password" (hashed with bcrypt)
INSERT INTO `staff` (`StaffID`, `FirstName`, `LastName`, `Email`, `Phone`, `RoleID`, `Username`, `PasswordHash`, `HireDate`, `Status`, `LastLogin`, `CreatedAt`) VALUES
(1, 'Christian', 'Supremo', 'christian.supremo@newyou.com', '09175551001', 1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-15', 'Active', '2025-12-10 08:30:00', '2024-01-15 09:00:00'),
(2, 'Dean', 'Mata', 'dean.mata@newyou.com', '09175551002', 2, 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-02-01', 'Active', '2025-12-09 16:45:00', '2024-02-01 09:00:00'),
(3, 'Shiori', 'Morisaka', 'shiori.morisaka@newyou.com', '09175551003', 3, 'receptionist', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-03-10', 'Active', '2025-12-10 09:15:00', '2024-03-10 09:00:00'),
(4, 'Zyleika', 'Invento', 'zyleika.invento@newyou.com', '09175551004', 3, 'zyleikainvento', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-06-15', 'Active', '2025-12-08 14:30:00', '2024-06-15 09:00:00'),
(5, 'Queenie', 'Sandoval', 'queenie.sandoval@newyou.com', '09175551005', 3, 'queeniesandoval', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-06-15', 'Active', '2025-12-08 14:30:00', '2024-06-15 09:00:00');

-- =====================================================
-- 4. INSERT PAYMENT METHODS  (GCash + Cash ONLY)
-- =====================================================
INSERT INTO `paymentmethods` (`PaymentMethodID`, `MethodName`, `IsActive`, `Description`) VALUES
(1, 'Cash', 1, 'Standard cash payment at reception'),
(2, 'GCash', 1, 'Mobile wallet payment via QR code');


-- =====================================================
-- 5. INSERT PLANS
-- =====================================================
INSERT INTO `plan` (`PlanID`, `PlanName`, `Description`, `PlanType`, `Duration`, `Rate`, `IsActive`) VALUES
(1, 'Basic 15 Days', 'Perfect for beginners - 15 days full gym access', 'Days', 15, 350.00, 1),
(2, 'Standard 1 Month', 'Most popular - 30 days with all amenities', 'Days', 30, 600.00, 1),
(3, 'Premium 3 Months', 'Best value - 3 months unlimited access', 'Months', 3, 1500.00, 1),
(4, 'Elite 6 Months', 'Serious commitment - 6 months with personal training', 'Months', 6, 2800.00, 1),
(5, 'Annual VIP', 'Ultimate fitness - 1 year all-inclusive membership', 'Months', 12, 5000.00, 1),
(6, 'Student Special', '1 month discounted rate for students', 'Days', 30, 450.00, 1);

-- =====================================================
-- 6. INSERT MEMBERS (30 realistic members)
-- =====================================================
INSERT INTO `member` (`MemberID`, `FirstName`, `LastName`, `Address`, `City`, `Province`, `Zipcode`, `Gender`, `DateOfBirth`, `PhoneNo`, `Email`, `JoinDate`, `EmergencyContactName`, `EmergencyContactNumber`, `MembershipStatus`, `CreatedBy`, `CreatedAt`) VALUES
-- Active Members (20)
(1, 'John', 'Reyes', '123 Mango Street', 'Cebu City', 'Cebu', '6000', 'Male', '1995-03-15', '09171234567', 'john.reyes@email.com', '2024-06-15', 'Anna Reyes', '09171234568', 'Active', 1, '2024-06-15 10:30:00'),
(2, 'Maria', 'Garcia', '456 Sampaguita Ave', 'Mandaue', 'Cebu', '6014', 'Female', '1998-07-22', '09181234567', 'maria.garcia@email.com', '2024-08-20', 'Pedro Garcia', '09181234568', 'Active', 2, '2024-08-20 11:15:00'),
(3, 'Carlos', 'Mendoza', '789 Orchid Road', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1992-11-08', '09191234567', 'carlos.mendoza@email.com', '2024-09-10', 'Linda Mendoza', '09191234568', 'Active', 1, '2024-09-10 09:45:00'),
(4, 'Ana', 'Cruz', '234 Rose Lane', 'Cebu City', 'Cebu', '6000', 'Female', '1996-05-19', '09201234567', 'ana.cruz@email.com', '2024-10-05', 'Miguel Cruz', '09201234568', 'Active', 3, '2024-10-05 14:20:00'),
(5, 'Rafael', 'Santos', '567 Jasmine St', 'Talisay', 'Cebu', '6045', 'Male', '1990-02-28', '09211234567', 'rafael.santos@email.com', '2024-11-01', 'Sofia Santos', '09211234568', 'Active', 2, '2024-11-01 10:00:00'),
(6, 'Isabella', 'Torres', '890 Lily Court', 'Cebu City', 'Cebu', '6000', 'Female', '1999-09-14', '09221234567', 'isabella.torres@email.com', '2024-11-15', 'Diego Torres', '09221234568', 'Active', 1, '2024-11-15 13:30:00'),
(7, 'Diego', 'Flores', '123 Sunflower Drive', 'Mandaue', 'Cebu', '6014', 'Male', '1994-12-03', '09231234567', 'diego.flores@email.com', '2024-12-01', 'Carmen Flores', '09231234568', 'Active', 3, '2024-12-01 08:45:00'),
(8, 'Sofia', 'Ramos', '456 Tulip Boulevard', 'Cebu City', 'Cebu', '6000', 'Female', '1997-04-17', '09241234567', 'sofia.ramos@email.com', '2025-01-10', 'Luis Ramos', '09241234568', 'Active', 2, '2025-01-10 11:00:00'),
(9, 'Miguel', 'Diaz', '789 Dahlia Street', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1993-08-25', '09251234567', 'miguel.diaz@email.com', '2025-02-14', 'Elena Diaz', '09251234568', 'Active', 1, '2025-02-14 15:20:00'),
(10, 'Elena', 'Morales', '234 Violet Avenue', 'Mandaue', 'Cebu', '6014', 'Female', '1991-01-30', '09261234567', 'elena.morales@email.com', '2025-03-20', 'Roberto Morales', '09261234568', 'Active', 3, '2025-03-20 09:30:00'),
(11, 'Luis', 'Jimenez', '567 Carnation Road', 'Cebu City', 'Cebu', '6000', 'Male', '1989-06-12', '09271234567', 'luis.jimenez@email.com', '2025-04-15', 'Patricia Jimenez', '09271234568', 'Active', 2, '2025-04-15 14:45:00'),
(12, 'Carmen', 'Ruiz', '890 Peony Lane', 'Talisay', 'Cebu', '6045', 'Female', '2000-10-05', '09281234567', 'carmen.ruiz@email.com', '2025-05-22', 'Fernando Ruiz', '09281234568', 'Active', 1, '2025-05-22 10:15:00'),
(13, 'Fernando', 'Alvarez', '123 Marigold St', 'Cebu City', 'Cebu', '6000', 'Male', '1988-03-18', '09291234567', 'fernando.alvarez@email.com', '2025-06-10', 'Gloria Alvarez', '09291234568', 'Active', 3, '2025-06-10 13:00:00'),
(14, 'Gloria', 'Herrera', '456 Iris Drive', 'Mandaue', 'Cebu', '6014', 'Female', '1995-11-27', '09301234567', 'gloria.herrera@email.com', '2025-07-18', 'Oscar Herrera', '09301234568', 'Active', 2, '2025-07-18 11:30:00'),
(15, 'Oscar', 'Medina', '789 Poppy Court', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1992-09-09', '09311234567', 'oscar.medina@email.com', '2025-08-25', 'Natalia Medina', '09311234568', 'Active', 1, '2025-08-25 08:20:00'),
(16, 'Natalia', 'Castro', '234 Azalea Boulevard', 'Cebu City', 'Cebu', '6000', 'Female', '1998-02-14', '09321234567', 'natalia.castro@email.com', '2025-09-05', 'Antonio Castro', '09321234568', 'Active', 3, '2025-09-05 15:45:00'),
(17, 'Antonio', 'Ortiz', '567 Hibiscus Avenue', 'Talisay', 'Cebu', '6045', 'Male', '1987-07-21', '09331234567', 'antonio.ortiz@email.com', '2025-10-12', 'Valentina Ortiz', '09331234568', 'Active', 2, '2025-10-12 09:00:00'),
(18, 'Valentina', 'Gomez', '890 Lavender Road', 'Mandaue', 'Cebu', '6014', 'Female', '1996-12-06', '09341234567', 'valentina.gomez@email.com', '2025-11-08', 'Gabriel Gomez', '09341234568', 'Active', 1, '2025-11-08 14:15:00'),
(19, 'Gabriel', 'Silva', '123 Chrysanthemum St', 'Cebu City', 'Cebu', '6000', 'Male', '1994-04-29', '09351234567', 'gabriel.silva@email.com', '2025-11-20', 'Lucia Silva', '09351234568', 'Active', 3, '2025-11-20 10:45:00'),
(20, 'Lucia', 'Vargas', '456 Gardenia Lane', 'Lapu-Lapu', 'Cebu', '6015', 'Female', '1990-08-16', '09361234567', 'lucia.vargas@email.com', '2025-12-01', 'Javier Vargas', '09361234568', 'Active', 2, '2025-12-01 13:20:00'),

-- Inactive Members (7)
(21, 'Javier', 'Rojas', '789 Begonia Court', 'Cebu City', 'Cebu', '6000', 'Male', '1993-01-11', '09371234567', 'javier.rojas@email.com', '2024-03-15', 'Monica Rojas', '09371234568', 'Inactive', 1, '2024-03-15 11:00:00'),
(22, 'Monica', 'Navarro', '234 Petunia Drive', 'Mandaue', 'Cebu', '6014', 'Female', '1997-05-23', '09381234567', 'monica.navarro@email.com', '2024-05-20', 'Eduardo Navarro', '09381234568', 'Inactive', 2, '2024-05-20 14:30:00'),
(23, 'Eduardo', 'Gutierrez', '567 Camellia Blvd', 'Talisay', 'Cebu', '6045', 'Male', '1991-10-07', '09391234567', 'eduardo.gutierrez@email.com', '2024-07-10', 'Daniela Gutierrez', '09391234568', 'Inactive', 3, '2024-07-10 09:15:00'),
(24, 'Daniela', 'Ramirez', '890 Zinnia Avenue', 'Cebu City', 'Cebu', '6000', 'Female', '1999-03-19', '09401234567', 'daniela.ramirez@email.com', '2024-09-05', 'Alejandro Ramirez', '09401234568', 'Inactive', 1, '2024-09-05 15:45:00'),
(25, 'Alejandro', 'Fuentes', '123 Magnolia Road', 'Lapu-Lapu', 'Cebu', '6015', 'Male', '1986-11-28', '09411234567', 'alejandro.fuentes@email.com', '2024-10-18', 'Victoria Fuentes', '09411234568', 'Inactive', 2, '2024-10-18 10:30:00'),
(26, 'Victoria', 'Soto', '456 Cosmos Street', 'Mandaue', 'Cebu', '6014', 'Female', '1995-06-14', '09421234567', 'victoria.soto@email.com', '2024-11-22', 'Mateo Soto', '09421234568', 'Inactive', 3, '2024-11-22 13:00:00'),
(27, 'Mateo', 'Delgado', '789 Clover Lane', 'Cebu City', 'Cebu', '6000', 'Male', '1992-02-09', '09431234567', 'mateo.delgado@email.com', '2025-01-05', 'Camila Delgado', '09431234568', 'Inactive', 1, '2025-01-05 11:45:00'),

-- Pending Members (3)
(28, 'Camila', 'Blanco', '234 Primrose Court', 'Talisay', 'Cebu', '6045', 'Female', '2001-09-30', '09441234567', 'camila.blanco@email.com', '2025-12-08', 'Sebastian Blanco', '09441234568', 'Pending', 2, '2025-12-08 14:20:00'),
(29, 'Sebastian', 'Vega', '567 Daffodil Drive', 'Cebu City', 'Cebu', '6000', 'Male', '1994-12-25', '09451234567', 'sebastian.vega@email.com', '2025-12-09', 'Adriana Vega', '09451234568', 'Pending', 3, '2025-12-09 09:30:00'),
(30, 'Adriana', 'Romero', '890 Freesia Avenue', 'Mandaue', 'Cebu', '6014', 'Female', '1996-07-04', '09461234567', 'adriana.romero@email.com', '2025-12-10', 'Francisco Romero', '09461234568', 'Pending', 1, '2025-12-10 10:15:00');

-- =====================================================
-- 7. INSERT MEMBERSHIPS (40 memberships - mix of statuses)
-- =====================================================
INSERT INTO `membership` (`MembershipID`, `MemberID`, `PlanID`, `StaffID`, `StartDate`, `EndDate`, `Status`) VALUES
-- Active Memberships
(1, 1, 3, 1, '2025-10-01', '2026-01-01', 'Active'),
(2, 2, 2, 2, '2025-11-15', '2025-12-15', 'Active'),
(3, 3, 4, 1, '2025-09-01', '2026-03-01', 'Active'),
(4, 4, 1, 3, '2025-12-01', '2025-12-16', 'Active'),
(5, 5, 5, 2, '2025-01-01', '2026-01-01', 'Active'),
(6, 6, 2, 1, '2025-11-20', '2025-12-20', 'Active'),
(7, 7, 3, 3, '2025-10-15', '2026-01-15', 'Active'),
(8, 8, 1, 2, '2025-12-05', '2025-12-20', 'Active'),
(9, 9, 2, 1, '2025-11-01', '2025-12-01', 'Active'),
(10, 10, 4, 3, '2025-08-01', '2026-02-01', 'Active'),
(11, 11, 1, 2, '2025-12-03', '2025-12-18', 'Active'),
(12, 12, 3, 1, '2025-09-20', '2025-12-20', 'Active'),
(13, 13, 2, 3, '2025-11-10', '2025-12-10', 'Active'),
(14, 14, 5, 2, '2025-02-01', '2026-02-01', 'Active'),
(15, 15, 1, 1, '2025-12-08', '2025-12-23', 'Active'),
(16, 16, 3, 3, '2025-10-01', '2026-01-01', 'Active'),
(17, 17, 4, 2, '2025-07-01', '2026-01-01', 'Active'),
(18, 18, 2, 1, '2025-11-25', '2025-12-25', 'Active'),
(19, 19, 1, 3, '2025-12-06', '2025-12-21', 'Active'),
(20, 20, 3, 2, '2025-10-10', '2026-01-10', 'Active'),

-- Expired Memberships
(21, 21, 2, 1, '2024-09-15', '2024-10-15', 'Expired'),
(22, 22, 1, 2, '2024-11-01', '2024-11-16', 'Expired'),
(23, 23, 3, 3, '2024-06-01', '2024-09-01', 'Expired'),
(24, 24, 2, 1, '2025-03-15', '2025-04-15', 'Expired'),
(25, 25, 1, 2, '2025-05-20', '2025-06-04', 'Expired'),
(26, 26, 2, 3, '2025-07-10', '2025-08-10', 'Expired'),
(27, 27, 1, 1, '2025-08-15', '2025-08-30', 'Expired'),

-- Cancelled Memberships
(28, 1, 1, 2, '2024-12-01', '2024-12-16', 'Cancelled'),
(29, 3, 2, 1, '2025-05-15', '2025-06-15', 'Cancelled'),
(30, 5, 1, 3, '2024-10-20', '2024-11-04', 'Cancelled'),

-- Pending Memberships (new registrations)
(31, 28, 2, 2, '2025-12-15', '2026-01-15', 'Pending'),
(32, 29, 1, 3, '2025-12-12', '2025-12-27', 'Pending'),
(33, 30, 3, 1, '2025-12-20', '2026-03-20', 'Pending'),

-- Renewal histories (showing members with multiple memberships)
(34, 2, 1, 2, '2024-08-20', '2024-09-04', 'Expired'),
(35, 4, 2, 3, '2025-09-01', '2025-10-01', 'Expired'),
(36, 6, 1, 1, '2025-09-20', '2025-10-05', 'Expired'),
(37, 8, 2, 2, '2025-10-05', '2025-11-05', 'Expired'),
(38, 10, 3, 3, '2025-04-01', '2025-07-01', 'Expired'),
(39, 15, 2, 1, '2025-10-08', '2025-11-08', 'Expired'),
(40, 19, 2, 3, '2025-10-06', '2025-11-06', 'Expired');

-- =====================================================
-- 8. INSERT PAYMENTS (50+ payments showing various scenarios)
-- =====================================================
INSERT INTO `payment` (`PaymentID`, `MembershipID`, `PaymentMethodID`, `StaffID`, `PaymentDate`, `AmountPaid`, `ReferenceNumber`, `PaymentStatus`, `Remarks`) VALUES
-- Completed Payments for Active Memberships
(1, 1, 1, 1, '2025-10-01 10:30:00', 1500.00, '', 'Completed', 'Full payment - 3 months plan'),
(2, 2, 2, 2, '2025-11-15 14:15:00', 600.00, 'GC20251115001', 'Completed', 'GCash payment verified'),
(4, 4, 1, 3, '2025-12-01 11:20:00', 350.00, '', 'Completed', 'Cash payment'),
(5, 5, 2, 2, '2025-01-01 13:00:00', 5000.00, 'GC20250101001', 'Completed', 'Annual VIP membership - GCash'),
(6, 6, 1, 1, '2025-11-20 10:45:00', 600.00, '', 'Completed', 'Walk-in payment'),
(7, 7, 2, 3, '2025-10-15 15:30:00', 1500.00, 'GC20251015001', 'Completed', 'GCash transfer'),
(8, 8, 1, 2, '2025-12-05 09:15:00', 350.00, '', 'Completed', 'Cash - basic plan'),
(10, 10, 1, 3, '2025-08-01 11:25:00', 2800.00, '', 'Completed', 'Cash payment for 6-month plan'),
(11, 11, 2, 2, '2025-12-03 10:10:00', 350.00, 'GC20251203001', 'Completed', 'Mobile payment'),
(12, 12, 1, 1, '2025-09-20 13:40:00', 1500.00, '', 'Completed', 'Cash - premium plan'),
(13, 13, 2, 3, '2025-11-10 15:20:00', 600.00, 'GC20251110001', 'Completed', 'GCash verified'),
(15, 15, 1, 1, '2025-12-08 14:05:00', 350.00, '', 'Completed', 'New member - cash'),
(16, 16, 2, 3, '2025-10-01 10:55:00', 1500.00, 'GC20251001001', 'Completed', 'GCash payment'),
(17, 17, 1, 2, '2025-07-01 11:40:00', 2800.00, '', 'Completed', 'Elite plan - cash'),
(18, 18, 2, 1, '2025-11-25 13:15:00', 600.00, 'GC20251125001', 'Completed', 'Standard plan GCash'),
(19, 19, 1, 3, '2025-12-06 09:50:00', 350.00, '', 'Completed', 'Walk-in payment'),

-- Payments for Expired Memberships (historical)
(21, 21, 1, 1, '2024-09-15 10:00:00', 600.00, '', 'Completed', 'Historical payment'),
(22, 22, 2, 2, '2024-11-01 11:30:00', 350.00, 'GC20241101001', 'Completed', 'Previous membership'),
(23, 23, 1, 3, '2024-06-01 14:15:00', 1500.00, '', 'Completed', 'Expired plan payment'),
(24, 24, 2, 1, '2025-03-15 10:45:00', 600.00, 'GC20250315001', 'Completed', 'Past payment record'),
(25, 25, 1, 2, '2025-05-20 13:20:00', 350.00, '', 'Completed', 'Historical data'),
(26, 26, 2, 3, '2025-07-10 09:40:00', 600.00, 'GC20250710001', 'Completed', 'Expired membership'),
(27, 27, 1, 1, '2025-08-15 15:10:00', 350.00, '', 'Completed', 'Past member payment'),

-- Payments for Cancelled Memberships
(28, 28, 1, 2, '2024-12-01 10:30:00', 350.00, '', 'Completed', 'Initial payment'),
(29, 28, 1, 2, '2024-12-10 14:20:00', -150.00, '', 'Refunded', 'Partial refund due to cancellation'),
(30, 29, 2, 1, '2025-05-15 11:15:00', 600.00, 'GC20250515001', 'Completed', 'Original payment'),
(31, 30, 1, 3, '2024-10-20 09:50:00', 350.00, '', 'Completed', 'Cancelled plan - paid'),

-- Pending Payments
(32, 31, 2, 2, '2025-12-09 15:30:00', 600.00, 'GC20251209001', 'Pending', 'Awaiting GCash confirmation'),
(33, 32, 1, 3, '2025-12-10 10:20:00', 350.00, '', 'Pending', 'Payment being processed'),

-- Failed Payments
(35, 2, 2, 2, '2025-11-14 16:45:00', 600.00, 'GC20251114001', 'Failed', 'GCash transaction timeout'),
(36, 9, 2, 1, '2025-10-31 17:30:00', 600.00, 'GC20251031001', 'Failed', 'Insufficient funds'),

-- Payments for Renewal histories
(37, 34, 1, 2, '2024-08-20 10:00:00', 350.00, '', 'Completed', 'First membership payment'),
(38, 35, 2, 3, '2025-09-01 11:30:00', 600.00, 'GC20250901002', 'Completed', 'Renewal payment'),
(39, 36, 1, 1, '2025-09-20 14:15:00', 350.00, '', 'Completed', 'Previous membership'),
(40, 37, 2, 2, '2025-10-05 09:45:00', 600.00, 'GC20251005001', 'Completed', 'Membership renewal'),
(41, 38, 1, 3, '2025-04-01 10:30:00', 1500.00, '', 'Completed', 'Premium plan payment'),
(42, 39, 2, 1, '2025-10-08 13:20:00', 600.00, 'GC20251008001', 'Completed', 'Standard renewal'),
(43, 40, 2, 3, '2025-10-06 15:40:00', 600.00, 'GC20251006001', 'Completed', 'Renewal completed'),

-- Additional Payments
(44, 5, 2, 2, '2025-06-01 10:15:00', 2500.00, 'GC20250601001', 'Completed', 'Mid-year top-up payment'),
(46, 1, 1, 1, '2025-12-01 09:30:00', 750.00, '', 'Completed', 'Additional 1.5 months extension'),
(47, 3, 2, 1, '2025-12-05 14:45:00', 1400.00, 'GC20251205001', 'Completed', 'Membership extension'),
(48, 7, 1, 3, '2025-12-08 10:50:00', 750.00, '', 'Completed', 'Extension payment - cash'),

-- Recent Transactions
(49, 11, 1, 2, '2025-12-09 11:15:00', 175.00, '', 'Completed', 'Partial payment 1/2'),
(50, 11, 1, 2, '2025-12-10 09:30:00', 175.00, '', 'Completed', 'Partial payment 2/2 - completed'),
(51, 15, 2, 1, '2025-12-10 10:45:00', 175.00, 'GC20251210002', 'Completed', 'Additional week extension'),
(52, 19, 1, 3, '2025-12-10 11:30:00', 175.00, '', 'Completed', 'Extension - 7 days');


-- =====================================================
-- 9. INSERT AGREEMENTS (for active memberships)
-- =====================================================
INSERT INTO `agreement` (`AgreementID`, `MemberID`, `MembershipID`, `AgreementDate`, `Terms`, `PhysicalConditionDetails`) VALUES
(1, 1, 1, '2025-10-01', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No pre-existing conditions reported'),
(2, 2, 2, '2025-11-15', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'Mild knee discomfort - advised to avoid heavy squats'),
(3, 3, 3, '2025-09-01', 'Elite membership agreement with personal training sessions included.', 'Good health - regular fitness routine'),
(4, 4, 4, '2025-12-01', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No medical concerns'),
(5, 5, 5, '2025-01-01', 'Annual VIP membership with full access to all facilities and classes.', 'Excellent physical condition'),
(6, 6, 6, '2025-11-20', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'Previous shoulder injury - cleared for gym activities'),
(7, 7, 7, '2025-10-15', 'Premium membership with access to group classes.', 'No restrictions'),
(8, 8, 8, '2025-12-05', 'Basic membership agreement.', 'First-time gym member'),
(9, 9, 9, '2025-11-01', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No health issues reported'),
(10, 10, 10, '2025-08-01', 'Elite 6-month membership with personal training.', 'Training for marathon'),
(11, 11, 11, '2025-12-03', 'Basic membership agreement.', 'No medical history provided'),
(12, 12, 12, '2025-09-20', 'Premium membership agreement.', 'Good overall health'),
(13, 13, 13, '2025-11-10', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No concerns'),
(14, 14, 14, '2025-02-01', 'Annual VIP membership with full benefits.', 'Regular gym-goer'),
(15, 15, 15, '2025-12-08', 'Basic membership agreement.', 'Beginner - needs orientation'),
(16, 16, 16, '2025-10-01', 'Premium membership with class access.', 'No medical issues'),
(17, 17, 17, '2025-07-01', 'Elite membership with PT sessions.', 'Advanced fitness level'),
(18, 18, 18, '2025-11-25', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'No restrictions'),
(19, 19, 19, '2025-12-06', 'Basic membership agreement.', 'Good health'),
(20, 20, 20, '2025-10-10', 'Premium membership agreement.', 'No health concerns'),
(21, 28, 31, '2025-12-09', 'Standard gym membership agreement. Member agrees to follow gym rules and regulations.', 'Pending medical clearance'),
(22, 29, 32, '2025-12-10', 'Basic membership agreement.', 'Student member - active lifestyle'),
(23, 30, 33, '2025-12-10', 'Premium membership agreement.', 'No medical history on file');

-- =====================================================
-- 10. UPDATE AUTO_INCREMENT VALUES
-- =====================================================
ALTER TABLE `roles` AUTO_INCREMENT = 4;
ALTER TABLE `staff` AUTO_INCREMENT = 6;
ALTER TABLE `paymentmethods` AUTO_INCREMENT = 3;
ALTER TABLE `plan` AUTO_INCREMENT = 7;
ALTER TABLE `member` AUTO_INCREMENT = 31;
ALTER TABLE `membership` AUTO_INCREMENT = 41;
ALTER TABLE `payment` AUTO_INCREMENT = 53;
ALTER TABLE `agreement` AUTO_INCREMENT = 24;

-- =====================================================
-- DEMONSTRATION DATA SUMMARY
-- =====================================================
-- 
-- ROLES: 3 (Admin, Manager, Receptionist)
-- STAFF: 5 members with different roles
-- PAYMENT METHODS: 2 (Cash, GCash)
-- PLANS: 6 active plans (15 days to 1 year)
-- MEMBERS: 30 total
--   - Active: 20
--   - Inactive: 7
--   - Pending: 3
-- MEMBERSHIPS: 40 records
--   - Active: 20
--   - Expired: 10
--   - Cancelled: 3
--   - Pending: 3
--   - Renewal histories: 7
-- PAYMENTS: 52 transactions
--   - Completed: 46
--   - Pending: 3
--   - Failed: 2
--   - Refunded: 1
-- AGREEMENTS: 23 signed agreements
--
-- KEY FEATURES DEMONSTRATED:
-- ✓ Multiple membership statuses
-- ✓ Membership renewals and histories
-- ✓ Various payment methods and statuses
-- ✓ Failed and refunded transactions
-- ✓ Partial and installment payments
-- ✓ Different plan types and durations
-- ✓ Member lifecycle (Active → Inactive → Renewal)
-- ✓ Staff with different roles and access levels
-- ✓ Emergency contact information
-- ✓ Payment reference numbers
-- ✓ Realistic dates and timestamps
-- =====================================================

COMMIT;

-- End of demonstration database
-- Password for all staff accounts: "password"
-- Login as: admin / password (Full access)
--          manager / password (Manager access)
--          receptionist / password (Receptionist access)