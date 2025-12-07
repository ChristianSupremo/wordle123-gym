<!-- EDIT MEMBER MODAL -->
<div class="modal-overlay" id="memberEditModal">
    <div class="modal-dialog-modern modal-dialog-large">
        <div class="modal-content-modern">

            <div class="modal-header-modern">
                <h5 class="modal-title-modern">Edit Member</h5>
                <button type="button" class="modal-close-modern" onclick="closeEditModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <form id="editMemberForm" method="POST" action="members.php?action=update" enctype="multipart/form-data">
                <div class="modal-body-modern">

                    <!-- LOADING STATE -->
                    <div id="edit-member-loading" class="loading-state-modern">
                        <div class="spinner-modern"></div>
                        <p>Loading member details...</p>
                    </div>

                    <!-- FORM CONTENT -->
                    <div id="edit-member-form" style="display:none;">
                        <input type="hidden" name="member_id" id="edit_member_id">
                        <input type="hidden" name="action" value="update">

                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">Personal Information</h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="edit_first_name" class="form-label-modern">
                                        <i class="bi bi-person"></i> First Name
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_first_name" 
                                           name="first_name" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_last_name" class="form-label-modern">
                                        <i class="bi bi-person"></i> Last Name
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_last_name" 
                                           name="last_name" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_email" class="form-label-modern">
                                        <i class="bi bi-envelope"></i> Email
                                        <span class="required">*</span>
                                    </label>
                                    <input type="email" class="form-control-modern" id="edit_email" 
                                           name="email" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_phone" class="form-label-modern">
                                        <i class="bi bi-telephone"></i> Phone Number
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_phone" 
                                           name="phone_no" required>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_gender" class="form-label-modern">
                                        <i class="bi bi-gender-ambiguous"></i> Gender
                                    </label>
                                    <select class="form-control-modern" id="edit_gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_dob" class="form-label-modern">
                                        <i class="bi bi-calendar"></i> Date of Birth
                                    </label>
                                    <input type="date" class="form-control-modern" id="edit_dob" 
                                           name="date_of_birth">
                                </div>

                                <div class="form-group-modern full-width">
                                    <label for="edit_address" class="form-label-modern">
                                        <i class="bi bi-house"></i> Address
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_address" 
                                           name="address">
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_city" class="form-label-modern">
                                        <i class="bi bi-building"></i> City
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_city" 
                                           name="city">
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_province" class="form-label-modern">
                                        <i class="bi bi-map"></i> Province
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_province" 
                                           name="province">
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_zipcode" class="form-label-modern">
                                        <i class="bi bi-mailbox"></i> Zipcode
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_zipcode" 
                                           name="zipcode">
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">Emergency Contact</h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="edit_emergency_name" class="form-label-modern">
                                        <i class="bi bi-person-badge"></i> Contact Name
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_emergency_name" 
                                           name="emergency_contact_name">
                                </div>

                                <div class="form-group-modern">
                                    <label for="edit_emergency_phone" class="form-label-modern">
                                        <i class="bi bi-telephone-plus"></i> Contact Number
                                    </label>
                                    <input type="text" class="form-control-modern" id="edit_emergency_phone" 
                                           name="emergency_contact_number">
                                </div>
                            </div>
                        </div>

                        <!-- Status Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">Status</h4>
                            
                            <div class="form-grid">
                                <div class="form-group-modern">
                                    <label for="edit_status" class="form-label-modern">
                                        <i class="bi bi-check-circle"></i> Membership Status
                                    </label>
                                    <select class="form-control-modern" id="edit_status" name="membership_status">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Photo Upload Section -->
                        <div class="form-section">
                            <h4 class="section-title-form">Profile Photo</h4>
                            
                            <div class="photo-upload-container">
                                <div class="current-photo" id="edit_current_photo">
                                    <img src="" alt="Current Photo" id="edit_photo_preview" class="photo-preview">
                                </div>
                                <div class="photo-upload-controls">
                                    <label for="edit_photo" class="btn-upload">
                                        <i class="bi bi-camera"></i> Change Photo
                                    </label>
                                    <input type="file" id="edit_photo" name="photo" accept="image/*" 
                                           style="display:none;" onchange="previewEditPhoto(event)">
                                    <p class="help-text">JPG, PNG or GIF. Max 2MB</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Error state -->
                    <div class="error-state-modern" id="edit-error" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Failed to load member details</p>
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" class="btn-secondary-modern" onclick="closeEditModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary-modern" id="saveEditBtn">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- EDIT MEMBER MODAL JS -->
<script>
// Open edit modal
function editMember(memberId) {
    const modal = document.getElementById('memberEditModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Reset UI
    document.getElementById('edit-member-form').style.display = 'none';
    document.getElementById('edit-member-loading').style.display = 'block';
    document.getElementById('edit-error').style.display = 'none';

    // Fetch member data
    fetch('api/get_members.php?id=' + memberId)
        .then(response => response.json())
        .then(data => {
            const member = data.member;

            // Show form
            document.getElementById('edit-member-loading').style.display = 'none';
            document.getElementById('edit-member-form').style.display = 'block';

            // Populate form fields
            document.getElementById('edit_member_id').value = member.MemberID;
            document.getElementById('edit_first_name').value = member.FirstName || '';
            document.getElementById('edit_last_name').value = member.LastName || '';
            document.getElementById('edit_email').value = member.Email || '';
            document.getElementById('edit_phone').value = member.PhoneNo || '';
            document.getElementById('edit_gender').value = member.Gender || '';
            document.getElementById('edit_dob').value = member.DateOfBirth || '';
            document.getElementById('edit_address').value = member.Address || '';
            document.getElementById('edit_city').value = member.City || '';
            document.getElementById('edit_province').value = member.Province || '';
            document.getElementById('edit_zipcode').value = member.Zipcode || '';
            document.getElementById('edit_emergency_name').value = member.EmergencyContactName || '';
            document.getElementById('edit_emergency_phone').value = member.EmergencyContactNumber || '';
            document.getElementById('edit_status').value = member.MembershipStatus || 'Active';

            // Set photo preview
            const photo = member.Photo
                ? 'assets/uploads/members/' + member.Photo
                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(member.FirstName + ' ' + member.LastName) + '&background=4fd1c5&color=fff&size=200';
            
            document.getElementById('edit_photo_preview').src = photo;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('edit-member-loading').style.display = 'none';
            document.getElementById('edit-error').style.display = 'block';
            // MODERN ERROR TOAST
            toast.error('Failed to load member details. Please try again.', 5000);
        });
}

// Close edit modal
function closeEditModal() {
    const modal = document.getElementById('memberEditModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.getElementById('editMemberForm').reset();
}

// Preview photo before upload
function previewEditPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        // Check file size - MODERN TOAST
        if (file.size > 2 * 1024 * 1024) {
            toast.warning('File size too large. Maximum 2MB allowed.', 5000);
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('edit_photo_preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
// Close modal when clicking outside
document.addEventListener('mousedown', function(event) {
    const overlay = document.getElementById('memberEditModal');

    // Only close when clicking *directly* on overlay (not children!)
    if (event.target === overlay) {
        closeEditModal();
    }
});

// Form submission with MODERN TOAST NOTIFICATIONS
document.getElementById('editMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const saveBtn = document.getElementById('saveEditBtn');
    
    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
    
    fetch('controllers/member_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // MODERN SUCCESS TOAST
            toast.success('Member updated successfully! Refreshing...', 3000);
            closeEditModal();
            
            // Reload after a short delay so user can see the toast
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // MODERN ERROR TOAST
            toast.error(data.message || 'Failed to update member. Please try again.', 5000);
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save Changes';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // MODERN ERROR TOAST
        toast.error('Network error. Please check your connection and try again.', 5000);
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save Changes';
    });
});
</script>