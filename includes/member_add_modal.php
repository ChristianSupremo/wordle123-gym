<!-- ADD MEMBER MODAL -->
<div class="modal-overlay" id="memberAddModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Add New Member</h5>
                <button type="button" class="modal-close-modern" onclick="closeAddModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="addMemberForm" method="POST" action="controllers/member_create.php" enctype="multipart/form-data">
                <div class="modal-body-modern">

                    <!-- FORM CONTENT -->
                    <div id="add-member-form">
                        <input type="hidden" name="action" value="create">

                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-person-circle"></i> Personal Information
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="add_first_name" class="form-label-modern">
                                        <i class="bi bi-person"></i> First Name
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_first_name" 
                                           name="first_name" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_last_name" class="form-label-modern">
                                        <i class="bi bi-person"></i> Last Name
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_last_name" 
                                           name="last_name" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_email" class="form-label-modern">
                                        <i class="bi bi-envelope"></i> Email
                                        <span class="required">*</span>
                                    </label>
                                    <input type="email" class="form-control-modern" id="add_email" 
                                           name="email" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_phone" class="form-label-modern">
                                        <i class="bi bi-telephone"></i> Phone Number
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_phone" 
                                           name="phone_no" required placeholder="09123456789">
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_gender" class="form-label-modern">
                                        <i class="bi bi-gender-ambiguous"></i> Gender
                                    </label>
                                    <select class="form-control-modern" id="add_gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_dob" class="form-label-modern">
                                        <i class="bi bi-calendar"></i> Date of Birth
                                    </label>
                                    <input type="date" class="form-control-modern" id="add_dob" 
                                           name="date_of_birth">
                                </div>

                                <div class="form-group-modern full-width">
                                    <label for="add_address" class="form-label-modern">
                                        <i class="bi bi-house"></i> Address
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_address" 
                                           name="address" placeholder="Street Address">
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_city" class="form-label-modern">
                                        <i class="bi bi-building"></i> City
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_city" 
                                           name="city" placeholder="City">
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_province" class="form-label-modern">
                                        <i class="bi bi-map"></i> Province
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_province" 
                                           name="province" placeholder="Province">
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_zipcode" class="form-label-modern">
                                        <i class="bi bi-mailbox"></i> Zipcode
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_zipcode" 
                                           name="zipcode" placeholder="0000">
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-telephone-plus"></i> Emergency Contact
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="add_emergency_name" class="form-label-modern">
                                        <i class="bi bi-person-badge"></i> Contact Name
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_emergency_name" 
                                           name="emergency_contact_name" placeholder="Full Name">
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_emergency_phone" class="form-label-modern">
                                        <i class="bi bi-telephone-plus"></i> Contact Number
                                    </label>
                                    <input type="text" class="form-control-modern" id="add_emergency_phone" 
                                           name="emergency_contact_number" placeholder="09123456789">
                                </div>
                            </div>
                        </div>

                        <!-- Membership Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-card-checklist"></i> Membership Details
                            </h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="add_status" class="form-label-modern">
                                        <i class="bi bi-check-circle"></i> Membership Status
                                    </label>
                                    <select class="form-control-modern" id="add_status" name="membership_status">
                                        <option value="Active" selected>Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="add_join_date" class="form-label-modern">
                                        <i class="bi bi-calendar-check"></i> Join Date
                                    </label>
                                    <input type="date" class="form-control-modern" id="add_join_date" 
                                           name="join_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Photo Upload Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">
                                <i class="bi bi-camera"></i> Profile Photo
                            </h4>
                            
                            <div class="photo-upload-container">
                                <div class="current-photo">
                                    <img src="https://ui-avatars.com/api/?name=New+Member&background=4fd1c5&color=fff&size=200" 
                                         alt="Preview" id="add_photo_preview" class="photo-preview">
                                </div>
                                <div class="photo-upload-controls">
                                    <label for="add_photo" class="btn-upload">
                                        <i class="bi bi-camera"></i> Upload Photo
                                    </label>
                                    <input type="file" id="add_photo" name="photo" accept="image/*" 
                                           style="display:none;" onchange="previewAddPhoto(event)">
                                    <p class="help-text">JPG, PNG or GIF. Max 2MB</p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="closeAddModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="saveAddBtn">
                        <i class="bi bi-check-lg"></i> Create Member
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ADD MEMBER MODAL JS -->
<script>
// Open add modal
function openAddModal() {
    const modal = document.getElementById('memberAddModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');
    
    // Reset form
    document.getElementById('addMemberForm').reset();
    document.getElementById('add_photo_preview').src = 'https://ui-avatars.com/api/?name=New+Member&background=4fd1c5&color=fff&size=200';
    document.getElementById('add_join_date').value = new Date().toISOString().split('T')[0];
}

// Close add modal
function closeAddModal() {
    const modal = document.getElementById('memberAddModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.getElementById('addMemberForm').reset();
}

// Preview photo before upload
function previewAddPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        // Check file size
        if (file.size > 2 * 1024 * 1024) {
            alert('File size too large. Maximum 2MB allowed.');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('add_photo_preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}

// Close modal when clicking outside (but not when selecting text)
let addModalMouseDownTarget = null;

document.addEventListener('mousedown', function(event) {
    addModalMouseDownTarget = event.target;
});

document.addEventListener('mouseup', function(event) {
    const modal = document.getElementById('memberAddModal');
    
    if (addModalMouseDownTarget === modal && 
        event.target === modal && 
        window.getSelection().toString().length === 0) {
        closeAddModal();
    }
    
    addModalMouseDownTarget = null;
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('memberAddModal');
        if (modal.classList.contains('show')) {
            closeAddModal();
        }
    }
});

// Form submission
document.getElementById('addMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const saveBtn = document.getElementById('saveAddBtn');
    
    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
    
    fetch('controllers/member_create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Member created successfully!');
            closeAddModal();
            // Reload the page to show new member
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to create member'));
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Create Member';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create member');
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Create Member';
    });
});
</script>