// Modern Toast Notification System
// Add this to your script.js file

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

// Example usage:
// toast.success('Member created successfully!');
// toast.error('Failed to create member');
// toast.warning('File size too large');
// toast.info('Please fill all required fields');
//
// const confirmed = await confirm.show({
//     title: 'Delete Member',
//     message: 'Are you sure you want to delete this member? This action cannot be undone.',
//     confirmText: 'Delete',
//     cancelText: 'Cancel',
//     type: 'danger'
// });
// if (confirmed) {
//     // Do delete action
// }

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