<!-- ADD MEMBERSHIP MODAL -->
<div class="modal-overlay" id="membershipAddModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Create New Membership</h5>
                <button type="button" class="modal-close-modern" onclick="closeMembershipAddModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="addMembershipForm" method="POST" action="memberships.php">
                <div class="modal-body-modern">

                    <!-- FORM CONTENT -->
                    <div id="add-membership-form">

                        <!-- Member Selection Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-person-circle"></i> Member Information
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern full-width">
                                    <label for="add_membership_member_search" class="form-label-modern">
                                        <i class="bi bi-person"></i> Select Member
                                        <span class="required">*</span>
                                    </label>
                                    <div class="searchable-select-wrapper">
                                        <input 
                                            type="text" 
                                            id="add_membership_member_search" 
                                            class="form-control-modern searchable-select-input" 
                                            placeholder="Search member..."
                                            autocomplete="off"
                                        >
                                        <input type="hidden" name="MemberID" id="add_membership_member_id" required>
                                        <div class="searchable-select-dropdown" id="add_membership_member_dropdown">
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
                            </div>
                        </div>

                        <!-- Membership Details Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-card-checklist"></i> Membership Details
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="add_membership_plan_id" class="form-label-modern">
                                        <i class="bi bi-tag"></i> Plan
                                        <span class="required">*</span>
                                    </label>
                                    <select name="PlanID" id="add_membership_plan_id" class="form-control-modern" required>
                                        <option value="">Select Plan...</option>
                                        <?php foreach ($plans as $plan): ?>
                                            <option value="<?= $plan['PlanID'] ?>">
                                                <?= htmlspecialchars($plan['PlanName'] . ' - ₱' . number_format($plan['Rate'], 2)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_membership_start_date" class="form-label-modern">
                                        <i class="bi bi-calendar-check"></i> Start Date
                                        <span class="required">*</span>
                                    </label>
                                    <input type="date" class="form-control-modern" id="add_membership_start_date" 
                                           name="StartDate" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_membership_status" class="form-label-modern">
                                        <i class="bi bi-check-circle"></i> Status
                                    </label>
                                    <select class="form-control-modern" id="add_membership_status" name="Status">
                                        <option value="Active" selected>Active</option>
                                        <option value="Expired">Expired</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
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

                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="closeMembershipAddModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="saveMembershipAddBtn">
                        <i class="bi bi-check-lg"></i> Create Membership
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- SEARCHABLE SELECT STYLES (if not already included) -->
<style>
.searchable-select-wrapper {
    position: relative;
}

.searchable-select-input {
    cursor: text;
}

.searchable-select-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 250px;
    overflow-y: auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    z-index: 1000;
    display: none;
    margin-top: 4px;
}

.searchable-select-dropdown.show {
    display: block;
}

.searchable-select-option {
    padding: 10px 14px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    font-size: 14px;
    color: #2d3748;
}

.searchable-select-option:hover {
    background-color: #f7fafc;
}

.searchable-select-option.selected {
    background-color: #ebf8ff;
    color: #2c5282;
    font-weight: 500;
}

.searchable-select-option.hidden {
    display: none;
}

.searchable-select-dropdown::-webkit-scrollbar {
    width: 8px;
}

.searchable-select-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.searchable-select-dropdown::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
}

.searchable-select-dropdown::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>

