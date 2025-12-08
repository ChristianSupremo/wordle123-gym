<?php
/**
 * Plan Edit Modal
 * Path: includes/plan_edit_modal.php
 */
?>

<!-- EDIT PLAN MODAL -->
<div class="modal-overlay" id="planEditModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Edit Plan</h5>
                <button type="button" class="modal-close-modern" onclick="PlanEditModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="editPlanForm" method="POST" action="plans.php">
                <div class="modal-body-modern">

                    <!-- Loading State -->
                    <div id="plan-edit-loading" class="loading-state-modern">
                        <div class="spinner-modern"></div>
                        <p>Loading plan details...</p>
                    </div>

                    <!-- Error State -->
                    <div id="plan-edit-error" class="error-state-modern" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load plan details</p>
                    </div>

                    <!-- Edit Form -->
                    <div id="plan-edit-form" style="display: none;">
                        <input type="hidden" name="plan_id" id="edit_plan_id">

                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-info-circle"></i> Basic Information
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern full-width">
                                    <label for="edit_PlanName" class="form-label-modern">
                                        <i class="bi bi-tag"></i> Plan Name
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" name="PlanName" id="edit_PlanName"
                                           placeholder="e.g., Premium Monthly, Basic Annual" required>
                                </div>

                                <div class="form-group-modern full-width">
                                    <label for="edit_Description" class="form-label-modern">
                                        <i class="bi bi-file-text"></i> Description
                                        <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control-modern" name="Description" id="edit_Description" 
                                              rows="3" placeholder="Describe what this plan offers..." required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Duration Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-currency-dollar"></i> Pricing & Duration
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="edit_Duration" class="form-label-modern">
                                        <i class="bi bi-calendar3"></i> Duration
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control-modern" name="Duration" id="edit_Duration"
                                           min="1" placeholder="1" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_PlanType" class="form-label-modern">
                                        <i class="bi bi-hourglass-split"></i> Duration Type
                                        <span class="required">*</span>
                                    </label>
                                    <select name="PlanType" id="edit_PlanType" class="form-control-modern" required>
                                        <option value="Days">Days</option>
                                        <option value="Months">Months</option>
                                        <option value="Years">Years</option>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_Rate" class="form-label-modern">
                                        <i class="bi bi-cash"></i> Rate (₱)
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" step="0.01" class="form-control-modern" name="Rate" id="edit_Rate"
                                           min="0" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>

                        <!-- Status Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-toggle-on"></i> Status
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern full-width">
                                    <label class="checkbox-container-modern">
                                        <input type="checkbox" name="IsActive" id="edit_IsActive">
                                        <span class="checkbox-label-modern">
                                            <span><i class="bi bi-check-circle"></i> Plan is Active</span>
                                            <small class="form-help-text">Active plans are available for member enrollment</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="PlanEditModal.close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="savePlanEditBtn">
                        <i class="bi bi-check-lg"></i> Update Plan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Plan Edit Modal Controller
const PlanEditModal = new ModalManager('planEditModal');

// Store original plan data for change tracking
let originalPlanData = {};

