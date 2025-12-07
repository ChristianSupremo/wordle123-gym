<!-- EDIT MEMBERSHIP MODAL -->
<div class="modal-overlay" id="membershipEditModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Edit Membership</h5>
                <button type="button" class="modal-close-modern" onclick="closeMembershipEditModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="editMembershipForm" method="POST" action="memberships.php">
                <div class="modal-body-modern">

                    <!-- LOADING STATE -->
                    <div id="edit-membership-loading" class="loading-state-modern">
                        <div class="spinner-modern"></div>
                        <p>Loading membership details...</p>
                    </div>

                    <!-- FORM CONTENT -->
                    <div id="edit-membership-form" style="display:none;">
                        <input type="hidden" name="membership_id" id="edit_membership_id">

                        <!-- Membership Information Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-card-checklist"></i> Membership Information
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="edit_member_search" class="form-label-modern">
                                        <i class="bi bi-person"></i> Member
                                        <span class="required">*</span>
                                    </label>
                                    <div class="searchable-select-wrapper">
                                        <input 
                                            type="text" 
                                            id="edit_member_search" 
                                            class="form-control-modern searchable-select-input" 
                                            placeholder="Search member..."
                                            autocomplete="off"
                                        >
                                        <input type="hidden" name="MemberID" id="edit_member_id" required>
                                        <div class="searchable-select-dropdown" id="edit_member_dropdown">
                                            <?php foreach ($members as $mem): ?>
                                                <div class="searchable-select-option" 
                                                     data-value="<?= $mem['MemberID'] ?>"
                                                     data-text="<?= htmlspecialchars($mem['FirstName'] . ' ' . $mem['LastName']) ?>">
                                                    <?= htmlspecialchars($mem['FirstName'] . ' ' . $mem['LastName']) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_plan_id" class="form-label-modern">
                                        <i class="bi bi-tag"></i> Plan
                                        <span class="required">*</span>
                                    </label>
                                    <div class="searchable-select-wrapper">
                                        <input 
                                            type="text" 
                                            id="edit_plan_search" 
                                            class="form-control-modern searchable-select-input" 
                                            placeholder="Search plan..."
                                            autocomplete="off"
                                        >

                                        <input type="hidden" name="PlanID" id="edit_plan_id" required>

                                        <div class="searchable-select-dropdown" id="edit_plan_dropdown">
                                            <?php foreach ($plans as $plan): ?>
                                                <div class="searchable-select-option"
                                                    data-value="<?= $plan['PlanID'] ?>"
                                                    data-text="<?= htmlspecialchars($plan['PlanName'] . ' - ₱' . number_format($plan['Rate'], 2)) ?>">
                                                    <?= htmlspecialchars($plan['PlanName'] . ' - ₱' . number_format($plan['Rate'], 2)) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_start_date" class="form-label-modern">
                                        <i class="bi bi-calendar-check"></i> Start Date
                                        <span class="required">*</span>
                                    </label>
                                    <input type="date" class="form-control-modern" id="edit_start_date" 
                                           name="StartDate" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_status_search" class="form-label-modern">
                                        <i class="bi bi-check-circle"></i> Status
                                        <span class="required">*</span>
                                    </label>

                                    <div class="searchable-select-wrapper">
                                        <input 
                                            type="text" 
                                            id="edit_status_search" 
                                            class="form-control-modern searchable-select-input" 
                                            placeholder="Search status..."
                                            autocomplete="off"
                                        >

                                        <input type="hidden" name="Status" id="edit_status" required>

                                        <div class="searchable-select-dropdown" id="edit_status_dropdown">
                                            <div class="searchable-select-option" data-value="Active" data-text="Active">Active</div>
                                            <div class="searchable-select-option" data-value="Expired" data-text="Expired">Expired</div>
                                            <div class="searchable-select-option" data-value="Cancelled" data-text="Cancelled">Cancelled</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Message -->
                            <div style="margin-top: 20px; padding: 12px 16px; background: #ebf8ff; border-radius: 8px; border-left: 4px solid #4299e1;">
                                <div style="display: flex; gap: 12px; align-items: start;">
                                    <i class="bi bi-info-circle" style="color: #4299e1; font-size: 20px; flex-shrink: 0;"></i>
                                    <div style="font-size: 13px; color: #2c5282; line-height: 1.6;">
                                        <strong>Note:</strong> The end date will be automatically calculated based on the selected plan's duration. 
                                        Status will be auto-computed unless you select "Cancelled".
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Error state -->
                    <div class="error-state-modern" id="edit-membership-error" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load membership details</p>
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="closeMembershipEditModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="saveMembershipEditBtn">
                        <i class="bi bi-check-lg"></i> Update Membership
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>