function handleSaveEditComment() {
    document.querySelectorAll('.save-edit').forEach(button => {
        button.addEventListener('click', async function() {
            const commentId = this.dataset.commentId;
            const commentElement = document.getElementById(`comment-${commentId}`);
            const editTextarea = commentElement.querySelector('.edit-textarea');
            const commentContent = commentElement.querySelector('.comment-content');
            const editArea = commentElement.querySelector('.edit-area');
            const newContent = editTextarea.value.trim();

            if (!newContent) {
                alert('Comment cannot be empty');
                return;
            }

            try {
                const response = await fetch('../controllers/edit_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `comment_id=${encodeURIComponent(commentId)}&comment_content=${encodeURIComponent(newContent)}`
                });
                const result = await response.json();

                if (result.success) {
                    commentContent.textContent = escapeHTML(newContent);
                    commentContent.style.display = 'block';
                    editArea.style.display = 'none';
                } else {
                    alert('Failed to update comment: ' + result.error);
                }
            } catch (error) {
                alert('An error occurred while updating the comment: ' + error.message);
            }
        });
    });
}