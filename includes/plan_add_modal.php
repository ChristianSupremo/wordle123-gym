<?php
/**
 * Plan Add Modal
 * Path: includes/plan_add_modal.php
 */
?>

<!-- ADD PLAN MODAL -->
<div class="modal-overlay" id="planAddModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Add New Plan</h5>
                <button type="button" class="modal-close-modern" onclick="PlanAddModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="addPlanForm" method="POST" action="plans.php">
                <div class="modal-body-modern">

                    <div id="add-plan-form">

                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-info-circle"></i> Basic Information
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern full-width">
                                    <label for="add_PlanName" class="form-label-modern">
                                        <i class="bi bi-tag"></i> Plan Name
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" name="PlanName" id="add_PlanName"
                                           placeholder="e.g., Premium Monthly, Basic Annual" required>
                                </div>

                                <div class="form-group-modern full-width">
                                    <label for="add_Description" class="form-label-modern">
                                        <i class="bi bi-file-text"></i> Description
                                        <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control-modern" name="Description" id="add_Description" 
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
                                    <label for="add_Duration" class="form-label-modern">
                                        <i class="bi bi-calendar3"></i> Duration
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control-modern" name="Duration" id="add_Duration"
                                           min="1" placeholder="1" value="30" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_PlanType" class="form-label-modern">
                                        <i class="bi bi-hourglass-split"></i> Duration Type
                                        <span class="required">*</span>
                                    </label>
                                    <select name="PlanType" id="add_PlanType" class="form-control-modern" required>
                                        <option value="Days" selected>Days</option>
                                        <option value="Months">Months</option>
                                        <option value="Years">Years</option>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_Rate" class="form-label-modern">
                                        <i class="bi bi-cash"></i> Rate (₱)
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" step="0.01" class="form-control-modern" name="Rate" id="add_Rate"
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
                                        <input type="checkbox" name="IsActive" id="add_IsActive" checked>
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
                    <button type="button" class="btn-secondary-modern" onclick="PlanAddModal.close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="savePlanAddBtn">
                        <i class="bi bi-check-lg"></i> Create Plan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Plan Add Modal Controller
const PlanAddModal = new ModalManager('planAddModal');

// Open add plan modal
function openAddPlanModal() {
    PlanAddModal.open();
    
    // Reset form
    document.getElementById('addPlanForm').reset();
    
    // Set defaults
    document.getElementById('add_Duration').value = '30';
    document.getElementById('add_PlanType').value = 'Days';
    document.getElementById('add_IsActive').checked = true;
}

// Initialize form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addPlanForm');
    const button = document.getElementById('savePlanAddBtn');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const originalButtonContent = button.innerHTML;
        
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
        
        try {
            const response = await fetch('plans.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.redirected) {
                toast.success('Plan created successfully! Refreshing...', 3000);
                setTimeout(() => {
                    window.location.href = response.url;
                }, 1000);
            } else {
                throw new Error('Creation failed');
            }
        } catch (error) {
            console.error('Error:', error);
            toast.error('Failed to create plan. Please try again.', 5000);
            button.disabled = false;
            button.innerHTML = originalButtonContent;
        }
    });
});
</script>