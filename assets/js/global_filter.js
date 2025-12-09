/**
 * Global Filter and Search JavaScript
 * Handles search functionality and filter dropdown interactions
 */

// Track currently open filter
let currentOpenFilter = null;

/**
 * Toggle filter dropdown visibility
 */
function toggleFilter(filterKey) {
    const dropdown = document.getElementById(`filterDropdown_${filterKey}`);
    
    if (!dropdown) return;
    
    const isCurrentlyOpen = dropdown.classList.contains('show');
    
    // Close all dropdowns first
    closeAllFilters();
    
    // Toggle current dropdown
    if (!isCurrentlyOpen) {
        dropdown.classList.add('show');
        currentOpenFilter = filterKey;
    } else {
        currentOpenFilter = null;
    }
}

/**
 * Close all filter dropdowns
 */
function closeAllFilters() {
    const allDropdowns = document.querySelectorAll('.filter-dropdown-content');
    allDropdowns.forEach(dropdown => {
        dropdown.classList.remove('show');
    });
    currentOpenFilter = null;
}

/**
 * Clear filter checkboxes
 */
function clearFilter(filterKey, paramName) {
    const form = document.querySelector(`#filterDropdown_${filterKey} form`);
    if (!form) return;
    
    // Uncheck all checkboxes
    const checkboxes = form.querySelectorAll(`input[name="${paramName}[]"]`);
    checkboxes.forEach(cb => cb.checked = false);
    
    // Submit form
    form.submit();
}

/**
 * Handle search input with debouncing
 */
let searchTimeout;
function handleSearch(input) {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        const page = input.dataset.page;
        const param = input.dataset.param;
        const value = input.value.trim();
        
        // Build URL with current parameters
        const url = new URL(window.location.href);
        
        if (value) {
            url.searchParams.set(param, value);
        } else {
            url.searchParams.delete(param);
        }
        
        // Redirect to new URL
        window.location.href = url.toString();
    }, 500); // 500ms delay
}

/**
 * Initialize search and filter functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Search input handler
    const searchInput = document.getElementById('globalSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            handleSearch(this);
        });
        
        // Handle Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                handleSearch(this);
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.filter-dropdown')) {
            closeAllFilters();
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    const filterContents = document.querySelectorAll('.filter-dropdown-content');
    filterContents.forEach(content => {
        content.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Handle filter buttons
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});

/**
 * Reset all filters
 */
function resetAllFilters() {
    const url = new URL(window.location.href);
    const page = url.pathname.split('/').pop();
    
    // Keep only the action parameter if it exists
    const action = url.searchParams.get('action');
    
    url.search = '';
    if (action) {
        url.searchParams.set('action', action);
    }
    
    window.location.href = url.toString();
}

/**
 * Export current filters as URL
 */
function exportFilters() {
    return window.location.href;
}