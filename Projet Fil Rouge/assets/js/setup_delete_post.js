function setupDeletePost(cardSelector, listSelector, emptyMessageHtml, deleteEndpoint) {
    const deleteButtons = document.querySelectorAll(".delete-button");
    
    deleteButtons.forEach(btn => {
        btn.addEventListener("click", async (e) => {
            e.preventDefault();

            const postId = btn.dataset.postId;
            const postStatus = btn.dataset.postStatus || (cardSelector === '.draft-card' ? 'draft' : 'published');
            
            if (!confirm("Are you sure you want to delete this post?")) {
                return;
            }

            try {
                const response = await fetch(deleteEndpoint, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${postId}&post_status=${postStatus}`
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const result = await response.json();
        
                if (result.success) {
                    // Remove the post card from the DOM
                    const postCard = btn.closest(cardSelector);
                    postCard.remove();
                    
                    // Check if this was the last post
                    const postsContainer = document.querySelector(listSelector);
                    const remainingPosts = postsContainer.querySelectorAll(cardSelector);
                    
                    if (remainingPosts.length === 0) {
                        // Show the no posts message
                        postsContainer.innerHTML = emptyMessageHtml;
                        
                        // Adjust grid layout if needed
                        if (listSelector === '.posts-grid') {
                            postsContainer.style.gridTemplateColumns = '1fr';
                        }
                    }
                    
                    if (cardSelector === '.post-card') {
                        alert('Post deleted successfully');
                    }
                } else {
                    alert('Error deleting post: ' + result.message);
                }                    
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while deleting the post');
            }
        });
    });
}