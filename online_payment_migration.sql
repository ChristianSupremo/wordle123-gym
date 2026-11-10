-- Online QR payment submission and admin approval
-- Run once in phpMyAdmin after importing gym_db.
ALTER TABLE membership MODIFY StaffID int(11) DEFAULT NULL;
ALTER TABLE payment MODIFY StaffID int(11) DEFAULT NULL;
ALTER TABLE payment ADD COLUMN ReceiptImage varchar(255) DEFAULT NULL AFTER ReferenceNumber;

-- The online page uses the existing GCash payment method (PaymentMethodID = 2).
