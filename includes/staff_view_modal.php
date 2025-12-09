<?php
/**
 * Staff View Modal
 * Path: includes/staff_view_modal.php
 */
?>

<!-- VIEW STAFF MODAL -->
<div class="modal-overlay" id="staffViewModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Staff Details</h5>
                <button type="button" class="modal-close-modern" onclick="StaffViewModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="modal-body-modern">

                <!-- Loading State -->
                <div id="staff-view-loading" class="loading-state-modern">
                    <div class="spinner-modern"></div>
                    <p>Loading staff details...</p>
                </div>

                <!-- Error State -->
                <div id="staff-view-error" class="error-state-modern" style="display: none;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <p>Failed to load staff details</p>
                </div>

                <!-- Staff Details Content -->
                <div id="staff-view-content" style="display: none;">

                    <!-- Staff Header with Photo -->
                    <div class="member-header-modern">
                        <img src="" alt="Staff Photo" class="member-avatar-modern" id="view_staff_photo" 
                             onerror="this.src='assets/uploads/staff/admin.png'">
                        <div class="member-header-info-modern">
                            <h2 class="member-name-modern" id="view_staff_name"></h2>
                            <div style="display: flex; gap: 12px; align-items: center; margin-top: 8px; flex-wrap: wrap;">
                                <span class="status-badge" id="view_staff_status"></span>
                                <span class="role-badge" id="view_staff_role"></span>
                                <span style="color: #64748b; font-size: 14px;" id="view_staff_email"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Stats Grid -->
                    <div class="plan-stat-grid">
                        <div class="plan-stat-item">
                            <div class="plan-stat-label">Members Registered</div>
                            <div class="plan-stat-value" id="view_members_registered">0</div>
                        </div>
                        <div class="plan-stat-item">
                            <div class="plan-stat-label">Payments Processed</div>
                            <div class="plan-stat-value" id="view_payments_processed">0</div>
                        </div>
                        <div class="plan-stat-item">
                            <div class="plan-stat-label">Total Revenue</div>
                            <div class="plan-stat-value" id="view_total_revenue">₱0.00</div>
                        </div>
                        <div class="plan-stat-item">
                            <div class="plan-stat-label">Memberships Created</div>
                            <div class="plan-stat-value" id="view_memberships_created">0</div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="tabs-modern">
                        <li class="tab-item-modern">
                            <a class="tab-link-modern active" data-tab="personal">
                                <i class="bi bi-person"></i> Personal Info
                            </a>
                        </li>
                        <li class="tab-item-modern">
                            <a class="tab-link-modern" data-tab="account">
                                <i class="bi bi-shield-lock"></i> Account Info
                            </a>
                        </li>
                        <li class="tab-item-modern">
                            <a class="tab-link-modern" data-tab="permissions">
                                <i class="bi bi-key"></i> Permissions
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content-modern">

                        <!-- Personal Info Tab -->
                        <div class="tab-pane-modern active" id="personal-tab">
                            <div class="info-grid-modern">
                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-person-badge"></i> First Name
                                    </div>
                                    <div class="info-value-modern" id="view_first_name"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-person-badge"></i> Last Name
                                    </div>
                                    <div class="info-value-modern" id="view_last_name"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-envelope"></i> Email
                                    </div>
                                    <div class="info-value-modern" id="view_email"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-telephone"></i> Phone
                                    </div>
                                    <div class="info-value-modern" id="view_phone"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-calendar-check"></i> Hire Date
                                    </div>
                                    <div class="info-value-modern" id="view_hire_date"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-clock-history"></i> Last Login
                                    </div>
                                    <div class="info-value-modern" id="view_last_login"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Info Tab -->
                        <div class="tab-pane-modern" id="account-tab">
                            <div class="info-grid-modern">
                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-person-circle"></i> Username
                                    </div>
                                    <div class="info-value-modern" id="view_username"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-award"></i> Role
                                    </div>
                                    <div class="info-value-modern" id="view_role_name"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-toggle-on"></i> Status
                                    </div>
                                    <div class="info-value-modern" id="view_status_text"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-calendar-plus"></i> Created At
                                    </div>
                                    <div class="info-value-modern" id="view_created_at"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-person-plus"></i> Created By
                                    </div>
                                    <div class="info-value-modern" id="view_created_by"></div>
                                </div>

                                <div class="info-item-modern">
                                    <div class="info-label-modern">
                                        <i class="bi bi-calendar-event"></i> Last Updated
                                    </div>
                                    <div class="info-value-modern" id="view_updated_at"></div>
                                </div>

                                <div class="info-item-modern full-width">
                                    <div class="info-label-modern">
                                        <i class="bi bi-file-text"></i> Role Description
                                    </div>
                                    <div class="info-value-modern" id="view_role_description"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions Tab -->
                        <div class="tab-pane-modern" id="permissions-tab">
                            <div class="permissions-container">
                                <h4 class="permissions-header">
                                    <i class="bi bi-shield-check"></i> 
                                    Role Permissions
                                </h4>
                                <ul id="view_permissions_list" class="permissions-list">
                                    <!-- Permissions will be populated here -->
                                    <!-- Example structure: -->
                                    <!-- <li>
                                        <i class="bi bi-check-circle-fill"></i>
                                        Can manage members
                                    </li>
                                    <li>
                                        <i class="bi bi-x-circle-fill"></i>
                                        Cannot access financial reports
                                    </li> -->
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer-modern">
                <button type="button" class="btn-secondary-modern" onclick="StaffViewModal.close()">
                    Close
                </button>
                <button type="button" class="btn-primary-modern" onclick="editStaffFromView()">
                    <i class="bi bi-pencil"></i> Edit Staff
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Initialize variables and modal when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal manager
    window.StaffViewModal = new ModalManager('staffViewModal');
    window.currentViewStaffId = null;

    // Initialize tab switching for staff view modal
    const tabLinks = document.querySelectorAll('#staffViewModal .tab-link-modern');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panes
            tabLinks.forEach(l => l.classList.remove('active'));
            document.querySelectorAll('#staffViewModal .tab-pane-modern').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Add active class to clicked tab and corresponding pane
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
});

