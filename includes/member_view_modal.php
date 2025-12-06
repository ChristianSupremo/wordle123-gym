<!-- VIEW MEMBER MODAL -->
<div class="modal-overlay" id="memberViewModal">
    <div class="modal-dialog-modern">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Member Profile</h5>
                <button type="button" class="modal-close-modern" onclick="closeModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="modal-body-modern">

                <!-- LOADING STATE -->
                <div id="member-details-loading" class="loading-state-modern">
                    <div class="spinner-modern"></div>
                    <p>Loading member details...</p>
                </div>

                <!-- CONTENT -->
                <div id="member-details" style="display:none;">

                    <!-- PHOTO + NAME + STATUS -->
                    <div class="member-header-modern">
                        <img id="view-photo" src="" 
                             class="member-avatar-modern"
                             alt="Member Photo">
                        <div class="member-header-info-modern">
                            <h4 class="member-name-modern" id="view-name"></h4>
                            <span class="status-badge-modal" id="view-status-badge"></span>
                        </div>
                    </div>

                    <!-- TABS -->
                    <ul class="tabs-modern" role="tablist">
                        <li class="tab-item-modern">
                            <a class="tab-link-modern active" data-tab="tab-profile" onclick="switchTab(event, 'tab-profile')">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>
                        <li class="tab-item-modern">
                            <a class="tab-link-modern" data-tab="tab-memberships" onclick="switchTab(event, 'tab-memberships')">
                                <i class="bi bi-card-list"></i> Memberships
                            </a>
                        </li>
                        <li class="tab-item-modern">
                            <a class="tab-link-modern" data-tab="tab-payments" onclick="switchTab(event, 'tab-payments')">
                                <i class="bi bi-credit-card"></i> Payments
                            </a>
                        </li>
                    </ul>

                    <!-- TAB CONTENT -->
                    <div class="tab-content-modern">

                        <!-- PROFILE TAB -->
                        <div class="tab-pane-modern active" id="tab-profile">
                            <div class="info-grid-modern">
                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-envelope"></i> Email
                                    </div>
                                    <div class="info-value-modern" id="view-email"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-telephone"></i> Phone
                                    </div>
                                    <div class="info-value-modern" id="view-phone"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-gender-ambiguous"></i> Gender
                                    </div>
                                    <div class="info-value-modern" id="view-gender"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-calendar"></i> Date of Birth
                                    </div>
                                    <div class="info-value-modern" id="view-dob"></div>
                                </div>

                                <div class="info-item-modern full-width">
                                    <div class="info-label-modern">
                                        <i class="bi bi-house"></i> Address
                                    </div>
                                    <div class="info-value-modern" id="view-address"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-person-badge"></i> Emergency Contact
                                    </div>
                                    <div class="info-value-modern" id="view-emergency-name"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-telephone-plus"></i> Emergency Number
                                    </div>
                                    <div class="info-value-modern" id="view-emergency-number"></div>
                                </div>
                            </div>
                        </div>

                        <!-- MEMBERSHIPS TAB -->
                        <div class="tab-pane-modern" id="tab-memberships">
                            <div class="table-container-modern">
                                <table class="table-modern">
                                    <thead>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="membership-table">
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- PAYMENTS TAB -->
                        <div class="tab-pane-modern" id="tab-payments">
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
                                    <tbody id="payments-table">
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div> <!-- tab-content-modern -->

                </div><!-- member-details -->
            </div><!-- modal-body-modern -->

        </div>
    </div>
</div>

