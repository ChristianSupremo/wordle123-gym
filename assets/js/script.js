class ToastNotification {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    show(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icons = {
            success: '<i class="bi bi-check-circle-fill"></i>',
            error: '<i class="bi bi-x-circle-fill"></i>',
            warning: '<i class="bi bi-exclamation-triangle-fill"></i>',
            info: '<i class="bi bi-info-circle-fill"></i>'
        };

        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="bi bi-x"></i>
            </button>
        `;

        this.container.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);

        return toast;
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Initialize toast notification globally
const toast = new ToastNotification();

// Modern Confirmation Dialog
class ConfirmDialog {
    show(options = {}) {
        return new Promise((resolve) => {
            const {
                title = 'Confirm Action',
                message = 'Are you sure you want to proceed?',
                confirmText = 'Confirm',
                cancelText = 'Cancel',
                type = 'warning' // warning, danger, info
            } = options;

            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            
            const icons = {
                warning: '<i class="bi bi-exclamation-triangle-fill text-warning"></i>',
                danger: '<i class="bi bi-exclamation-octagon-fill text-danger"></i>',
                info: '<i class="bi bi-info-circle-fill text-info"></i>'
            };

            overlay.innerHTML = `
                <div class="confirm-dialog">
                    <div class="confirm-icon">${icons[type]}</div>
                    <h3 class="confirm-title">${title}</h3>
                    <p class="confirm-message">${message}</p>
                    <div class="confirm-actions">
                        <button class="btn-cancel" id="confirmCancel">${cancelText}</button>
                        <button class="btn-confirm btn-${type}" id="confirmOk">${confirmText}</button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);
            document.body.classList.add('modal-open');

            // Trigger animation
            setTimeout(() => overlay.classList.add('show'), 10);

            // Handle cancel
            const handleCancel = () => {
                overlay.classList.remove('show');
                document.body.classList.remove('modal-open');
                setTimeout(() => overlay.remove(), 300);
                resolve(false);
            };

            // Handle confirm
            const handleConfirm = () => {
                overlay.classList.remove('show');
                document.body.classList.remove('modal-open');
                setTimeout(() => overlay.remove(), 300);
                resolve(true);
            };

            // Event listeners
            document.getElementById('confirmCancel').addEventListener('click', handleCancel);
            document.getElementById('confirmOk').addEventListener('click', handleConfirm);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) handleCancel();
            });

            // ESC key
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    handleCancel();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
        });
    }
}

// Initialize confirm dialog globally
const confirm = new ConfirmDialog();

// ========================================
// SIDEBAR TOGGLE FUNCTIONALITY
// ========================================

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    
    sidebar.classList.toggle('collapsed');
    body.classList.toggle('sidebar-collapsed');
    
    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
    
    // Add/remove overlay
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.onclick = toggleMobileMenu;
        document.body.appendChild(overlay);
    }
    overlay.classList.toggle('active');
}

// ========================================
// DARK MODE FUNCTIONALITY
// ========================================

function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    if (!darkModeToggle) return;
    
    // Check for saved dark mode preference
    const darkModeEnabled = localStorage.getItem('darkMode') === 'enabled';
    
    if (darkModeEnabled) {
        document.body.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }
    
    // Toggle dark mode
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
            toast.success('Dark mode enabled');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
            toast.success('Light mode enabled');
        }
    });
}

// ========================================
// INITIALIZATION ON PAGE LOAD
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Restore sidebar state
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed && sidebar) {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }
    
    // Initialize dark mode
    initDarkMode();
    
    // Handle mobile menu
    handleMobileMenu();
});

// ========================================
// MOBILE MENU HANDLING
// ========================================

function handleMobileMenu() {
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                sidebar.classList.remove('active');
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.classList.remove('active');
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
            const overlay = document.querySelector('.sidebar-overlay');
            if (overlay) overlay.remove();
        }
    });
}

//EDIT MEMBERSHIP MODAL JS
// Initialize searchable select for member field
window.initEditMemberSearch =function initEditMemberSearch() {
    const searchInput = document.getElementById('edit_member_search');
    const hiddenInput = document.getElementById('edit_member_id');
    const dropdown = document.getElementById('edit_member_dropdown');
    const options = dropdown.querySelectorAll('.searchable-select-option');

    // Show dropdown on focus
    searchInput.addEventListener('focus', function() {
        dropdown.classList.add('show');
        filterOptions('');
    });

    // Filter options on input
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterOptions(searchTerm);
    });

    // Handle option selection
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.getAttribute('data-text');
            
            hiddenInput.value = value;
            searchInput.value = text;
            dropdown.classList.remove('show');
            
            // Update selected state
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Filter options function
    function filterOptions(searchTerm) {
        let hasVisibleOptions = false;
        
        options.forEach(option => {
            const text = option.getAttribute('data-text').toLowerCase();
            const matches = text.includes(searchTerm);
            
            if (matches) {
                option.classList.remove('hidden');
                hasVisibleOptions = true;
            } else {
                option.classList.add('hidden');
            }
        });

        // Show/hide dropdown based on visible options
        if (hasVisibleOptions) {
            dropdown.classList.add('show');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const visibleOptions = Array.from(options).filter(opt => !opt.classList.contains('hidden'));
        const currentIndex = visibleOptions.findIndex(opt => opt.classList.contains('selected'));

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIndex = currentIndex < visibleOptions.length - 1 ? currentIndex + 1 : 0;
            if (visibleOptions[nextIndex]) {
                options.forEach(opt => opt.classList.remove('selected'));
                visibleOptions[nextIndex].classList.add('selected');
                visibleOptions[nextIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIndex = currentIndex > 0 ? currentIndex - 1 : visibleOptions.length - 1;
            if (visibleOptions[prevIndex]) {
                options.forEach(opt => opt.classList.remove('selected'));
                visibleOptions[prevIndex].classList.add('selected');
                visibleOptions[prevIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const selectedOption = visibleOptions.find(opt => opt.classList.contains('selected'));
            if (selectedOption) {
                selectedOption.click();
            } else if (visibleOptions.length > 0) {
                visibleOptions[0].click();
            }
        } else if (e.key === 'Escape') {
            dropdown.classList.remove('show');
        }
    });
}