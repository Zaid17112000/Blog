function hideEmptyState() {
    const overlay = document.querySelector('.overlay');
    const emptyStateContainer = document.querySelector('.empty-state-container');
    
    overlay.classList.remove('show');
    emptyStateContainer.classList.remove('show');
    
    // Re-enable scrolling
    document.body.style.overflow = 'auto';
}

// Add click event listener to the document
document.addEventListener('click', function(event) {
    const overlay = document.querySelector('.overlay');
    const emptyStateContainer = document.querySelector('.empty-state-container');
    
    // Only hide if the empty state is currently visible
    if (emptyStateContainer.classList.contains('show') || emptyStateContainer.classList.contains('active')) {
        // Check if the click is NOT inside the empty state container
        if (!emptyStateContainer.contains(event.target)) {
            hideEmptyState();
        }
    }
});