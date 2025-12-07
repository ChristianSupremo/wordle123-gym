<?php
// This file should be included after payment_modal_helpers.php is loaded
// and after $payment_methods are fetched
?>

<!-- EDIT PAYMENT MODAL -->
<div class="modal-overlay" id="paymentEditModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Edit Payment</h5>
                <button type="button" class="modal-close-modern" onclick="PaymentEditModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="editPaymentForm" method="POST" action="payments.php">
                <div class="modal-body-modern">

                    <!-- LOADING STATE -->
                    <div id="edit-payment-loading" class="loading-state-modern">
                        <div class="spinner-modern"></div>
                        <p>Loading payment details...</p>
                    </div>

                    <!-- FORM CONTENT -->
                    <div id="edit-payment-form" style="display:none;">
                        <input type="hidden" name="payment_id" id="edit_payment_id">
                        <input type="hidden" name="MembershipID" id="edit_membership_id">

                        <!-- READ-ONLY SECTION -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-info-circle"></i> Payment Information (Read-Only)
                            </h4>
                            
                            <div class="readonly-info-grid">
                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-person"></i> Member
                                    </label>
                                    <div class="readonly-value" id="edit_member_name"></div>
                                </div>

                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-tag"></i> Plan
                                    </label>
                                    <div class="readonly-value" id="edit_plan_name"></div>
                                </div>

                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-calendar"></i> Payment Date
                                    </label>
                                    <div class="readonly-value" id="edit_payment_date"></div>
                                </div>

                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-person-badge"></i> Processed By
                                    </label>
                                    <div class="readonly-value" id="edit_staff_name"></div>
                                </div>
                            </div>
                        </div>

                        <!-- EDITABLE SECTION -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-pencil-square"></i> Editable Payment Details
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern" id="edit_payment_method_group">
                                    <label for="edit_payment_method" class="form-label-modern">
                                        <i class="bi bi-wallet2"></i> Payment Method
                                        <span class="required">*</span>
                                    </label>
                                    <select name="PaymentMethod" id="edit_payment_method" class="form-control-modern" required>
                                        <?php renderPaymentMethodOptions($payment_methods); ?>
                                    </select>
                                </div>

                                <div class="form-group-modern" id="edit_payment_amount_group">
                                    <label for="edit_payment_amount" class="form-label-modern">
                                        <i class="bi bi-cash"></i> Amount Paid
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" step="0.01" class="form-control-modern" 
                                           id="edit_payment_amount" name="AmountPaid" 
                                           placeholder="0.00" required>
                                </div>

                                <div class="form-group-modern" id="edit_payment_status_group">
                                    <label for="edit_payment_status" class="form-label-modern">
                                        <i class="bi bi-check-circle"></i> Payment Status
                                    </label>
                                    <select name="PaymentStatus" id="edit_payment_status" class="form-control-modern">
                                        <option value="Completed">Completed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Failed">Failed</option>
                                    </select>
                                </div>

                                <div class="form-group-modern" id="edit_payment_reference_group" style="display:none;">
                                    <label for="edit_payment_reference" class="form-label-modern">
                                        <i class="bi bi-hash"></i> Reference Number
                                        <span class="required" id="edit_reference_required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" 
                                           id="edit_payment_reference" name="ReferenceNumber" 
                                           placeholder="Enter reference number">
                                </div>

                                <div class="form-group-modern full-width">
                                    <label for="edit_payment_remarks" class="form-label-modern">
                                        <i class="bi bi-sticky"></i> Remarks
                                    </label>
                                    <textarea class="form-control-modern" id="edit_payment_remarks" 
                                              name="Remarks" rows="3" 
                                              placeholder="Optional notes or remarks..."></textarea>
                                </div>
                            </div>

                            <!-- Info Message -->
                            <div id="edit_payment_info_box" class="payment-info-message success">
                                <div class="payment-info-content">
                                    <i class="bi bi-check-circle payment-info-icon success" id="edit_payment_info_icon"></i>
                                    <div class="payment-info-text success" id="edit_payment_info_text">
                                        <strong>Payment Recording:</strong> This payment record will be updated.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error state -->
                    <div class="error-state-modern" id="edit-payment-error" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load payment details</p>
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="PaymentEditModal.close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="savePaymentEditBtn">
                        <i class="bi bi-check-lg"></i> Update Payment
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Payment Edit Modal Controller
const PaymentEditModal = new ModalManager('paymentEditModal');

// Open edit payment modal
async function editPayment(paymentId) {
    PaymentEditModal.open();
    
    // Show loading state
    showLoadingState('edit-payment-loading', 'edit-payment-form', 'edit-payment-error');
    
    try {
        const payment = await fetchPaymentDetails(paymentId);
        
        // Show form
        showFormState('edit-payment-loading', 'edit-payment-form', 'edit-payment-error');
        
        // Populate READ-ONLY fields
        document.getElementById('edit_payment_id').value = payment.PaymentID;
        document.getElementById('edit_membership_id').value = payment.MembershipID;
        document.getElementById('edit_member_name').textContent = payment.MemberName;
        document.getElementById('edit_plan_name').textContent = payment.PlanName;
        document.getElementById('edit_payment_date').textContent = formatPaymentDate(payment.PaymentDate);
        document.getElementById('edit_staff_name').textContent = payment.StaffName || 'N/A';
        
        // Populate EDITABLE fields
        document.getElementById('edit_payment_method').value = payment.PaymentMethodID;
        document.getElementById('edit_payment_amount').value = parseFloat(payment.AmountPaid).toFixed(2);
        document.getElementById('edit_payment_reference').value = payment.ReferenceNumber || '';
        document.getElementById('edit_payment_status').value = payment.PaymentStatus;
        document.getElementById('edit_payment_remarks').value = payment.Remarks || '';
        
        // Trigger change events
        document.getElementById('edit_payment_method').dispatchEvent(new Event('change'));
        document.getElementById('edit_payment_status').dispatchEvent(new Event('change'));
        
    } catch (error) {
        showErrorState('edit-payment-loading', 'edit-payment-form', 'edit-payment-error');
        if (window.toast) {
            toast.error('Failed to load payment details. Please try again.', 5000);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Payment method change handler
    const editPaymentMethodSelect = document.getElementById('edit_payment_method');
    if (editPaymentMethodSelect) {
        editPaymentMethodSelect.addEventListener('change', function() {
            toggleReferenceField(
                'edit_payment_method',
                'edit_payment_reference_group',
                'edit_payment_reference',
                'edit_reference_required'
            );
        });
    }
    
    // Payment status change handler
    const editStatusSelect = document.getElementById('edit_payment_status');
    if (editStatusSelect) {
        editStatusSelect.addEventListener('change', function() {
            updatePaymentInfoMessage(
                'edit_payment_status',
                'edit_payment_info_box',
                'edit_payment_info_icon',
                'edit_payment_info_text'
            );
        });
    }
    
    // Form submission
    handleFormSubmit(
        'editPaymentForm',
        'savePaymentEditBtn',
        'Payment updated successfully! Refreshing...'
    );
});
</script>