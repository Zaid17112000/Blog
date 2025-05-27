const deleteButton = document.querySelectorAll(".delete-button");

deleteButton.forEach(btn => {
    btn.addEventListener("click", async (e) => {
        e.preventDefault();

        const postId = btn.dataset.postId;
        const postStatus = btn.dataset.postStatus || 'published';
        
        if (!confirm("Are you sure you want to delete this post ?")) {
            return;
        }

        try {
            const response = await fetch("../../php/controllers/delete_post.php", {
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
                btn.closest('.post-card').remove();

                // Check if this was the last post
                const postsGrid = document.querySelector('.posts-grid');
                const postCards = postsGrid.querySelectorAll('.post-card');

                if (postCards.length === 0) {
                    // Show the "no posts" message
                    postsGrid.innerHTML = `
                        <div class="no-posts-message">
                            <p>No posts have been published yet. Start sharing your ideas by creating a new post!</p>
                            <a href="add_blog_ajax_draft.php"><button class="action-button">Create Your First Post</button></a>
                        </div>
                    `;
                    // Update grid layout
                    postsGrid.style.gridTemplateColumns = '1fr';
                }
                alert('Post deleted successfully');
            } else {
                alert('Error deleting post: ' + result.message);
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the post');
        }
    })
})