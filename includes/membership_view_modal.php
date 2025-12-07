<!-- VIEW MEMBERSHIP MODAL -->
<div class="modal-overlay" id="membershipViewModal">
    <div class="modal-dialog-modern">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Membership Details</h5>
                <button type="button" class="modal-close-modern" onclick="closeMembershipViewModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="modal-body-modern">

                <!-- LOADING STATE -->
                <div id="membership-details-loading" class="loading-state-modern">
                    <div class="spinner-modern"></div>
                    <p>Loading membership details...</p>
                </div>

                <!-- CONTENT (hidden by default) -->
                <div id="membership-details" style="display:none;">

                    <!-- MEMBER HEADER + STATUS -->
                    <div class="member-header-modern">
                        <img id="view-membership-photo" src="" 
                             class="member-avatar-modern"
                             alt="Member Photo">
                        <div class="member-header-info-modern">
                            <h4 class="member-name-modern" id="view-membership-member-name"></h4>
                            <span class="status-badge-modal" id="view-membership-status-badge"></span>
                        </div>
                    </div>

                    <!-- TABS -->
                    <ul class="tabs-modern" role="tablist">
                        <li class="tab-item-modern">
                            <a class="tab-link-modern active" onclick="switchMembershipTab(event, 'tab-membership-info')">
                                <i class="bi bi-card-checklist"></i> Membership Info
                            </a>
                        </li>
                        <li class="tab-item-modern">
                            <a class="tab-link-modern" onclick="switchMembershipTab(event, 'tab-member-profile')">
                                <i class="bi bi-person"></i> Member Profile
                            </a>
                        </li>
                        <li class="tab-item-modern">
                            <a class="tab-link-modern" onclick="switchMembershipTab(event, 'tab-payment-history')">
                                <i class="bi bi-credit-card"></i> Payment History
                            </a>
                        </li>
                    </ul>

                    <!-- TAB CONTENT -->
                    <div class="tab-content-modern">

                        <!-- MEMBERSHIP INFO TAB -->
                        <div class="tab-pane-modern active" id="tab-membership-info">
                            <div class="info-grid-modern">

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-tag"></i> Plan Name</div>
                                    <div class="info-value-modern" id="view-membership-plan-name"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-cash"></i> Plan Rate</div>
                                    <div class="info-value-modern" id="view-membership-plan-rate"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-calendar-check"></i> Start Date</div>
                                    <div class="info-value-modern" id="view-membership-start-date"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-calendar-x"></i> End Date</div>
                                    <div class="info-value-modern" id="view-membership-end-date"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-hourglass-split"></i> Duration</div>
                                    <div class="info-value-modern" id="view-membership-duration"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-clock-history"></i> Days Remaining</div>
                                    <div class="info-value-modern" id="view-membership-days-left"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-person-badge"></i> Registered By</div>
                                    <div class="info-value-modern" id="view-membership-staff"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-check-circle"></i> Status</div>
                                    <div class="info-value-modern">
                                        <span class="status-badge" id="view-membership-status"></span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- MEMBER PROFILE TAB -->
                        <div class="tab-pane-modern" id="tab-member-profile">
                            <div class="info-grid-modern">

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-envelope"></i> Email</div>
                                    <div class="info-value-modern" id="view-membership-email"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-telephone"></i> Phone</div>
                                    <div class="info-value-modern" id="view-membership-phone"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-gender-ambiguous"></i> Gender</div>
                                    <div class="info-value-modern" id="view-membership-gender"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-calendar"></i> Date of Birth</div>
                                    <div class="info-value-modern" id="view-membership-dob"></div>
                                </div>

                                <div class="info-item-modern full-width">
                                    <div class="info-label-modern"><i class="bi bi-house"></i> Address</div>
                                    <div class="info-value-modern" id="view-membership-address"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-person-badge"></i> Emergency Contact</div>
                                    <div class="info-value-modern" id="view-membership-emergency-name"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern"><i class="bi bi-telephone-plus"></i> Emergency Number</div>
                                    <div class="info-value-modern" id="view-membership-emergency-number"></div>
                                </div>

                            </div>
                        </div>

                        <!-- PAYMENT HISTORY TAB -->
                        <div class="tab-pane-modern" id="tab-payment-history">
                            <div class="table-container-modern">
                                <table class="table-modern">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="membership-payments-table"></tbody>
                                </table>
                            </div>
                        </div>

                    </div> <!-- tab-content-modern -->

                    <!-- ERROR STATE (inside content, hidden by default) -->
                    <div class="error-state-modern" id="membership-details-error" style="display:none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load membership details</p>
                    </div>

                </div>

            </div>
            
        </div>
    </div>
</div>
<script src="assets/js/membership_view_modal.js"></script>