<!-- RENEW MEMBERSHIP MODAL -->
<div class="modal-overlay" id="membershipRenewModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Renew Membership</h5>
                <button type="button" class="modal-close-modern" onclick="closeMembershipRenewModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="renewMembershipForm" method="POST" action="memberships.php">
                <div class="modal-body-modern">

                    <!-- LOADING STATE -->
                    <div id="renew-membership-loading" class="loading-state-modern">
                        <div class="spinner-modern"></div>
                        <p>Loading membership details...</p>
                    </div>

                    <!-- FORM CONTENT -->
                    <div id="renew-membership-form" style="display:none;">
                        <input type="hidden" name="renew_member_id" id="renew_member_id">

                        <!-- READ-ONLY SECTION -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-info-circle"></i> Current Membership Information
                            </h4>
                            
                            <div class="readonly-info-grid">
                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-person"></i> Member Name
                                    </label>
                                    <div class="readonly-value" id="renew_member_name"></div>
                                </div>

                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-tag"></i> Current Plan
                                    </label>
                                    <div class="readonly-value" id="renew_current_plan"></div>
                                </div>

                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-calendar-x"></i> Current End Date
                                    </label>
                                    <div class="readonly-value" id="renew_current_end_date"></div>
                                </div>

                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-calendar-event"></i> Computed New End Date
                                    </label>
                                    <div class="readonly-value" id="renew_computed_end_date">Will be calculated</div>
                                </div>

                                <div class="readonly-info-item">
                                    <label class="readonly-label">
                                        <i class="bi bi-cash"></i> Plan Rate
                                    </label>
                                    <div class="readonly-value" id="renew_plan_rate"></div>
                                </div>
                            </div>
                        </div>

                        <!-- EDITABLE SECTION -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-pencil-square"></i> Renewal Details
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="renew_plan_id" class="form-label-modern">
                                        <i class="bi bi-tag"></i> Select New Plan
                                        <span class="required">*</span>
                                    </label>
                                    <select name="PlanID" id="renew_plan_id" class="form-control-modern" required>
                                        <option value="">Select Plan...</option>
                                        <?php foreach ($plans as $plan): ?>
                                            <option value="<?= $plan['PlanID'] ?>" data-rate="<?= $plan['Rate'] ?>">
                                                <?= htmlspecialchars($plan['PlanName'] . ' - ₱' . number_format($plan['Rate'], 2)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="renew_start_date" class="form-label-modern">
                                        <i class="bi bi-calendar-check"></i> Start Date
                                        <span class="required">*</span>
                                    </label>
                                    <input type="date" class="form-control-modern" id="renew_start_date" 
                                           name="StartDate" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="renew_payment_method" class="form-label-modern">
                                        <i class="bi bi-credit-card"></i> Payment Method
                                        <span class="required">*</span>
                                    </label>
                                    <select name="PaymentMethod" id="renew_payment_method" class="form-control-modern" required>
                                        <option value="">Select Method...</option>
                                        <option value="Cash">Cash</option>
                                        <option value="GCash">GCash</option>
                                    </select>
                                </div>

                                <div class="form-group-modern" id="renew_reference_group" style="display:none;">
                                    <label for="renew_reference_number" class="form-label-modern">
                                        <i class="bi bi-hash"></i> Reference Number
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="renew_reference_number" 
                                           name="ReferenceNumber" placeholder="Enter reference number">
                                </div>

                                <div class="form-group-modern full-width">
                                    <label for="renew_notes" class="form-label-modern">
                                        <i class="bi bi-sticky"></i> Notes
                                    </label>
                                    <textarea class="form-control-modern" id="renew_notes" name="Notes" 
                                              rows="3" placeholder="Optional staff notes..."></textarea>
                                </div>
                            </div>

                            <!-- Info Message -->
                            <div style="margin-top: 20px; padding: 12px 16px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #22c55e;">
                                <div style="display: flex; gap: 12px; align-items: start;">
                                    <i class="bi bi-check-circle" style="color: #22c55e; font-size: 20px; flex-shrink: 0;"></i>
                                    <div style="font-size: 13px; color: #166534; line-height: 1.6;">
                                        <strong>Renewal Process:</strong> This will create a new membership record starting from the selected date. 
                                        The end date will be automatically calculated based on the plan duration. A payment record will be created automatically.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Error state -->
                    <div class="error-state-modern" id="renew-membership-error" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load membership details</p>
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="closeMembershipRenewModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="saveMembershipRenewBtn">
                        <i class="bi bi-arrow-repeat"></i> Renew Membership
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="assets/js/membership_renew.js"></script>