<!-- ADD MEMBERSHIP MODAL JS -->
<script>
// Initialize searchable select for member field in add modal
function initAddMembershipMemberSearch() {
    const searchInput = document.getElementById('add_membership_member_search');
    const hiddenInput = document.getElementById('add_membership_member_id');
    const dropdown = document.getElementById('add_membership_member_dropdown');
    const options = dropdown.querySelectorAll('.searchable-select-option');

    // Show dropdown on focus
    searchInput.addEventListener('focus', function() {
        dropdown.classList.add('show');
        filterAddMembershipOptions('');
    });

    // Filter options on input
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterAddMembershipOptions(searchTerm);
    });

    // Handle option selection
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.getAttribute('data-text');
            
            hiddenInput.value = value;
            searchInput.value = text;
            dropdown.classList.remove('show');
            
            // Update selected state
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Filter options function
    function filterAddMembershipOptions(searchTerm) {
        let hasVisibleOptions = false;
        
        options.forEach(option => {
            const text = option.getAttribute('data-text').toLowerCase();
            const matches = text.includes(searchTerm);
            
            if (matches) {
                option.classList.remove('hidden');
                hasVisibleOptions = true;
            } else {
                option.classList.add('hidden');
            }
        });

        // Show/hide dropdown based on visible options
        if (hasVisibleOptions) {
            dropdown.classList.add('show');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const visibleOptions = Array.from(options).filter(opt => !opt.classList.contains('hidden'));
        const currentIndex = visibleOptions.findIndex(opt => opt.classList.contains('selected'));

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIndex = currentIndex < visibleOptions.length - 1 ? currentIndex + 1 : 0;
            if (visibleOptions[nextIndex]) {
                options.forEach(opt => opt.classList.remove('selected'));
                visibleOptions[nextIndex].classList.add('selected');
                visibleOptions[nextIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIndex = currentIndex > 0 ? currentIndex - 1 : visibleOptions.length - 1;
            if (visibleOptions[prevIndex]) {
                options.forEach(opt => opt.classList.remove('selected'));
                visibleOptions[prevIndex].classList.add('selected');
                visibleOptions[prevIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const selectedOption = visibleOptions.find(opt => opt.classList.contains('selected'));
            if (selectedOption) {
                selectedOption.click();
            } else if (visibleOptions.length > 0) {
                visibleOptions[0].click();
            }
        } else if (e.key === 'Escape') {
            dropdown.classList.remove('show');
        }
    });
}

// Open add membership modal
function openAddMembershipModal() {
    const modal = document.getElementById('membershipAddModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');
    
    // Reset form
    document.getElementById('addMembershipForm').reset();
    document.getElementById('add_membership_member_search').value = '';
    document.getElementById('add_membership_member_id').value = '';
    document.getElementById('add_membership_start_date').value = new Date().toISOString().split('T')[0];
    
    // Clear selected state from all options
    document.querySelectorAll('#add_membership_member_dropdown .searchable-select-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    // Initialize searchable select
    initAddMembershipMemberSearch();
}

// Close add membership modal
function closeMembershipAddModal() {
    const modal = document.getElementById('membershipAddModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.getElementById('addMembershipForm').reset();
    document.getElementById('add_membership_member_dropdown').classList.remove('show');
}

// Close modal when clicking outside (but not when selecting text)
let membershipAddMouseDownTarget = null;

document.addEventListener('mousedown', function(event) {
    membershipAddMouseDownTarget = event.target;
});

document.addEventListener('mouseup', function(event) {
    const modal = document.getElementById('membershipAddModal');
    
    if (membershipAddMouseDownTarget === modal && 
        event.target === modal && 
        window.getSelection().toString().length === 0) {
        closeMembershipAddModal();
    }
    
    membershipAddMouseDownTarget = null;
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('membershipAddModal');
        if (modal.classList.contains('show')) {
            closeMembershipAddModal();
        }
    }
});

// Form submission
document.getElementById('addMembershipForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const saveBtn = document.getElementById('saveMembershipAddBtn');
    
    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
    
    fetch('memberships.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response redirected (successful form submission)
        if (response.redirected) {
            toast.success('Membership created successfully! Refreshing...', 3000);
            setTimeout(() => {
                window.location.href = response.url;
            }, 1000);
        } else {
            return response.text().then(text => {
                throw new Error('Creation failed');
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toast.error('Failed to create membership. Please try again.', 5000);
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Create Membership';
    });
});
</script>