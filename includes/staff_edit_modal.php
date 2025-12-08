<?php
/**
 * Staff Edit Modal
 * Path: includes/staff_edit_modal.php
 */
?>

<!-- EDIT STAFF MODAL -->
<div class="modal-overlay" id="staffEditModal">
    <div class="modal-dialog-modern">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Edit Staff Member</h5>
                <button type="button" class="modal-close-modern" onclick="StaffEditModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="editStaffForm" enctype="multipart/form-data">
                <input type="hidden" name="staff_id" id="edit_staff_id">
                
                <div class="modal-body-modern">

                    <!-- Loading State -->
                    <div id="edit-staff-loading" class="loading-state-modern" style="display: none;">
                        <div class="spinner-modern"></div>
                        <p>Loading staff details...</p>
                    </div>

                    <!-- Content -->
                    <div id="edit-staff-content">

                        <!-- Photo Upload -->
                        <div class="photo-upload-section">
                            <div class="photo-preview-container">
                                <img src="assets/uploads/staff/admin.png" 
                                     alt="Staff Photo" 
                                     class="photo-preview" 
                                     id="edit_staff_photo_preview">
                                <label for="edit_staff_photo" class="photo-upload-btn">
                                    <i class="bi bi-camera"></i>
                                    <span>Change Photo</span>
                                </label>
                                <input type="file" 
                                       id="edit_staff_photo" 
                                       name="photo" 
                                       accept="image/*" 
                                       style="display: none;"
                                       onchange="previewStaffPhoto(this, 'edit_staff_photo_preview')">
                            </div>
                            <p class="photo-upload-hint">
                                <i class="bi bi-info-circle"></i>
                                Leave empty to keep current photo
                            </p>
                        </div>

                        <!-- Personal Information -->
                        <div class="form-section-modern">
                            <h6 class="form-section-title">Personal Information</h6>
                            <div class="form-grid-modern">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        First Name <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control-modern" 
                                           name="first_name" 
                                           id="edit_first_name"
                                           required>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        Last Name <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control-modern" 
                                           name="last_name" 
                                           id="edit_last_name"
                                           required>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        Email <span class="required">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control-modern" 
                                           name="email" 
                                           id="edit_email"
                                           required>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">Phone</label>
                                    <input type="text" 
                                           class="form-control-modern" 
                                           name="phone" 
                                           id="edit_phone"
                                           placeholder="09XX XXX XXXX">
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="form-section-modern">
                            <h6 class="form-section-title">Account Information</h6>
                            <div class="form-grid-modern">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        Username <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control-modern" 
                                           name="username" 
                                           id="edit_username"
                                           required>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        New Password
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" 
                                               class="form-control-modern" 
                                               name="password" 
                                               id="edit_staff_password"
                                               placeholder="Leave blank to keep current">
                                        <button type="button" 
                                                class="password-toggle-btn" 
                                                onclick="togglePasswordVisibility('edit_staff_password')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <small class="form-hint">Leave blank to keep current password</small>

                                    <!-- 🔥 CAPSLOCK WARNING -->
                                    <small id="caps_warning_editstaff" 
                                        style="color:#e53e3e; display:none; font-size:13px;">
                                        ⚠ Caps Lock is ON
                                    </small>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        Role <span class="required">*</span>
                                    </label>
                                    <select class="form-control-modern" name="role_id" id="edit_role_id" required>
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['RoleID'] ?>">
                                                <?= htmlspecialchars($role['RoleName']) ?> 
                                                (Level <?= $role['AccessLevel'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        Hire Date <span class="required">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control-modern" 
                                           name="hire_date" 
                                           id="edit_hire_date"
                                           required>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        Status <span class="required">*</span>
                                    </label>
                                    <select class="form-control-modern" name="status" id="edit_status" required>
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer-modern">
                    <button type="button" 
                            class="btn-secondary-modern" 
                            onclick="StaffEditModal.close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-check-circle"></i> Update Staff Member
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Staff Edit Modal Manager
const StaffEditModal = new ModalManager('staffEditModal');

// Current staff being edited
let currentEditStaffId = null;

// Open edit staff modal
async function editStaff(staffId) {
    currentEditStaffId = staffId;
    StaffEditModal.open();
    
    // Show loading
    document.getElementById('edit-staff-loading').style.display = 'block';
    document.getElementById('edit-staff-content').style.display = 'none';
    
    try {
        const response = await fetch(`api/get_staff_details.php?id=${staffId}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load staff details');
        }
        
        // Populate form
        populateEditStaffForm(data.staff);
        
        // Hide loading, show content
        document.getElementById('edit-staff-loading').style.display = 'none';
        document.getElementById('edit-staff-content').style.display = 'block';
        
    } catch (error) {
        console.error('Error loading staff details:', error);
        toast.error('Failed to load staff details');
        StaffEditModal.close();
    }
}

// Populate edit form with staff data
function populateEditStaffForm(staff) {
    document.getElementById('edit_staff_id').value = staff.StaffID;
    document.getElementById('edit_first_name').value = staff.FirstName;
    document.getElementById('edit_last_name').value = staff.LastName;
    document.getElementById('edit_email').value = staff.Email;
    document.getElementById('edit_phone').value = staff.Phone || '';
    document.getElementById('edit_username').value = staff.Username;
    document.getElementById('edit_role_id').value = staff.RoleID;
    document.getElementById('edit_hire_date').value = staff.HireDate;
    document.getElementById('edit_status').value = staff.Status;
    
    // Set photo preview
    const photoSrc = staff.Photo 
        ? 'assets/uploads/staff/' + staff.Photo 
        : 'assets/uploads/staff/admin.png';
    document.getElementById('edit_staff_photo_preview').src = photoSrc;
    
    // Clear password field
    document.getElementById('edit_staff_password').value = '';
}

// Handle edit staff form submission
document.getElementById('editStaffForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
    
    try {
        const response = await fetch('controllers/staff_update.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            toast.success(data.message);
            StaffEditModal.close();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            toast.error(data.message || 'Failed to update staff member');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        toast.error('An error occurred while updating staff member');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

//CapsLock detection
setTimeout(() => {
    enableCapsLockWarning("edit_staff_password", "caps_warning_editstaff");
}, 100);

</script>