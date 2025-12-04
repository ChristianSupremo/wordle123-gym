<!-- VIEW MEMBER MODAL -->
<div class="modal fade" id="memberViewModal" tabindex="-1" aria-labelledby="memberViewModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title" id="memberViewModalLabel">Member Profile</h5>
            <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
            </button>
        </div>

        <div class="modal-body">

            <!-- LOADING STATE -->
            <div id="member-details-loading" class="text-center py-3">
                <div class="spinner-border"></div>
                <p>Loading member details...</p>
            </div>

            <!-- CONTENT -->
            <div id="member-details" style="display:none;">

                <!-- PHOTO + NAME -->
                <div class="text-center mb-3">
                    <img id="view-photo" src="" 
                         class="rounded-circle mb-2"
                         width="120" height="120"
                         style="object-fit:cover; border:2px solid #ccc;">
                    <h4 id="view-name"></h4>
                </div>

                <!-- TABS -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-profile">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-memberships">Memberships</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-payments">Payments</a>
                    </li>
                </ul>

                <!-- TAB CONTENT -->
                <div class="tab-content mt-3">

                    <!-- PROFILE TAB -->
                    <div class="tab-pane fade show active" id="tab-profile">
                        <p><strong>Email:</strong> <span id="view-email"></span></p>
                        <p><strong>Phone:</strong> <span id="view-phone"></span></p>
                        <p><strong>Gender:</strong> <span id="view-gender"></span></p>
                        <p><strong>Date of Birth:</strong> <span id="view-dob"></span></p>
                        <p><strong>Address:</strong> <span id="view-address"></span></p>
                        <p><strong>Emergency Contact:</strong> <span id="view-emergency-name"></span></p>
                        <p><strong>Emergency Number:</strong> <span id="view-emergency-number"></span></p>
                        <p><strong>Status:</strong> <span id="view-status" class="badge"></span></p>
                    </div>

                    <!-- MEMBERSHIPS TAB -->
                    <div class="tab-pane fade" id="tab-memberships">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="membership-table">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- PAYMENTS TAB -->
                    <div class="tab-pane fade" id="tab-payments">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="payments-table">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>

                </div> <!-- tab-content -->

            </div><!-- member-details -->
        </div><!-- modal-body -->

    </div>
</div>
</div>

<!-- VIEW MEMBER MODAL JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.view-member-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const memberId = this.dataset.id;

            // RESET UI
            document.getElementById('member-details').style.display = 'none';
            document.getElementById('member-details-loading').style.display = 'block';

            // FETCH MEMBER DATA
            fetch('api/get_members.php?id=' + memberId)
                .then(response => response.json())
                .then(data => {

                    const member = data.member;

                    // SHOW CONTENT
                    document.getElementById('member-details-loading').style.display = 'none';
                    document.getElementById('member-details').style.display = 'block';

                    // PHOTO
                    const photo = member.Photo
                        ? 'assets/uploads/members/' + member.Photo
                        : 'assets/uploads/members/default.jpg';

                    document.getElementById('view-photo').src = photo;

                    // BASIC PROFILE
                    document.getElementById('view-name').textContent =
                        member.FirstName + " " + member.LastName;

                    document.getElementById('view-email').textContent = member.Email;
                    document.getElementById('view-phone').textContent = member.PhoneNo;
                    document.getElementById('view-gender').textContent = member.Gender;
                    document.getElementById('view-dob').textContent = member.DateOfBirth;

                    document.getElementById('view-address').textContent =
                        `${member.Address}, ${member.City}, ${member.Province} ${member.Zipcode}`;

                    document.getElementById('view-emergency-name').textContent = member.EmergencyContactName;
                    document.getElementById('view-emergency-number').textContent = member.EmergencyContactNumber;

                    // STATUS BADGE
                    const badge = document.getElementById('view-status');
                    badge.textContent = member.MembershipStatus;
                    badge.className = "badge " + (
                        member.MembershipStatus === "Active" ? "bg-success" :
                        member.MembershipStatus === "Inactive" ? "bg-warning" :
                        "bg-secondary"
                    );

                    /* ------------------------------
                       MEMBERSHIP TABLE
                    -------------------------------*/
                    const membershipTable = document.getElementById('membership-table');
                    membershipTable.innerHTML = "";

                    data.memberships.forEach(m => {
                        membershipTable.innerHTML += `
                            <tr>
                                <td>${m.PlanName}</td>
                                <td>${m.StartDate}</td>
                                <td>${m.EndDate}</td>
                                <td>
                                    <span class="badge ${
                                        m.Status === 'Active' ? 'bg-success' :
                                        m.Status === 'Expired' ? 'bg-warning' :
                                        'bg-secondary'
                                    }">${m.Status}</span>
                                </td>
                            </tr>
                        `;
                    });

                    /* ------------------------------
                       PAYMENTS TABLE
                    -------------------------------*/
                    const paymentsTable = document.getElementById('payments-table');
                    paymentsTable.innerHTML = "";

                    data.payments.forEach(p => {
                        paymentsTable.innerHTML += `
                            <tr>
                                <td>${p.PaymentDate}</td>
                                <td>₱${parseFloat(p.AmountPaid).toFixed(2)}</td>
                                <td>${p.PaymentMethod}</td>
                                <td>${p.ReferenceNumber ?? ''}</td>
                                <td>
                                    <span class="badge ${
                                        p.PaymentStatus === 'Completed' ? 'bg-success' :
                                        p.PaymentStatus === 'Pending' ? 'bg-warning' :
                                        'bg-danger'
                                    }">${p.PaymentStatus}</span>
                                </td>
                            </tr>
                        `;
                    });

                    // Ensure the first tab always opens on fresh click
                    document.querySelector('.nav-link.active').classList.remove('active');
                    document.querySelector('#tab-profile').classList.add('show', 'active');
                    document.querySelector("a[href='#tab-profile']").classList.add('active');
                });
        });
    });
});
</script>
