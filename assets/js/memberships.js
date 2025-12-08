// Initialize searchable Member dropdown
function initEditMemberSearch() {
    const searchInput = document.getElementById('edit_member_search');
    const hiddenInput = document.getElementById('edit_member_id');
    const dropdown = document.getElementById('edit_member_dropdown');
    const options = dropdown.querySelectorAll('.searchable-select-option');

    if (!searchInput || !hiddenInput || !dropdown) return;

    // Show dropdown on focus
    searchInput.addEventListener('focus', () => {
        dropdown.classList.add('show');
        filterOptions('');
    });

    // Filter options
    searchInput.addEventListener('input', () => {
        const term = searchInput.value.toLowerCase();
        filterOptions(term);
    });

    function filterOptions(term) {
        let hasVisible = false;
        options.forEach(opt => {
            const text = opt.getAttribute('data-text').toLowerCase();
            if (text.includes(term)) {
                opt.classList.remove('hidden');
                hasVisible = true;
            } else {
                opt.classList.add('hidden');
            }
        });
        dropdown.classList.toggle('show', hasVisible);
    }

    // Click selection
    options.forEach(option => {
        option.addEventListener('click', () => {
            hiddenInput.value = option.dataset.value;
            searchInput.value = option.dataset.text;

            options.forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');

            dropdown.classList.remove('show');
        });
    });

    // Click outside → close dropdown
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
}

// Open edit membership modal
window.editMembership = function (membershipId) {
    const modal = document.getElementById('membershipEditModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Reset UI
    document.getElementById('edit-membership-form').style.display = 'none';
    document.getElementById('edit-membership-loading').style.display = 'block';
    document.getElementById('edit-membership-error').style.display = 'none';

    // Fetch membership data
    fetch('api/get_membership.php?id=' + membershipId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const membership = data.membership;

                // Show form
                document.getElementById('edit-membership-loading').style.display = 'none';
                document.getElementById('edit-membership-form').style.display = 'block';

                // Populate form fields
                document.getElementById('edit_membership_id').value = membership.MembershipID;
                document.getElementById('edit_plan_id').value = membership.PlanID;
                document.getElementById('edit_start_date').value = membership.StartDate;
                document.getElementById('edit_status').value = membership.Status;

                // Set member selection
                document.getElementById('edit_member_id').value = membership.MemberID;
                document.getElementById('edit_member_search').value = membership.MemberName;
                
                // Set plan selection
                document.getElementById('edit_plan_search').value = membership.PlanName + ' - ₱' + parseFloat(membership.PlanRate).toFixed(2);
                
                // Set status selection
                document.getElementById('edit_status_search').value = membership.Status;

                // Mark selected options
                document.querySelectorAll('#edit_member_dropdown .searchable-select-option').forEach(opt => {
                    opt.classList.remove('selected');
                    if (opt.getAttribute('data-value') == membership.MemberID) {
                        opt.classList.add('selected');
                    }
                });
                
                document.querySelectorAll('#edit_plan_dropdown .searchable-select-option').forEach(opt => {
                    opt.classList.remove('selected');
                    if (opt.getAttribute('data-value') == membership.PlanID) {
                        opt.classList.add('selected');
                    }
                });
                
                document.querySelectorAll('#edit_status_dropdown .searchable-select-option').forEach(opt => {
                    opt.classList.remove('selected');
                    if (opt.getAttribute('data-value') == membership.Status) {
                        opt.classList.add('selected');
                    }
                });

                // Initialize searchable selects with CORRECT parameter format
                initSearchableSelect({
                    searchInputId: 'edit_member_search',
                    hiddenInputId: 'edit_member_id',
                    dropdownId: 'edit_member_dropdown'
                });
                
                initSearchableSelect({
                    searchInputId: 'edit_plan_search',
                    hiddenInputId: 'edit_plan_id',
                    dropdownId: 'edit_plan_dropdown'
                });
                
                initSearchableSelect({
                    searchInputId: 'edit_status_search',
                    hiddenInputId: 'edit_status',
                    dropdownId: 'edit_status_dropdown'
                });

            } else {
                document.getElementById('edit-membership-loading').style.display = 'none';
                document.getElementById('edit-membership-error').style.display = 'block';
                toast.error(data.message || 'Failed to load membership details', 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('edit-membership-loading').style.display = 'none';
            document.getElementById('edit-membership-error').style.display = 'block';
            toast.error('Failed to load membership details. Please try again.', 5000);
        });
}


// Close edit membership modal
window.closeMembershipEditModal = function closeMembershipEditModal() {
    const modal = document.getElementById('membershipEditModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.getElementById('editMembershipForm').reset();
    document.getElementById('edit_member_search').value = '';
    document.getElementById('edit_member_dropdown').classList.remove('show');
}

// Close modal when clicking outside (but not when selecting text)
let membershipEditMouseDownTarget = null;

document.addEventListener('mousedown', function(event) {
    membershipEditMouseDownTarget = event.target;
});

document.addEventListener('mouseup', function(event) {
    const modal = document.getElementById('membershipEditModal');
    
    if (membershipEditMouseDownTarget === modal && 
        event.target === modal && 
        window.getSelection().toString().length === 0) {
        closeMembershipEditModal();
    }
    
    membershipEditMouseDownTarget = null;
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('membershipEditModal');
        if (modal.classList.contains('show')) {
            closeMembershipEditModal();
        }
    }
});

// Form submission
const editForm = document.getElementById('editMembershipForm');

if (editForm) {
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const saveBtn = document.getElementById('saveMembershipEditBtn');

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';

        fetch('memberships.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                toast.success('Membership updated successfully! Refreshing...', 3000);
                setTimeout(() => {
                    window.location.href = response.url;
                }, 1000);
            } else {
                throw new Error('Update failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toast.error('Failed to update membership. Please try again.', 5000);
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Update Membership';
        });
    });
}

function initSearchableSelect(searchInputId, hiddenInputId, dropdownId) {
    const searchInput = document.getElementById(searchInputId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.searchable-select-option');

    // Show dropdown on focus
    searchInput.addEventListener('focus', () => {
        dropdown.classList.add('show');
        filterOptions('');
    });

    // Filter options
    searchInput.addEventListener('input', function () {
        filterOptions(this.value.toLowerCase());
    });

    // Clicking an option
    options.forEach(option => {
        option.addEventListener('click', function () {
            searchInput.value = this.getAttribute('data-text');
            hiddenInput.value = this.getAttribute('data-value');

            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            dropdown.classList.remove('show');
        });
    });

    // Filter function
    function filterOptions(term) {
        let visible = false;

        options.forEach(option => {
            const text = option.getAttribute('data-text').toLowerCase();
            const match = text.includes(term);

            option.classList.toggle('hidden', !match);
            if (match) visible = true;
        });

        dropdown.classList.toggle('show', visible);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
}
