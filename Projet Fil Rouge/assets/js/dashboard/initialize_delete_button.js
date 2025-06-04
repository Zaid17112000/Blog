/**
 * Initializes delete buttons with confirmation, AJAX deletion, and UI updates
 * @param {string} buttonSelector - CSS selector for delete buttons (default: '.delete-button')
 * @param {string} itemSelector - CSS selector for the parent item to remove (default: '.activity-item')
 * @param {string} endpoint - URL for the delete endpoint
 * @param {function} onSuccess - Optional callback after successful deletion
 */
function initializeDeleteButtons({
    buttonSelector = '.delete-button',
    itemSelector = '.activity-item',
    endpoint = '../functions/actions/delete_post_dashboard.php',
    onSuccess = null
} = {}) {
    document.querySelectorAll(buttonSelector).forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const postItem = this.closest(itemSelector);
            const postId = postItem.dataset.postId;
            const postStatus = postItem.dataset.postStatus == 'published' ? 'published' : 'draft';
            
            if (!confirm('Are you sure you want to delete this post?')) {
                return;
            }

            // Show loading indicator
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            this.style.pointerEvents = 'none';
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&post_status=${postStatus}`
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to delete post');
                }

                // Remove the post from UI
                postItem.style.opacity = '0';
                setTimeout(() => {
                    postItem.remove();
                    
                    // Update counts
                    updatePostCounts(-1, postStatus === 'draft');
                    
                    // Show success message
                    showToast('Post deleted successfully', 'success');
                    
                    // Call custom success handler if provided
                    if (typeof onSuccess === 'function') {
                        onSuccess(postId, postStatus);
                    }
                }, 300);
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'An error occurred while deleting the post', 'error');
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }
        });
    });
}