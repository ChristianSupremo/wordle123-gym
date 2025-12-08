<?php
/**
 * Plan View Modal
 * Path: includes/plan_view_modal.php
 */
?>

<!-- VIEW PLAN MODAL -->
<div class="modal-overlay" id="planViewModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Plan Details</h5>
                <button type="button" class="modal-close-modern" onclick="PlanViewModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="modal-body-modern">

                <!-- Loading State -->
                <div id="plan-view-loading" class="loading-state-modern">
                    <div class="spinner-modern"></div>
                    <p>Loading plan details...</p>
                </div>

                <!-- Error State -->
                <div id="plan-view-error" class="error-state-modern" style="display: none;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <p>Failed to load plan details</p>
                </div>

                <!-- Plan Details Content -->
                <div id="plan-view-content" style="display: none;">

                    <!-- Plan Header -->
                    <div class="member-header-modern">
                        <div class="member-header-info-modern" style="width: 100%;">
                            <h2 class="member-name-modern" id="view_plan_name"></h2>
                            <div style="display: flex; gap: 12px; align-items: center; margin-top: 8px;">
                                <span class="status-badge" id="view_plan_status"></span>
                                <span id="view_plan_type_display" style="color: #64748b; font-size: 14px;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="plan-stat-grid">
                        <div class="plan-stat-item">
                            <div class="plan-stat-label">Active Members</div>
                            <div class="plan-stat-value" id="view_active_members">0</div>
                        </div>
                        <div class="plan-stat-item">
                            <div class="plan-stat-label">Total Revenue</div>
                            <div class="plan-stat-value" id="view_total_revenue">₱0.00</div>
                        </div>
                        <div class="plan-stat-item">
                            <div class="plan-stat-label">Total Members</div>
                            <div class="plan-stat-value" id="view_total_members">0</div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="tabs-modern">
                        <li class="tab-item-modern">
                            <a class="tab-link-modern active" data-tab="details">
                                <i class="bi bi-info-circle"></i> Details
                            </a>
                        </li>
                        <li class="tab-item-modern">
                            <a class="tab-link-modern" data-tab="pricing">
                                <i class="bi bi-currency-dollar"></i> Pricing
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content-modern">

                        <!-- Details Tab -->
                        <div class="tab-pane-modern active" id="details-tab">
                            <div class="info-grid-modern">
                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-tag"></i> Plan Name
                                    </div>
                                    <div class="info-value-modern" id="view_plan_name_detail"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-calendar3"></i> Duration
                                    </div>
                                    <div class="info-value-modern" id="view_plan_duration"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-hourglass-split"></i> Plan Type
                                    </div>
                                    <div class="info-value-modern" id="view_plan_type"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-toggle-on"></i> Status
                                    </div>
                                    <div class="info-value-modern" id="view_plan_status_text"></div>
                                </div>

                                <div class="info-item-modern full-width">
                                    <div class="info-label-modern">
                                        <i class="bi bi-file-text"></i> Description
                                    </div>
                                    <div class="info-value-modern" id="view_plan_description"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Tab -->
                        <div class="tab-pane-modern" id="pricing-tab">
                            <div class="info-grid-modern">
                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-cash"></i> Rate
                                    </div>
                                    <div class="info-value-modern" id="view_plan_rate" style="color: #059669; font-size: 24px; font-weight: 700;"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-calculator"></i> Rate per Day
                                    </div>
                                    <div class="info-value-modern" id="view_plan_rate_per_day"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer-modern">
                <button type="button" class="btn-secondary-modern" onclick="PlanViewModal.close()">
                    Close
                </button>
                <button type="button" class="btn-primary-modern" onclick="editPlanFromView()">
                    <i class="bi bi-pencil"></i> Edit Plan
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Plan View Modal Controller
const PlanViewModal = new ModalManager('planViewModal');

// Current plan ID being viewed
let currentViewPlanId = null;

// Open view plan modal
async function viewPlan(planId) {
    currentViewPlanId = planId;
    PlanViewModal.open();
    
    // Show loading state
    document.getElementById('plan-view-loading').style.display = 'block';
    document.getElementById('plan-view-error').style.display = 'none';
    document.getElementById('plan-view-content').style.display = 'none';
    
    try {
        const response = await fetch(`api/get_plan_details.php?id=${planId}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load plan details');
        }
        
        // Populate plan details
        populatePlanView(data.plan, data.stats);
        
        // Hide loading, show content
        document.getElementById('plan-view-loading').style.display = 'none';
        document.getElementById('plan-view-content').style.display = 'block';
        
    } catch (error) {
        console.error('Error loading plan details:', error);
        document.getElementById('plan-view-loading').style.display = 'none';
        document.getElementById('plan-view-error').style.display = 'block';
    }
}

// Populate plan view with data
function populatePlanView(plan, stats) {
    // Header
    document.getElementById('view_plan_name').textContent = plan.PlanName;
    
    const statusBadge = document.getElementById('view_plan_status');
    statusBadge.textContent = plan.IsActive ? 'Active' : 'Inactive';
    statusBadge.className = 'status-badge ' + (plan.IsActive ? 'active' : 'failed');
    
    document.getElementById('view_plan_type_display').innerHTML = 
        `<i class="bi bi-${getPlanTypeIconName(plan.PlanType)}"></i> ${plan.Duration} ${plan.PlanType}`;
    
    // Stats
    document.getElementById('view_active_members').textContent = stats.active_count;
    document.getElementById('view_total_revenue').textContent = '₱' + parseFloat(stats.total_revenue).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('view_total_members').textContent = stats.total_members;
    
    // Details tab
    document.getElementById('view_plan_name_detail').textContent = plan.PlanName;
    document.getElementById('view_plan_duration').textContent = `${plan.Duration} ${plan.PlanType}`;
    document.getElementById('view_plan_type').textContent = plan.PlanType;
    document.getElementById('view_plan_status_text').textContent = plan.IsActive ? 'Active' : 'Inactive';
    document.getElementById('view_plan_description').textContent = plan.Description;
    
    // Pricing tab
    document.getElementById('view_plan_rate').textContent = '₱' + parseFloat(plan.Rate).toFixed(2);
    
    // Calculate rate per day
    let daysTotal = parseInt(plan.Duration);
    if (plan.PlanType === 'Months') daysTotal *= 30;
    if (plan.PlanType === 'Years') daysTotal *= 365;
    const ratePerDay = parseFloat(plan.Rate) / daysTotal;
    document.getElementById('view_plan_rate_per_day').textContent = '₱' + ratePerDay.toFixed(2);
}

// Helper function for plan type icons
function getPlanTypeIconName(type) {
    const icons = {
        'Days': 'calendar-day',
        'Months': 'calendar3',
        'Years': 'calendar-range'
    };
    return icons[type] || 'calendar';
}

// Edit plan from view modal
function editPlanFromView() {
    PlanViewModal.close();
    if (currentViewPlanId) {
        // Open edit modal instead of redirecting
        setTimeout(() => {
            editPlan(currentViewPlanId);
        }, 300);
    }
}

// Initialize tab switching
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('#planViewModal .tab-link-modern');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panes
            tabLinks.forEach(l => l.classList.remove('active'));
            document.querySelectorAll('#planViewModal .tab-pane-modern').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Add active class to clicked tab and corresponding pane
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
});
</script>