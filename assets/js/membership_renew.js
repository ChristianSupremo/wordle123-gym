// ------------------------------
// OPEN RENEW MEMBERSHIP MODAL
// ------------------------------
function renewMembership(membershipId) {
    const modal = document.getElementById('membershipRenewModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Reset UI
    document.getElementById('renew-membership-form').style.display = 'none';
    document.getElementById('renew-membership-loading').style.display = 'block';
    document.getElementById('renew-membership-error').style.display = 'none';

    fetch('api/get_membership.php?id=' + membershipId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const membership = data.membership;

                document.getElementById('renew-membership-loading').style.display = 'none';
                document.getElementById('renew-membership-form').style.display = 'block';

                // Populate read-only fields
                document.getElementById('renew_member_id').value = membership.MemberID;
                document.getElementById('renew_member_name').textContent = membership.MemberName;
                document.getElementById('renew_current_plan').textContent = membership.PlanName;
                document.getElementById('renew_current_end_date').textContent =
                    new Date(membership.EndDate).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

                // FIX for NaN issue
                const rate = parseFloat(membership.PlanRate || 0);
                document.getElementById('renew_plan_rate').textContent = "₱" + rate.toFixed(2);

                // Set editable fields
                document.getElementById('renew_plan_id').value = membership.PlanID;

                const endDate = new Date(membership.EndDate);
                endDate.setDate(endDate.getDate() + 1);
                document.getElementById('renew_start_date').value = endDate.toISOString().split('T')[0];

                // Reset fields
                document.getElementById('renew_payment_method').value = '';
                document.getElementById('renew_reference_number').value = '';
                document.getElementById('renew_notes').value = '';
                document.getElementById('renew_reference_group').style.display = 'none';

                calculateRenewEndDate();
            } else {
                document.getElementById('renew-membership-loading').style.display = 'none';
                document.getElementById('renew-membership-error').style.display = 'block';
                toast.error(data.message || 'Failed to load membership details', 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('renew-membership-loading').style.display = 'none';
            document.getElementById('renew-membership-error').style.display = 'block';
        });
}



// ------------------------------
// CALCULATE END DATE
// ------------------------------
function calculateRenewEndDate() {
    const planSelect = document.getElementById('renew_plan_id');
    const startDateInput = document.getElementById('renew_start_date');
    const computedEndDateEl = document.getElementById('renew_computed_end_date');

    if (!planSelect.value || !startDateInput.value) {
        computedEndDateEl.textContent = 'Select plan and start date';
        return;
    }

    fetch('api/get_plan_duration.php?id=' + planSelect.value)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let duration = parseInt(data.duration);
                let planType = data.plan_type.toLowerCase();
                let startDate = new Date(startDateInput.value);
                let endDate = new Date(startDate);

                if (planType === 'days') endDate.setDate(endDate.getDate() + duration);
                if (planType === 'months') endDate.setMonth(endDate.getMonth() + duration);
                if (planType === 'years') endDate.setFullYear(endDate.getFullYear() + duration);

                computedEndDateEl.textContent = endDate.toLocaleDateString('en-US', {
                    year: 'numeric', month: 'short', day: 'numeric'
                });
                computedEndDateEl.style.color = '#22c55e';
            }
        })
        .catch(err => {
            console.error('Error calculating end date:', err);
            computedEndDateEl.textContent = 'Unable to calculate';
        });
}



// ------------------------------
// DOMContentLoaded (EVENT LISTENERS)
// ------------------------------
document.addEventListener("DOMContentLoaded", function () {

    // Payment method logic
    document.getElementById('renew_payment_method').addEventListener('change', function () {
        const group = document.getElementById('renew_reference_group');
        const input = document.getElementById('renew_reference_number');

        if (this.value === 'GCash') {
            group.style.display = 'block';
            input.required = true;
        } else {
            group.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    });

    // Change listeners
    document.getElementById('renew_plan_id').addEventListener('change', calculateRenewEndDate);
    document.getElementById('renew_start_date').addEventListener('change', calculateRenewEndDate);

    // Form submit
    document.getElementById('renewMembershipForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const saveBtn = document.getElementById('saveMembershipRenewBtn');
        const formData = new FormData(this);

        formData.append('MemberID', document.getElementById('renew_member_id').value);

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Renewing...';

        fetch('memberships.php', { method: 'POST', body: formData })
            .then(res => {
                if (res.redirected) {
                    toast.success('Membership renewed successfully!', 3000);
                    setTimeout(() => window.location.href = res.url, 800);
                } else {
                    throw new Error();
                }
            })
            .catch(err => {
                toast.error('Failed to renew membership.', 5000);
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Renew Membership';
            });
    });

}); // END DOMContentLoaded



// ------------------------------
// MODAL CLOSE LOGIC (GLOBAL)
// ------------------------------
function closeMembershipRenewModal() {
    const modal = document.getElementById('membershipRenewModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.getElementById('renewMembershipForm').reset();
}

let membershipRenewMouseDownTarget = null;

document.addEventListener('mousedown', e => {
    membershipRenewMouseDownTarget = e.target;
});

document.addEventListener('mouseup', e => {
    const modal = document.getElementById('membershipRenewModal');
    if (membershipRenewMouseDownTarget === modal &&
        e.target === modal &&
        window.getSelection().toString().length === 0) {
        closeMembershipRenewModal();
    }
    membershipRenewMouseDownTarget = null;
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('membershipRenewModal');
        if (modal.classList.contains('show')) {
            closeMembershipRenewModal();
        }
    }
});
