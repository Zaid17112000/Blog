const publishButtons = document.querySelectorAll(".publish-button");

publishButtons.forEach(btn => {
    btn.addEventListener("click", async (e) => {
        e.preventDefault();

        const postId = btn.dataset.postId;
        
        if (!confirm("Are you sure you want to publish this post?")) {
            return;
        }

        try {
            const response = await fetch("../../php/functions/actions/publish_draft_post.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const result = await response.json();
    
            if (result.success) {
                btn.closest('.draft-card').remove();
                alert('Post published successfully');
            } else {
                alert('Error publishing post: ' + result.message);
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while publishing the post');
        }
    });
});