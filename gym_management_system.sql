-- ============================================================
-- DROP TABLES (only run if you want to reset the schema)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Payment;
DROP TABLE IF EXISTS Agreement;
DROP TABLE IF EXISTS Membership;
DROP TABLE IF EXISTS Staff;
DROP TABLE IF EXISTS Roles;
DROP TABLE IF EXISTS PaymentMethods;
DROP TABLE IF EXISTS Plan;
DROP TABLE IF EXISTS Member;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TABLE: Member
-- ============================================================
CREATE TABLE Member (
    MemberID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50),
    LastName VARCHAR(50),
    Address VARCHAR(255),
    City VARCHAR(100),
    Province VARCHAR(100),
    Zipcode VARCHAR(20),
    Gender ENUM('Male','Female','Other'),
    DateOfBirth DATE,
    PhoneNo VARCHAR(20),
    Email VARCHAR(100),
    JoinDate DATE,
    EmergencyContactName VARCHAR(100),
    EmergencyContactNumber VARCHAR(20),
    MembershipStatus ENUM('Active','Inactive','Pending') DEFAULT 'Active'
);

-- ============================================================
-- TABLE: Roles
-- ============================================================
CREATE TABLE Roles (
    RoleID INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50),
    Description TEXT
);

-- ============================================================
-- TABLE: Staff
-- ============================================================
CREATE TABLE Staff (
    StaffID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100),
    RoleID INT,
    Username VARCHAR(50),
    Password VARCHAR(255),
    HireDate DATE,
    Status ENUM('Active','Inactive') DEFAULT 'Active',
    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID)
);

-- ============================================================
-- TABLE: Plan
-- ============================================================
CREATE TABLE Plan (
    PlanID INT AUTO_INCREMENT PRIMARY KEY,
    PlanName VARCHAR(100),
    Description TEXT,
    PlanType VARCHAR(50),
    Duration INT,
    Rate DECIMAL(10,2),
    IsActive TINYINT(1) DEFAULT 1
);

-- ============================================================
-- TABLE: PaymentMethods
-- ============================================================
CREATE TABLE PaymentMethods (
    PaymentMethodID INT AUTO_INCREMENT PRIMARY KEY,
    MethodName VARCHAR(50),
    IsActive TINYINT(1) DEFAULT 1,
    Description TEXT
);

-- ============================================================
-- TABLE: Membership
-- ============================================================
CREATE TABLE Membership (
    MembershipID INT AUTO_INCREMENT PRIMARY KEY,
    MemberID INT NOT NULL,
    PlanID INT NOT NULL,
    StaffID INT NOT NULL,
    StartDate DATE,
    EndDate DATE,
    RenewalDate DATE,
    Status ENUM('Active','Expired','Cancelled','Pending') DEFAULT 'Active',
    FOREIGN KEY (MemberID) REFERENCES Member(MemberID),
    FOREIGN KEY (PlanID) REFERENCES Plan(PlanID),
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID)
);

-- ============================================================
-- TABLE: Agreement
-- ============================================================
CREATE TABLE Agreement (
    AgreementID INT AUTO_INCREMENT PRIMARY KEY,
    MemberID INT NOT NULL,
    MembershipID INT NOT NULL,
    AgreementDate DATE,
    Terms TEXT,
    PhysicalConditionDetails TEXT,
    FOREIGN KEY (MemberID) REFERENCES Member(MemberID),
    FOREIGN KEY (MembershipID) REFERENCES Membership(MembershipID)
);

-- ============================================================
-- TABLE: Payment
-- ============================================================
CREATE TABLE Payment (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    MembershipID INT NOT NULL,
    PaymentMethodID INT NOT NULL,
    StaffID INT NOT NULL,
    PaymentDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    AmountPaid DECIMAL(10,2) NOT NULL,
    ReferenceNumber VARCHAR(100),
    PaymentStatus ENUM('Pending','Completed','Failed','Refunded') DEFAULT 'Completed',
    Remarks TEXT,
    FOREIGN KEY (MembershipID) REFERENCES Membership(MembershipID),
    FOREIGN KEY (PaymentMethodID) REFERENCES PaymentMethods(PaymentMethodID),
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID)
);
