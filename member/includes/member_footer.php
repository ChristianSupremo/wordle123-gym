        </main> <!-- /.main-content -->
    </div> <!-- /.dashboard-wrapper -->

    <button class="mobile-menu-toggle" onclick="toggleSidebar()" style="display: none;">
        <i class="bi bi-list"></i>
    </button>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/capslock_detector.js"></script>

    <?php if (!empty($_SESSION['member_login_success'])): ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        toast.success(<?= json_encode('Welcome back, ' . ($_SESSION['member_name'] ?? 'Member') . '!') ?>);
    });
    </script>
    <?php unset($_SESSION['member_login_success']); endif; ?>
    <?php if (!empty($page_scripts)) echo $page_scripts; ?>
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
