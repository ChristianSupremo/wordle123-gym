<!-- VIEW PAYMENT MODAL -->
<div class="modal-overlay" id="paymentViewModal">
    <div class="modal-dialog-modern">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Payment Details</h5>
                <button type="button" class="modal-close-modern" onclick="PaymentViewModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="modal-body-modern">

                <!-- LOADING STATE -->
                <div id="payment-details-loading" class="loading-state-modern">
                    <div class="spinner-modern"></div>
                    <p>Loading payment details...</p>
                </div>

                <!-- CONTENT -->
                <div id="payment-details" style="display:none;">

                    <!-- Payment Header -->
                    <div class="payment-header-modern">
                        <div class="payment-amount-display">
                            <div class="payment-amount-label">Amount Paid</div>
                            <div class="payment-amount-value" id="view-payment-amount"></div>
                        </div>
                        <span class="status-badge-modal" id="view-payment-status-badge"></span>
                    </div>

                    <!-- Payment Information -->
                    <div class="info-grid-modern">
                        <div class="info-item-modern">
                            <div class="info-label-modern">
                                <i class="bi bi-calendar"></i> Payment Date
                            </div>
                            <div class="info-value-modern" id="view-payment-date"></div>
                        </div>

                        <div class="info-item-modern">
                            <div class="info-label-modern">
                                <i class="bi bi-person"></i> Member Name
                            </div>
                            <div class="info-value-modern" id="view-payment-member"></div>
                        </div>

                        <div class="info-item-modern">
                            <div class="info-label-modern">
                                <i class="bi bi-tag"></i> Membership / Plan
                            </div>
                            <div class="info-value-modern" id="view-payment-plan"></div>
                        </div>

                        <div class="info-item-modern">
                            <div class="info-label-modern">
                                <i class="bi bi-wallet2"></i> Payment Method
                            </div>
                            <div class="info-value-modern" id="view-payment-method"></div>
                        </div>

                        <div class="info-item-modern">
                            <div class="info-label-modern">
                                <i class="bi bi-hash"></i> Reference Number
                            </div>
                            <div class="info-value-modern" id="view-payment-reference"></div>
                        </div>

                        <div class="info-item-modern">
                            <div class="info-label-modern">
                                <i class="bi bi-person-badge"></i> Processed By
                            </div>
                            <div class="info-value-modern" id="view-payment-staff"></div>
                        </div>

                        <div class="info-item-modern full-width">
                            <div class="info-label-modern">
                                <i class="bi bi-sticky"></i> Remarks
                            </div>
                            <div class="info-value-modern" id="view-payment-remarks"></div>
                        </div>
                    </div>

                </div>

                <!-- Error state -->
                <div class="error-state-modern" id="payment-details-error" style="display: none;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <p>Failed to load payment details</p>
                </div>

            </div>

            <div class="modal-footer-modern">
                <button type="button" class="btn-secondary-modern" onclick="PaymentViewModal.close()">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Payment View Modal Controller
const PaymentViewModal = new ModalManager('paymentViewModal');

// Open payment view modal
async function viewPaymentDetails(paymentId) {
    PaymentViewModal.open();
    
    // Show loading state
    showLoadingState('payment-details-loading', 'payment-details', 'payment-details-error');
    
    try {
        const payment = await fetchPaymentDetails(paymentId);
        
        // Show content
        showFormState('payment-details-loading', 'payment-details', 'payment-details-error');
        
        // Payment Amount
        document.getElementById('view-payment-amount').textContent = 
            '₱' + parseFloat(payment.AmountPaid).toFixed(2);
        
        // Status Badge
        const statusBadge = document.getElementById('view-payment-status-badge');
        statusBadge.textContent = payment.PaymentStatus;
        statusBadge.className = 'status-badge-modal ' + payment.PaymentStatus.toLowerCase();
        
        // Payment Details
        document.getElementById('view-payment-date').textContent = formatPaymentDate(payment.PaymentDate);
        document.getElementById('view-payment-member').textContent = payment.MemberName || 'N/A';
        document.getElementById('view-payment-plan').textContent = payment.PlanName || 'N/A';
        document.getElementById('view-payment-method').textContent = payment.PaymentMethod || 'N/A';
        document.getElementById('view-payment-reference').textContent = payment.ReferenceNumber || 'N/A';
        document.getElementById('view-payment-staff').textContent = payment.StaffName || 'N/A';
        document.getElementById('view-payment-remarks').textContent = payment.Remarks || 'No remarks';
        
    } catch (error) {
        showErrorState('payment-details-loading', 'payment-details', 'payment-details-error');
        if (window.toast) {
            toast.error('Failed to load payment details. Please try again.', 5000);
        }
    }
}
</script>