<?php
/**
 * Staff Add Modal
 * Path: includes/staff_add_modal.php
 */
?>

<!-- ADD STAFF MODAL -->
<div class="modal-overlay" id="staffAddModal">
    <div class="modal-dialog-modern">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Add New Staff Member</h5>
                <button type="button" class="modal-close-modern" onclick="StaffAddModal.close()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="addStaffForm" enctype="multipart/form-data">
                <div class="modal-body-modern">

                    <!-- Photo Upload -->
                    <div class="photo-upload-section">
                        <div class="photo-preview-container">
                            <img src="assets/uploads/staff/admin.png" 
                                 alt="Staff Photo" 
                                 class="photo-preview" 
                                 id="add_staff_photo_preview">
                            <label for="add_staff_photo" class="photo-upload-btn">
                                <i class="bi bi-camera"></i>
                                <span>Upload Photo</span>
                            </label>
                            <input type="file" 
                                   id="add_staff_photo" 
                                   name="photo" 
                                   accept="image/*" 
                                   style="display: none;"
                                   onchange="previewStaffPhoto(this, 'add_staff_photo_preview')">
                        </div>
                        <p class="photo-upload-hint">
                            <i class="bi bi-info-circle"></i>
                            Maximum file size: 2MB. Supported formats: JPG, PNG, GIF
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
                                       required>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    Last Name <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control-modern" 
                                       name="last_name" 
                                       required>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    Email <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control-modern" 
                                       name="email" 
                                       required>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">Phone</label>
                                <input type="text" 
                                       class="form-control-modern" 
                                       name="phone" 
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
                                       required>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    Password <span class="required">*</span>
                                </label>

                                <div class="password-input-group">
                                    <input type="password" 
                                        class="form-control-modern" 
                                        name="password" 
                                        id="add_staff_password"
                                        required>

                                    <button type="button" 
                                            class="password-toggle-btn" 
                                            onclick="togglePasswordVisibility('add_staff_password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                <small class="form-hint">Minimum 8 characters</small>

                                <!-- 🔥 CAPSLOCK WARNING -->
                                <small id="caps_warning_addstaff" 
                                    style="color:#e53e3e; display:none; font-size:13px;">
                                    ⚠ Caps Lock is ON
                                </small>
                            </div>
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    Role <span class="required">*</span>
                                </label>
                                <select class="form-control-modern" name="role_id" required>
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
                                       value="<?= date('Y-m-d') ?>"
                                       required>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    Status <span class="required">*</span>
                                </label>
                                <select class="form-control-modern" name="status" required>
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer-modern">
                    <button type="button" 
                            class="btn-secondary-modern" 
                            onclick="StaffAddModal.close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-plus-circle"></i> Add Staff Member
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Staff Add Modal Manager
const StaffAddModal = new ModalManager('staffAddModal');

// Open add staff modal
function openAddStaffModal() {
    document.getElementById('addStaffForm').reset();
    document.getElementById('add_staff_photo_preview').src = 'assets/uploads/staff/admin.png';
    StaffAddModal.open();
}

// Preview staff photo
function previewStaffPhoto(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.currentTarget.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Handle add staff form submission
document.getElementById('addStaffForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Adding...';
    
    try {
        const response = await fetch('controllers/staff_create.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            toast.success(data.message);
            StaffAddModal.close();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            toast.error(data.message || 'Failed to add staff member');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        toast.error('An error occurred while adding staff member');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

//CapsLock detection
setTimeout(() => {
    enableCapsLockWarning("add_staff_password", "caps_warning_addstaff");
}, 100);

</script>