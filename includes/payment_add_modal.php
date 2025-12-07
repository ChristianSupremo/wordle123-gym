<?php
// This file should be included after payment_modal_helpers.php is loaded
// and after $active_memberships and $payment_methods are fetched
?>

<!-- ADD PAYMENT MODAL -->
<div class="modal-overlay" id="paymentAddModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Record New Payment</h5>
                <button type="button" class="modal-close-modern" onclick="PaymentAddModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="addPaymentForm" method="POST" action="payments.php">
                <div class="modal-body-modern">

                    <div id="add-payment-form">

                        <!-- Membership Selection Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-person-circle"></i> Membership Selection
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern full-width">
                                    <label for="add_payment_membership_search" class="form-label-modern">
                                        <i class="bi bi-card-checklist"></i> Select Membership
                                        <span class="required">*</span>
                                    </label>
                                    <div class="searchable-select-wrapper">
                                        <input 
                                            type="text" 
                                            id="add_payment_membership_search" 
                                            class="form-control-modern searchable-select-input" 
                                            placeholder="Search member or plan..."
                                            autocomplete="off"
                                        >
                                        <input type="hidden" name="MembershipID" id="add_payment_membership_id" required>
                                        <div class="searchable-select-dropdown" id="add_payment_membership_dropdown">
                                            <?php renderMembershipOptions($active_memberships); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-credit-card"></i> Payment Details
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="add_payment_method" class="form-label-modern">
                                        <i class="bi bi-wallet2"></i> Payment Method
                                        <span class="required">*</span>
                                    </label>
                                    <select name="PaymentMethod" id="add_payment_method" class="form-control-modern" required>
                                        <?php renderPaymentMethodOptions($payment_methods); ?>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_payment_amount" class="form-label-modern">
                                        <i class="bi bi-cash"></i> Amount Paid
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" step="0.01" class="form-control-modern" 
                                           id="add_payment_amount" name="AmountPaid" 
                                           placeholder="0.00" required>
                                </div>

                                <div class="form-group-modern" id="add_payment_reference_group" style="display:none;">
                                    <label for="add_payment_reference" class="form-label-modern">
                                        <i class="bi bi-hash"></i> Reference Number
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" 
                                           id="add_payment_reference" name="ReferenceNumber" 
                                           placeholder="Enter reference number">
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_payment_status" class="form-label-modern">
                                        <i class="bi bi-check-circle"></i> Payment Status
                                    </label>
                                    <select name="PaymentStatus" id="add_payment_status" class="form-control-modern">
                                        <option value="Completed" selected>Completed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Failed">Failed</option>
                                    </select>
                                    <small class="form-help-text">Use "Failed" to track unsuccessful payment attempts</small>
                                </div>

                                <div class="form-group-modern full-width">
                                    <label for="add_payment_remarks" class="form-label-modern">
                                        <i class="bi bi-sticky"></i> Remarks
                                    </label>
                                    <textarea class="form-control-modern" id="add_payment_remarks" 
                                              name="Remarks" rows="3" 
                                              placeholder="Optional notes or remarks..."></textarea>
                                </div>
                            </div>

                            <!-- Info Message -->
                            <div id="add_payment_info_box" class="payment-info-message success">
                                <div class="payment-info-content">
                                    <i class="bi bi-check-circle payment-info-icon success" id="add_payment_info_icon"></i>
                                    <div class="payment-info-text success" id="add_payment_info_text">
                                        <strong>Payment Recording:</strong> This payment will be recorded with the current date and time. 
                                        The amount will be linked to the selected membership for tracking and accounting purposes.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="PaymentAddModal.close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="savePaymentAddBtn">
                        <i class="bi bi-check-lg"></i> Record Payment
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Payment Add Modal Controller
const PaymentAddModal = new ModalManager('paymentAddModal');

// Variable to track if searchable select is initialized
let isSearchableSelectInitialized = false;

// Open add payment modal
function openAddPaymentModal() {
    PaymentAddModal.open();
    
    // Reset form
    document.getElementById('addPaymentForm').reset();
    document.getElementById('add_payment_membership_search').value = '';
    document.getElementById('add_payment_membership_id').value = '';
    document.getElementById('add_payment_reference_group').style.display = 'none';
    
    // Clear selected state
    document.querySelectorAll('#add_payment_membership_dropdown .searchable-select-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Initialize searchable select only once
    if (!isSearchableSelectInitialized) {
        initSearchableSelect({
            searchInputId: 'add_payment_membership_search',
            hiddenInputId: 'add_payment_membership_id',
            dropdownId: 'add_payment_membership_dropdown',
            additionalFieldId: 'add_payment_amount',
            additionalFieldAttr: 'data-rate'
        });
        isSearchableSelectInitialized = true;
    }
}

// Pre-select membership when clicking the + button
function openAddPaymentModalForMembership(membershipId) {
    openAddPaymentModal();
    
    setTimeout(() => {
        const options = document.querySelectorAll('#add_payment_membership_dropdown .searchable-select-option');
        const targetOption = Array.from(options).find(opt => opt.getAttribute('data-value') == membershipId);
        
        if (targetOption) {
            targetOption.click();
        }
    }, 100);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Payment method change handler
    const paymentMethodSelect = document.getElementById('add_payment_method');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            toggleReferenceField(
                'add_payment_method',
                'add_payment_reference_group',
                'add_payment_reference',
                null
            );
        });
        
        // Trigger initial state
        paymentMethodSelect.dispatchEvent(new Event('change'));
    }
    
    // Payment status change handler
    const statusSelect = document.getElementById('add_payment_status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            updatePaymentInfoMessage(
                'add_payment_status',
                'add_payment_info_box',
                'add_payment_info_icon',
                'add_payment_info_text'
            );
        });
    }
    
    // Form submission
    handleFormSubmit(
        'addPaymentForm',
        'savePaymentAddBtn',
        'Payment recorded successfully! Refreshing...'
    );
    
    // Debug: Check if membership options are in the DOM
    console.log('Membership options count:', document.querySelectorAll('#add_payment_membership_dropdown .searchable-select-option').length);
});
</script>