// Open view staff modal
async function viewStaff(staffId) {
    window.currentViewStaffId = staffId;
    window.StaffViewModal.open();
    
    // Show loading state
    document.getElementById('staff-view-loading').style.display = 'block';
    document.getElementById('staff-view-error').style.display = 'none';
    document.getElementById('staff-view-content').style.display = 'none';
    
    try {
        const response = await fetch(`api/get_staff_details.php?id=${staffId}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load staff details');
        }
        
        // Populate staff details
        populateStaffView(data.staff, data.stats, data.permissions);
        
        // Hide loading, show content
        document.getElementById('staff-view-loading').style.display = 'none';
        document.getElementById('staff-view-content').style.display = 'block';
        
    } catch (error) {
        console.error('Error loading staff details:', error);
        document.getElementById('staff-view-loading').style.display = 'none';
        document.getElementById('staff-view-error').style.display = 'block';
    }
}

// Populate staff view with data
function populateStaffView(staff, stats, permissions) {
    // Header
    document.getElementById('view_staff_photo').src = staff.Photo 
        ? 'assets/uploads/staff/' + staff.Photo 
        : 'assets/uploads/staff/admin.png';
    document.getElementById('view_staff_name').textContent = staff.FullName;
    document.getElementById('view_staff_email').innerHTML = '<i class="bi bi-envelope"></i> ' + staff.Email;
    
    const statusBadge = document.getElementById('view_staff_status');
    statusBadge.textContent = staff.Status;
    statusBadge.className = 'status-badge ' + (staff.Status === 'Active' ? 'active' : 'failed');
    
    const roleBadge = document.getElementById('view_staff_role');
    roleBadge.textContent = staff.RoleName;
    roleBadge.className = 'role-badge ' + getRoleBadgeClassName(staff.AccessLevel);
    
    // Stats
    document.getElementById('view_members_registered').textContent = stats.members_registered;
    document.getElementById('view_payments_processed').textContent = stats.payments_processed;
    document.getElementById('view_total_revenue').textContent = '₱' + parseFloat(stats.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('view_memberships_created').textContent = stats.memberships_created;
    
    // Personal Info
    document.getElementById('view_first_name').textContent = staff.FirstName;
    document.getElementById('view_last_name').textContent = staff.LastName;
    document.getElementById('view_email').textContent = staff.Email;
    document.getElementById('view_phone').textContent = staff.Phone || 'N/A';
    document.getElementById('view_hire_date').textContent = formatDate(staff.HireDate);
    document.getElementById('view_last_login').textContent = staff.LastLogin ? formatDate(staff.LastLogin) : 'Never';
    
    // Account Info
    document.getElementById('view_username').textContent = staff.Username;
    document.getElementById('view_role_name').textContent = staff.RoleName;
    document.getElementById('view_status_text').textContent = staff.Status;
    document.getElementById('view_created_at').textContent = formatDate(staff.CreatedAt);
    document.getElementById('view_created_by').textContent = staff.CreatedByFirstName && staff.CreatedByLastName 
        ? staff.CreatedByFirstName + ' ' + staff.CreatedByLastName 
        : 'System';
    document.getElementById('view_updated_at').textContent = staff.UpdatedAt ? formatDate(staff.UpdatedAt) : 'N/A';
    document.getElementById('view_role_description').textContent = staff.RoleDescription || 'No description available';
    
    // Permissions
    const permissionsList = document.getElementById('view_permissions_list');
    permissionsList.innerHTML = '';
    permissions.forEach(permission => {
        const li = document.createElement('li');
        li.style.cssText = 'padding: 10px 0; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;';
        const isAllowed = !permission.includes('Cannot');
        li.innerHTML = `
            <i class="bi bi-${isAllowed ? 'check-circle-fill' : 'x-circle-fill'}" 
               style="color: ${isAllowed ? '#48bb78' : '#f56565'}; font-size: 18px;"></i>
            <span style="color: #2d3748; font-size: 14px;">${permission}</span>
        `;
        permissionsList.appendChild(li);
    });
}

// Helper function to get role badge class
function getRoleBadgeClassName(accessLevel) {
    const classes = {
        3: 'role-admin',
        2: 'role-manager',
        1: 'role-receptionist'
    };
    return classes[accessLevel] || 'role-default';
}

// Helper function to format dates
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Edit staff from view modal
function editStaffFromView() {
    window.StaffViewModal.close();
    if (window.currentViewStaffId) {
        setTimeout(() => {
            editStaff(window.currentViewStaffId);
        }, 300);
    }
}
</script>