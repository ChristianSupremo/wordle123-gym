<h2><?= $member['MemberID'] ? 'Edit Member' : 'Add Member' ?></h2>

<form action="members.php" method="POST">
<input type="hidden" name="member_id" value="<?= $member['MemberID'] ?>">

<div class="form-row">
    <div class="form-group col-md-6">
        <label>First Name</label>
        <input class="form-control" name="FirstName"
               value="<?= htmlspecialchars($member['FirstName']) ?>" required>
    </div>
    <div class="form-group col-md-6">
        <label>Last Name</label>
        <input class="form-control" name="LastName"
               value="<?= htmlspecialchars($member['LastName']) ?>" required>
    </div>
</div>

<div class="form-group">
    <label>Address</label>
    <textarea class="form-control" name="Address"><?= htmlspecialchars($member['Address']) ?></textarea>
</div>

<div class="form-row">
    <div class="form-group col-md-4"><label>City</label>
        <input class="form-control" name="City" value="<?= $member['City'] ?>">
    </div>
    <div class="form-group col-md-4"><label>Province</label>
        <input class="form-control" name="Province" value="<?= $member['Province'] ?>">
    </div>
    <div class="form-group col-md-4"><label>Zipcode</label>
        <input class="form-control" name="Zipcode" value="<?= $member['Zipcode'] ?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Date of Birth</label>
        <input type="date" class="form-control" name="DateOfBirth" value="<?= $member['DateOfBirth'] ?>">
    </div>
    <div class="form-group col-md-6">
        <label>Gender</label>
        <select name="Gender" class="form-control">
            <option value="Male"   <?= $member['Gender']=='Male'?'selected':'' ?>>Male</option>
            <option value="Female" <?= $member['Gender']=='Female'?'selected':'' ?>>Female</option>
            <option value="Other"  <?= $member['Gender']=='Other'?'selected':'' ?>>Other</option>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Phone Number</label>
        <input class="form-control" name="PhoneNo" value="<?= $member['PhoneNo'] ?>">
    </div>
    <div class="form-group col-md-6">
        <label>Email</label>
        <input type="email" class="form-control" name="Email"
               value="<?= $member['Email'] ?>" required>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Emergency Contact Name</label>
        <input class="form-control" name="EmergencyContactName"
               value="<?= $member['EmergencyContactName'] ?>">
    </div>
    <div class="form-group col-md-6">
        <label>Emergency Contact Number</label>
        <input class="form-control" name="EmergencyContactNumber"
               value="<?= $member['EmergencyContactNumber'] ?>">
    </div>
</div>

<div class="form-group">
    <label>Status</label>
    <select name="MembershipStatus" class="form-control">
        <option value="Active"   <?= $member['MembershipStatus']=='Active'?'selected':'' ?>>Active</option>
        <option value="Inactive" <?= $member['MembershipStatus']=='Inactive'?'selected':'' ?>>Inactive</option>
        <option value="Pending"  <?= $member['MembershipStatus']=='Pending'?'selected':'' ?>>Pending</option>
    </select>
</div>

<button class="btn btn-success">Save</button>
<a href="members.php" class="btn btn-secondary">Cancel</a>
</form>
