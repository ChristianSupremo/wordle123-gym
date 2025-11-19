<?php
// This script will handle background updates for membership status and plan.
// It expects a POST request.

header('Content-Type: application/json'); // Set the response type to JSON

require_once '../config/db.php';
require_once '../includes/functions.php';

// Basic security check
check_login();

// Get the data from the POST request
 $membership_id = $_POST['membership_id'] ?? null;
 $field_to_update = $_POST['field'] ?? null; // 'status' or 'plan'
 $new_value = $_POST['value'] ?? null;

 $response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($membership_id && $field_to_update && $new_value) {
    try {
        if ($field_to_update === 'status') {
            // Update the status
            $sql = "UPDATE Membership SET Status = ? WHERE MembershipID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_value, $membership_id]);
            $response = ['status' => 'success', 'message' => 'Status updated successfully.'];

        } elseif ($field_to_update === 'plan') {
            // --- Plan change is more complex ---
            // It requires recalculating the end date and creating a new payment/agreement record.
            // For simplicity here, we will just update the plan ID and end date.
            // A full implementation would also prompt for a new payment.

            // 1. Get new plan details
            $stmt = $pdo->prepare("SELECT Duration, PlanType, Rate FROM Plan WHERE PlanID = ?");
            $stmt->execute([$new_value]);
            $new_plan = $stmt->fetch();

            if ($new_plan) {
                // 2. Get the current membership to find the start date
                $stmt = $pdo->prepare("SELECT StartDate FROM Membership WHERE MembershipID = ?");
                $stmt->execute([$membership_id]);
                $current_membership = $stmt->fetch();
                $start_date = $current_membership['StartDate'];

                // 3. Calculate new end date
                $duration = $new_plan['Duration'];
                $plan_type = strtolower($new_plan['PlanType']);
                if ($plan_type == 'days') {
                    $end_date = date('Y-m-d', strtotime("+$duration days", strtotime($start_date)));
                } elseif ($plan_type == 'months') {
                    $end_date = date('Y-m-d', strtotime("+$duration months", strtotime($start_date)));
                } else {
                    $end_date = date('Y-m-d', strtotime("+$duration months", strtotime($start_date)));
                }

                // 4. Update the membership record
                $sql = "UPDATE Membership SET PlanID = ?, EndDate = ? WHERE MembershipID = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$new_value, $end_date, $membership_id]);
                $response = ['status' => 'success', 'message' => 'Plan updated successfully. New end date is ' . date('M j, Y', strtotime($end_date))];
            } else {
                $response['message'] = 'New plan not found.';
            }
        } else {
            $response['message'] = 'Invalid field specified.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Send the response back as JSON
echo json_encode($response);