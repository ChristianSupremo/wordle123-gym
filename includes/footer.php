<?php // includes/footer.php ?>
        </main> <!-- /.main-content -->
    </div> <!-- /.dashboard-wrapper -->

    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()" style="display: none;">
        <i class="bi bi-list"></i>
    </button>

    <script>
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });

            // Check for saved dark mode preference
            if (localStorage.getItem('darkMode') === 'enabled') {
                darkModeToggle.checked = true;
                document.body.classList.add('dark-mode');
            }
        }

        // Show mobile menu toggle on small screens
        function checkScreenSize() {
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            if (mobileToggle) {
                if (window.innerWidth <= 768) {
                    mobileToggle.style.display = 'block';
                } else {
                    mobileToggle.style.display = 'none';
                    document.getElementById('sidebar').classList.remove('active');
                }
            }
        }

        window.addEventListener('resize', checkScreenSize);
        window.addEventListener('load', checkScreenSize);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
    <script src="assets/js/memberships.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) == 'memberships.php'): ?>
        <script src="assets/js/membership_page.js"></script>
    <?php endif; ?>
    <script src="assets/js/payment_modals.js"></script>
    <script src="assets/js/capslock_detector.js"></script>
</body>
</html>