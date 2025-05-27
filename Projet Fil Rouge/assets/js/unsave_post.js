document.addEventListener('DOMContentLoaded', function () {
    // Delegate click event for unsave buttons
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('unsave-post')) {
            const button = event.target;
            const postId = button.dataset.postId;
            const card = button.closest('.col-md-4');

            fetch('../../php/controllers/unsave_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fade out and remove the card
                    card.style.transition = 'opacity 0.3s ease';
                    card.style.opacity = 0;
                    setTimeout(() => {
                        card.remove();

                        // Check if there are no more posts left
                        if (document.querySelectorAll('.col-md-4').length === 0) {
                            document.querySelector('.row').innerHTML = `
                                <div class="no-posts-container">
                                    <i class="fas fa-bookmark no-posts-icon"></i>
                                    <h3 class="no-posts-title">No Saved Posts</h3>
                                    <p class="no-posts-message">You haven't saved any posts yet. When you find content you like, click the Save Icon to add it to your collection.</p>
                                    <a href="../pages/blogsphere.php" class="no-posts-btn">Explore Posts</a>
                                </div>
                            `;
                        }
                    }, 300);
                    console.log(data);
                    
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('An error occurred: ' + error);
            });
        }
    });
});