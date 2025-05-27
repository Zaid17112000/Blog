document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', async function(e) {
        const likeIcon = e.target.closest('.like-svg');
        if (!likeIcon) return;
        
        // Prevent default if it's a link or button
        e.preventDefault();
        
        const likesContainer = e.target.closest('.likes');
        const postId = likesContainer.dataset.postId;
        const likeCountSpan = likesContainer.querySelector('.like-count');

        try {
            const response = await fetch('../controllers/like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            });

            const data = await response.json();

            if (data.success) {
                likeCountSpan.textContent = data.likes;
                if (data.action === 'liked') {
                    likesContainer.classList.add('liked');
                    likeIcon.classList.remove('fa-regular');
                    likeIcon.classList.add('fa-solid');
                } else if (data.action === 'unliked') {
                    likesContainer.classList.remove('liked');
                    likeIcon.classList.remove('fa-solid');
                    likeIcon.classList.add('fa-regular');
                }
            } else {
                console.error(data.error);
                alert(data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to like post');
        }
    });
});