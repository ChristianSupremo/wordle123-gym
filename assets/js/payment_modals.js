/* ==========================================
   PAYMENT MODAL SHARED JAVASCRIPT
   ========================================== */

/**
 * Shared modal close handler with text selection support
 */
class ModalManager {
    constructor(modalId) {
        this.modalId = modalId;
        this.modal = document.getElementById(modalId);
        this.mouseDownTarget = null;
        
        this.initEventListeners();
    }
    
    initEventListeners() {
        // Track mousedown target
        document.addEventListener('mousedown', (e) => {
            this.mouseDownTarget = e.target;
        });
        
        // Handle click outside
        document.addEventListener('mouseup', (e) => {
            if (this.mouseDownTarget === this.modal && 
                e.target === this.modal && 
                window.getSelection().toString().length === 0) {
                this.close();
            }
            this.mouseDownTarget = null;
        });
        
        // Handle Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });
    }
    
    open() {
        this.modal.classList.add('show');
        document.body.classList.add('modal-open');
    }
    
    close() {
        this.modal.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
}

/**
 * Toggle reference number field based on payment method
 */
function toggleReferenceField(paymentMethodSelectId, referenceGroupId, referenceInputId, referenceRequiredId) {
    const paymentMethodSelect = document.getElementById(paymentMethodSelectId);
    const referenceGroup = document.getElementById(referenceGroupId);
    const referenceInput = document.getElementById(referenceInputId);
    const referenceRequired = document.getElementById(referenceRequiredId);
    
    if (!paymentMethodSelect) return;
    
    const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];
    const methodName = selectedOption.getAttribute('data-method-name');
    
    // Methods that require reference numbers
    const requiresReference = ['GCash', 'Bank Transfer', 'Credit Card', 'Debit Card'];
    
    if (requiresReference.includes(methodName)) {
        referenceGroup.style.display = 'block';
        referenceInput.required = true;
        if (referenceRequired) referenceRequired.style.display = 'inline';
    } else {
        referenceGroup.style.display = 'none';
        referenceInput.required = false;
        if (referenceRequired) referenceRequired.style.display = 'none';
        referenceInput.value = '';
    }
}

/**
 * Initialize searchable select dropdown
 */
function initSearchableSelect(config) {
    const {
        searchInputId,
        hiddenInputId,
        dropdownId,
        additionalFieldId = null,
        additionalFieldAttr = null
    } = config;
    
    const searchInput = document.getElementById(searchInputId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const dropdown = document.getElementById(dropdownId);
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
            
            // Handle additional field (e.g., amount)
            if (additionalFieldId && additionalFieldAttr) {
                const additionalValue = this.getAttribute(additionalFieldAttr);
                const additionalField = document.getElementById(additionalFieldId);
                if (additionalField && additionalValue) {
                    additionalField.value = parseFloat(additionalValue).toFixed(2);
                }
            }
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
    
    // Keyboard navigation
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

/**
 * Update payment info message based on status
 */
function updatePaymentInfoMessage(statusSelectId, infoBoxId, iconId, textId) {
    const statusSelect = document.getElementById(statusSelectId);
    const infoBox = document.getElementById(infoBoxId);
    const icon = document.getElementById(iconId);
    const text = document.getElementById(textId);
    
    if (!statusSelect || !infoBox) return;
    
    const status = statusSelect.value;
    
    const messages = {
        'Failed': {
            background: '#fef2f2',
            borderColor: '#ef4444',
            iconClass: 'bi bi-exclamation-triangle',
            iconColor: '#ef4444',
            textColor: '#991b1b',
            message: '<strong>Failed Payment:</strong> This will record/mark a failed payment attempt for tracking purposes. The membership will NOT be activated. Use this to document payment issues that need follow-up.'
        },
        'Pending': {
            background: '#fffbeb',
            borderColor: '#f59e0b',
            iconClass: 'bi bi-clock-history',
            iconColor: '#f59e0b',
            textColor: '#92400e',
            message: '<strong>Pending Payment:</strong> This will record/mark a pending payment awaiting verification. The membership status will remain unchanged until payment is confirmed.'
        },
        'Completed': {
            background: '#f0fdf4',
            borderColor: '#22c55e',
            iconClass: 'bi bi-check-circle',
            iconColor: '#22c55e',
            textColor: '#166534',
            message: '<strong>Completed Payment:</strong> This payment will be recorded/marked as successfully processed. The amount will be linked to the selected membership for tracking and accounting purposes.'
        }
    };
    
    const config = messages[status] || messages['Completed'];
    
    infoBox.style.background = config.background;
    infoBox.style.borderLeftColor = config.borderColor;
    icon.className = config.iconClass;
    icon.style.color = config.iconColor;
    text.style.color = config.textColor;
    text.innerHTML = config.message;
}

/**
 * Fetch payment details from API
 */
async function fetchPaymentDetails(paymentId) {
    try {
        const response = await fetch(`api/get_payment_details.php?id=${paymentId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load payment details');
        }
        
        return data.payment;
    } catch (error) {
        console.error('Error fetching payment details:', error);
        throw error;
    }
}

/**
 * Show loading state in modal
 */
function showLoadingState(loadingId, formId, errorId) {
    document.getElementById(loadingId).style.display = 'block';
    document.getElementById(formId).style.display = 'none';
    document.getElementById(errorId).style.display = 'none';
}

/**
 * Show form state in modal
 */
function showFormState(loadingId, formId, errorId) {
    document.getElementById(loadingId).style.display = 'none';
    document.getElementById(formId).style.display = 'block';
    document.getElementById(errorId).style.display = 'none';
}

/**
 * Show error state in modal
 */
function showErrorState(loadingId, formId, errorId) {
    document.getElementById(loadingId).style.display = 'none';
    document.getElementById(formId).style.display = 'none';
    document.getElementById(errorId).style.display = 'block';
}

/**
 * Handle form submission with loading state
 */
function handleFormSubmit(formId, buttonId, successMessage, endpoint = 'payments.php') {
    const form = document.getElementById(formId);
    const button = document.getElementById(buttonId);
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const originalButtonContent = button.innerHTML;
        
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                if (window.toast) {
                    toast.success(successMessage, 3000);
                }
                setTimeout(() => {
                    window.location.href = response.url;
                }, 1000);
            } else {
                return response.text().then(text => {
                    throw new Error('Operation failed');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.toast) {
                toast.error('Operation failed. Please try again.', 5000);
            }
            button.disabled = false;
            button.innerHTML = originalButtonContent;
        });
    });
}

/**
 * Format date for display
 */
function formatPaymentDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}