// Open edit plan modal
async function editPlan(planId) {
    PlanEditModal.open();
    
    // Show loading state
    document.getElementById('plan-edit-loading').style.display = 'block';
    document.getElementById('plan-edit-error').style.display = 'none';
    document.getElementById('plan-edit-form').style.display = 'none';
    
    try {
        const response = await fetch(`api/get_plan_details.php?id=${planId}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load plan details');
        }
        
        // Store original data
        originalPlanData = { ...data.plan };
        
        // Populate form
        populatePlanEditForm(data.plan);
        
        // Hide loading, show form
        document.getElementById('plan-edit-loading').style.display = 'none';
        document.getElementById('plan-edit-form').style.display = 'block';
        
    } catch (error) {
        console.error('Error loading plan details:', error);
        document.getElementById('plan-edit-loading').style.display = 'none';
        document.getElementById('plan-edit-error').style.display = 'block';
    }
}

// Populate edit form with plan data
function populatePlanEditForm(plan) {
    document.getElementById('edit_plan_id').value = plan.PlanID;
    document.getElementById('edit_PlanName').value = plan.PlanName;
    document.getElementById('edit_Description').value = plan.Description;
    document.getElementById('edit_Duration').value = plan.Duration;
    document.getElementById('edit_PlanType').value = plan.PlanType;
    document.getElementById('edit_Rate').value = parseFloat(plan.Rate).toFixed(2);
    document.getElementById('edit_IsActive').checked = plan.IsActive == 1;
}

// Get changes made by user
function getPlanChanges() {
    const changes = [];
    
    const currentData = {
        PlanName: document.getElementById('edit_PlanName').value,
        Description: document.getElementById('edit_Description').value,
        Duration: parseInt(document.getElementById('edit_Duration').value),
        PlanType: document.getElementById('edit_PlanType').value,
        Rate: parseFloat(document.getElementById('edit_Rate').value),
        IsActive: document.getElementById('edit_IsActive').checked ? 1 : 0
    };
    
    if (currentData.PlanName !== originalPlanData.PlanName) {
        changes.push(`<strong>Plan Name:</strong> "${originalPlanData.PlanName}" → "${currentData.PlanName}"`);
    }
    
    if (currentData.Description !== originalPlanData.Description) {
        changes.push(`<strong>Description:</strong> Changed`);
    }
    
    if (currentData.Duration !== parseInt(originalPlanData.Duration)) {
        changes.push(`<strong>Duration:</strong> ${originalPlanData.Duration} → ${currentData.Duration}`);
    }
    
    if (currentData.PlanType !== originalPlanData.PlanType) {
        changes.push(`<strong>Duration Type:</strong> ${originalPlanData.PlanType} → ${currentData.PlanType}`);
    }
    
    if (Math.abs(currentData.Rate - parseFloat(originalPlanData.Rate)) > 0.001) {
        changes.push(`<strong>Rate:</strong> ₱${parseFloat(originalPlanData.Rate).toFixed(2)} → ₱${currentData.Rate.toFixed(2)}`);
    }
    
    if (currentData.IsActive !== parseInt(originalPlanData.IsActive)) {
        const oldStatus = originalPlanData.IsActive ? 'Active' : 'Inactive';
        const newStatus = currentData.IsActive ? 'Active' : 'Inactive';
        changes.push(`<strong>Status:</strong> ${oldStatus} → ${newStatus}`);
    }
    
    return changes;
}

// Initialize form submission with change confirmation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editPlanForm');
    const button = document.getElementById('savePlanEditBtn');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Get changes
        const changes = getPlanChanges();
        
        if (changes.length === 0) {
            toast.info('No changes were made to the plan.');
            return;
        }
        
        // Show confirmation dialog with changes
        const changesList = changes.map(change => `• ${change}`).join('<br>');
        const confirmed = await confirm.show({
            title: 'Confirm Plan Update',
            message: `You are about to make the following changes:<br><br>${changesList}<br><br>Do you want to proceed?`,
            confirmText: 'Update Plan',
            cancelText: 'Cancel',
            type: 'info'
        });
        
        if (!confirmed) {
            return;
        }
        
        // Proceed with form submission
        const formData = new FormData(form);
        const originalButtonContent = button.innerHTML;
        
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
        
        try {
            const response = await fetch('plans.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.redirected) {
                toast.success('Plan updated successfully! Refreshing...', 3000);
                setTimeout(() => {
                    window.location.href = response.url;
                }, 1000);
            } else {
                throw new Error('Update failed');
            }
        } catch (error) {
            console.error('Error:', error);
            toast.error('Failed to update plan. Please try again.', 5000);
            button.disabled = false;
            button.innerHTML = originalButtonContent;
        }
    });
});
</script>