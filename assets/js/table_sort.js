/**
 * Table Sorting JavaScript
 * Handles column sorting with 3-state cycle: ASC -> DESC -> DEFAULT
 */

/**
 * Sort table by column
 * @param {string} column - Column name to sort by
 * @param {string} currentPage - Current page (e.g., 'members.php')
 */
function sortTable(column, currentPage = 'members.php') {
    const url = new URL(window.location.href);
    
    // Get current sort parameters
    const currentSortBy = url.searchParams.get('sort_by');
    const currentSortOrder = url.searchParams.get('sort_order');
    
    let newSortOrder;
    
    // 3-state cycle logic
    if (currentSortBy === column) {
        if (currentSortOrder === 'ASC') {
            newSortOrder = 'DESC';
        } else if (currentSortOrder === 'DESC') {
            // Third click: remove sorting (back to default)
            url.searchParams.delete('sort_by');
            url.searchParams.delete('sort_order');
            window.location.href = url.toString();
            return;
        }
    } else {
        // First click on a new column: sort ascending
        newSortOrder = 'ASC';
    }
    
    // Set new sort parameters
    url.searchParams.set('sort_by', column);
    url.searchParams.set('sort_order', newSortOrder);
    
    // Redirect with new sort parameters
    window.location.href = url.toString();
}

/**
 * Initialize sortable table headers
 */
document.addEventListener('DOMContentLoaded', function() {
    const sortableHeaders = document.querySelectorAll('th.sortable');
    
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.style.userSelect = 'none';
        
        // Add click event
        header.addEventListener('click', function() {
            const column = this.dataset.column;
            const page = this.dataset.page || 'members.php';
            sortTable(column, page);
        });
        
        // Add hover effect
        header.addEventListener('mouseenter', function() {
            if (!this.classList.contains('sorted')) {
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
            }
        });
        
        header.addEventListener('mouseleave', function() {
            if (!this.classList.contains('sorted')) {
                this.style.backgroundColor = '';
            }
        });
    });
    
    // Update visual indicators for current sort
    updateSortIndicators();
});

/**
 * Update sort indicators on page load
 */
function updateSortIndicators() {
    const url = new URL(window.location.href);
    const sortBy = url.searchParams.get('sort_by');
    const sortOrder = url.searchParams.get('sort_order');
    
    if (!sortBy || !sortOrder) return;
    
    // Find the sorted column header
    const sortedHeader = document.querySelector(`th.sortable[data-column="${sortBy}"]`);
    
    if (sortedHeader) {
        sortedHeader.classList.add('sorted');
        sortedHeader.classList.add(sortOrder.toLowerCase());
    }
}

/**
 * Clear all sorting
 */
function clearSort() {
    const url = new URL(window.location.href);
    url.searchParams.delete('sort_by');
    url.searchParams.delete('sort_order');
    window.location.href = url.toString();
}