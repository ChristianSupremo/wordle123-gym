// Open membership view modal
function viewMembershipDetails(membershipId) {
    const modal = document.getElementById('membershipViewModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Reset UI
    document.getElementById('membership-details').style.display = 'none';
    document.getElementById('membership-details-loading').style.display = 'block';
    document.getElementById('membership-details-error').style.display = 'none';

    // Fetch membership data
    fetch('api/get_membership_details.php?id=' + membershipId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const membership = data.membership;
                const member = data.member;
                const payments = data.payments || [];

                // Show content
                document.getElementById('membership-details-loading').style.display = 'none';
                document.getElementById('membership-details').style.display = 'block';
                document.getElementById('membership-details-error').style.display = 'none';

                // PHOTO
                const photo = member.Photo
                    ? 'assets/uploads/members/' + member.Photo
                    : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(member.FirstName + ' ' + member.LastName) + '&background=4fd1c5&color=fff&size=200';

                document.getElementById('view-membership-photo').src = photo;

                // MEMBER NAME
                document.getElementById('view-membership-member-name').textContent =
                    member.FirstName + " " + member.LastName;

                // STATUS BADGE (TOP)
                const topBadge = document.getElementById('view-membership-status-badge');
                topBadge.textContent = membership.Status;
                topBadge.className = "status-badge-modal " + membership.Status.toLowerCase();

                // MEMBERSHIP INFO TAB
                document.getElementById('view-membership-plan-name').textContent = membership.PlanName || 'N/A';
                document.getElementById('view-membership-plan-rate').textContent = 
                    membership.PlanRate ? '₱' + parseFloat(membership.PlanRate).toFixed(2) : 'N/A';
                document.getElementById('view-membership-start-date').textContent = 
                    membership.StartDate ? new Date(membership.StartDate).toLocaleDateString() : 'N/A';
                document.getElementById('view-membership-end-date').textContent = 
                    membership.EndDate ? new Date(membership.EndDate).toLocaleDateString() : 'N/A';
                document.getElementById('view-membership-duration').textContent = membership.Duration || 'N/A';
                
                // Days Left calculation
                const daysLeftEl = document.getElementById('view-membership-days-left');
                if (membership.Status === 'Cancelled') {
                    daysLeftEl.innerHTML = '<span style="color: #718096;">Cancelled</span>';
                } else if (membership.Status === 'Expired') {
                    const daysExpired = Math.abs(membership.DaysLeft);
                    daysLeftEl.innerHTML = '<span style="color: #e53e3e;">Expired (' + daysExpired + ' day' + (daysExpired != 1 ? 's' : '') + ' ago)</span>';
                } else if (membership.DaysLeft < 0) {
                    daysLeftEl.innerHTML = '<span style="color: #e53e3e;">Expired</span>';
                } else if (membership.DaysLeft == 0) {
                    daysLeftEl.innerHTML = '<span style="color: #dd6b20;">Expires today</span>';
                } else if (membership.DaysLeft <= 7) {
                    daysLeftEl.innerHTML = '<span style="color: #dd6b20;">' + membership.DaysLeft + ' day' + (membership.DaysLeft != 1 ? 's' : '') + ' left</span>';
                } else {
                    daysLeftEl.innerHTML = '<span style="color: #38a169;">' + membership.DaysLeft + ' day' + (membership.DaysLeft != 1 ? 's' : '') + ' left</span>';
                }

                document.getElementById('view-membership-staff').textContent = membership.StaffName || 'N/A';

                // STATUS BADGE (IN TAB)
                const statusBadge = document.getElementById('view-membership-status');
                statusBadge.textContent = membership.Status;
                statusBadge.className = 'status-badge ' + membership.Status.toLowerCase();

                // MEMBER PROFILE TAB
                document.getElementById('view-membership-email').textContent = member.Email || 'N/A';
                document.getElementById('view-membership-phone').textContent = member.PhoneNo || 'N/A';
                document.getElementById('view-membership-gender').textContent = member.Gender || 'N/A';
                document.getElementById('view-membership-dob').textContent = member.DateOfBirth || 'N/A';
                
                document.getElementById('view-membership-address').textContent =
                    `${member.Address || ''}, ${member.City || ''}, ${member.Province || ''} ${member.Zipcode || ''}`.trim() || 'N/A';
                
                document.getElementById('view-membership-emergency-name').textContent = member.EmergencyContactName || 'N/A';
                document.getElementById('view-membership-emergency-number').textContent = member.EmergencyContactNumber || 'N/A';

                // PAYMENT HISTORY TAB
                const paymentsTable = document.getElementById('membership-payments-table');
                paymentsTable.innerHTML = "";

                if (payments.length > 0) {
                    payments.forEach(p => {
                        paymentsTable.innerHTML += `
                            <tr>
                                <td>${new Date(p.PaymentDate).toLocaleDateString()}</td>
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
                    paymentsTable.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#718096;">No payment history found</td></tr>';
                }

                // Reset to first tab
                switchMembershipTab({preventDefault: () => {}}, 'tab-membership-info');

            } else {
                document.getElementById('membership-details-loading').style.display = 'none';
                document.getElementById('membership-details-error').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('membership-details-loading').style.display = 'none';
            document.getElementById('membership-details-error').style.display = 'block';
        });
}

// Close membership view modal
function closeMembershipViewModal() {
    const modal = document.getElementById('membershipViewModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
}

// Close modal when clicking outside (but not when selecting text)
let membershipViewMouseDownTarget = null;

document.addEventListener('mousedown', function(event) {
    membershipViewMouseDownTarget = event.target;
});

document.addEventListener('mouseup', function(event) {
    const modal = document.getElementById('membershipViewModal');
    
    if (membershipViewMouseDownTarget === modal && 
        event.target === modal && 
        window.getSelection().toString().length === 0) {
        closeMembershipViewModal();
    }
    
    membershipViewMouseDownTarget = null;
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('membershipViewModal');
        if (modal.classList.contains('show')) {
            closeMembershipViewModal();
        }
    }
});

// Switch tabs function
function switchMembershipTab(event, tabId) {
    event.preventDefault();
    
    // Remove active from all tabs
    document.querySelectorAll('#membershipViewModal .tab-link-modern').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active from all panes
    document.querySelectorAll('#membershipViewModal .tab-pane-modern').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Add active to clicked tab
    const tabLink = event.target?.closest('.tab-link-modern');
    if (tabLink) tabLink.classList.add('active');
    
    // Show corresponding pane
    document.getElementById(tabId).classList.add('active');
}