<!-- VIEW MEMBER MODAL JS -->
<script>
// Open modal function
function viewMember(memberId) {
    const modal = document.getElementById('memberViewModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // RESET UI
    document.getElementById('member-details').style.display = 'none';
    document.getElementById('member-details-loading').style.display = 'block';

    // FETCH MEMBER DATA
    fetch('api/get_members.php?id=' + memberId)
        .then(response => response.json())
        .then(data => {
            const member = data.member;

            // SHOW CONTENT
            document.getElementById('member-details-loading').style.display = 'none';
            document.getElementById('member-details').style.display = 'block';

            // PHOTO
            const photo = member.Photo
                ? 'assets/uploads/members/' + member.Photo
                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(member.FirstName + ' ' + member.LastName) + '&background=4fd1c5&color=fff&size=200';

            document.getElementById('view-photo').src = photo;

            // BASIC PROFILE
            document.getElementById('view-name').textContent =
                member.FirstName + " " + member.LastName;

            document.getElementById('view-email').textContent = member.Email || 'N/A';
            document.getElementById('view-phone').textContent = member.PhoneNo || 'N/A';
            document.getElementById('view-gender').textContent = member.Gender || 'N/A';
            document.getElementById('view-dob').textContent = member.DateOfBirth || 'N/A';

            document.getElementById('view-address').textContent =
                `${member.Address || ''}, ${member.City || ''}, ${member.Province || ''} ${member.Zipcode || ''}`.trim() || 'N/A';

            document.getElementById('view-emergency-name').textContent = member.EmergencyContactName || 'N/A';
            document.getElementById('view-emergency-number').textContent = member.EmergencyContactNumber || 'N/A';

            // STATUS BADGE
            const badge = document.getElementById('view-status-badge');
            badge.textContent = member.MembershipStatus;
            badge.className = "status-badge-modal " + member.MembershipStatus.toLowerCase();

            /* MEMBERSHIP TABLE */
            const membershipTable = document.getElementById('membership-table');
            membershipTable.innerHTML = "";

            if (data.memberships && data.memberships.length > 0) {
                data.memberships.forEach(m => {
                    membershipTable.innerHTML += `
                        <tr>
                            <td>${m.PlanName}</td>
                            <td>${m.StartDate}</td>
                            <td>${m.EndDate}</td>
                            <td>
                                <span class="status-badge ${m.Status.toLowerCase()}">${m.Status}</span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                membershipTable.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:#718096;">No memberships found</td></tr>';
            }

            /* PAYMENTS TABLE */
            const paymentsTable = document.getElementById('payments-table');
            paymentsTable.innerHTML = "";

            if (data.payments && data.payments.length > 0) {
                data.payments.forEach(p => {
                    paymentsTable.innerHTML += `
                        <tr>
                            <td>${p.PaymentDate}</td>
                            <td>₱${parseFloat(p.AmountPaid).toFixed(2)}</td>
                            <td>${p.PaymentMethod}</td>
                            <td>${p.ReferenceNumber || 'N/A'}</td>
                            <td>
                                <span class="status-badge ${p.PaymentStatus.toLowerCase()}">${p.PaymentStatus}</span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                paymentsTable.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#718096;">No payments found</td></tr>';
            }

            // Reset to first tab
            switchTab({preventDefault: () => {}}, 'tab-profile');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('member-details-loading').innerHTML = 
                '<div class="error-state-modern"><i class="bi bi-exclamation-triangle"></i><p>Failed to load member details</p></div>';
        });
}

// Close modal function - ADD THIS!
function closeModal() {
    const modal = document.getElementById('memberViewModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
}

// Close modal on click outside with text selection check
document.addEventListener('mousedown', function(event) {
    const overlay = document.getElementById('memberViewModal');
    if (event.target === overlay) {
        // Check if there's any text selected
        const selection = window.getSelection();
        const hasSelection = selection && selection.toString().length > 0;
        
        // Only close modal if there's no text selected
        if (!hasSelection) {
            closeModal();
        }
    }
});


// Switch tabs function
function switchTab(event, tabId) {
    event.preventDefault();
    
    // Remove active from all tabs
    document.querySelectorAll('.tab-link-modern').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active from all panes
    document.querySelectorAll('.tab-pane-modern').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Add active to clicked tab
    event.target.closest('.tab-link-modern').classList.add('active');
    
    // Show corresponding pane
    document.getElementById(tabId).classList.add('active');
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>