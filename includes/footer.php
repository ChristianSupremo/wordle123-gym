<?php // includes/footer.php ?>
        </main> <!-- /.main-content -->
    </div> <!-- /.dashboard-wrapper -->

    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()" style="display: none;">
        <i class="bi bi-list"></i>
    </button>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/memberships.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) == 'memberships.php'): ?>
        <script src="assets/js/membership_page.js"></script>
    <?php endif; ?>
    <script src="assets/js/payment_modals.js"></script>
    <script src="assets/js/capslock_detector.js"></script>

    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        toast.success('Welcome back, <?php echo addslashes($_SESSION['staff_name']); ?>!');
    });
    </script>
    <?php unset($_SESSION['login_success']); endif; ?>
    <script>
    function logoutNow() {
        confirm.show({
            title: "Confirm Logout",
            message: "Are you sure you want to log out?",
            confirmText: "Log Out",
            cancelText: "Cancel",
            type: "warning"
        }).then((ok) => {
            if (ok) {
                window.location.href = "logout.php";
            }
        });
    }
    </script>
</body>
</html>