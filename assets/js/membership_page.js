// Toggle filter dropdown
function toggleFilter() {
    document.getElementById('filterDropdown').classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const filterBtn = document.querySelector('.filter-btn');
    const dropdown = document.getElementById('filterDropdown');
    if (filterBtn && dropdown) {
    if (!filterBtn.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
        }
    }
});

// Search on Enter
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('membershipSearchInput');
    if (!searchInput) return;

    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            window.location.href =
                'memberships.php?action=list&search=' + encodeURIComponent(this.value);
        }
    });
});