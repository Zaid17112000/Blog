function handleCommentLike() {
    document.querySelectorAll('.likes-container .like-button').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const commentId = this.getAttribute('data-comment-id');
            
            if (!commentId) {
                console.error('Invalid comment ID');
                return;
            }

            const container = this.closest('.likes-container');
            const icon = this.querySelector('i'); // Assuming you're using Font Awesome icons
            const isCurrentlyLiked = icon.classList.contains('fa-solid');
            const action = isCurrentlyLiked ? 'unlike' : 'like';
            
            try {
                const response = await fetch('../functions/actions/handle_comment_like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `comment_id=${commentId}&action=${action}`
                });

                const result = await response.json();

                if (result.success) {
                    // Update like count
                    const likeCountElement = container.querySelector('.like-count');
                    if (likeCountElement) {
                        likeCountElement.textContent = result.new_like_count;
                    }
                    
                    // Update icon state
                    if (icon) {
                        icon.classList.toggle('fa-solid', result.is_liked);
                        icon.classList.toggle('fa-regular', !result.is_liked);
                    }
                    
                    // Update container state if needed
                    container.classList.toggle('liked', result.is_liked);
                } else {
                    console.error('Like failed:', result.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
}

// Make sure to call this function after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    handleCommentLike();
});

function handleDeleteComment(commentElement) {
    const deleteButton = commentElement.querySelector('.delete-comment');
    if (deleteButton) {
        deleteButton.addEventListener('click', async function() {
            const commentId = this.dataset.commentId;
            const commentElement = document.getElementById(`comment-${commentId}`);
            if (confirm('Are you sure you want to delete this comment and all its replies?')) {
                try {
                    const response = await fetch('../functions/actions/delete_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `comment_id=${encodeURIComponent(commentId)}`
                    });
                    const result = await response.json();
                    if (result.success) {
                        commentElement.remove();
                        if (!commentElement.closest('.replies-container')) {
                            responseCount.textContent = Math.max(0, parseInt(responseCount.textContent) - 1);
                        } else {
                            const parentComment = commentElement.closest('.comment');
                            const replyCountElement = parentComment.querySelector('.reply-count-toggle');
                            if (replyCountElement) {
                                const currentCount = parseInt(replyCountElement.textContent) || 0;
                                replyCountElement.textContent = `${Math.max(0, currentCount - 1)} replies`;
                            }
                        }
                    } else {
                        alert('Failed to delete comment: ' + result.error);
                    }
                } catch (error) {
                    alert('An error occurred: ' + error.message);
                }
            }
        });
    }
}

function handleSubmitReply(post_id, commentElement) {
    const replySubmitButton = commentElement.querySelector('.submit-button');
    if (replySubmitButton) {
        replySubmitButton.addEventListener('click', async function() {
            const replyArea = this.closest('.reply-area');
            const textarea = replyArea.querySelector('.reply-textarea');
            const commentId = this.dataset.commentId;
            const commentContent = textarea.value.trim();

            if (!commentContent) {
                alert('Reply cannot be empty');
                return;
            }

            const formData = new FormData();
            formData.append('post_id', post_id);
            formData.append('comment', commentContent);
            formData.append('parent_comment_id', commentId);

            try {
                const response = await fetch('../functions/actions/submit_comment.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const replyDiv = document.createElement('div');
                    replyDiv.classList.add('comment');
                    replyDiv.id = `comment-${result.comment_id}`;
                    replyDiv.innerHTML = `
                        <div style="margin-bottom: 12px; display: flex; align-items: center; position: relative;">
                            <img alt="${escapeHTML(result.user_name)}" style="border-radius: 50%; background-color: #F2F2F2;" 
                                src="https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png" width="32" height="32" loading="lazy">
                            <div style="margin-left: 12px;">
                                <span style="font-weight: 400; font-size: 14px;">${escapeHTML(result.user_name)}</span>
                                <div style="font-size: 12px; color: #6B6B6B;">${escapeHTML(result.created_at)}</div>
                            </div>
                            <div class="settings-comment">
                                <button style="border: none; fill: #6B6B6B; padding: 8px 2px; margin: 0; cursor: pointer; background: transparent;">
                                    <svg style="color: #6B6B6B; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path fill="currentColor" fill-rule="evenodd" d="M4.385 12c0 .55.2 1.02.59 1.41.39.4.86.59 1.41.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.02.2-1.41.59-.4.39-.59.86-.59 1.41m5.62 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.42.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.03.2-1.42.59s-.58.86-.58 1.41m5.6 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.43.59s1.03-.2 1.42-.59.58-.86.58-1.41-.2-1.02-.58-1.41a1.93 1.93 0 0 0-1.42-.59c-.56 0-1.04.2-1.43.59s-.58.86-.58 1.41" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="settings" style="display: none; position: absolute; right: 0; top: 40px; z-index: 10;">
                                <div style="background: #fff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                    <ul style="padding: 8px 0; margin: 0; list-style: none;">
                                        <li style="font-size: 14px; padding: 8px 20px;">
                                            <button class="edit-comment" data-comment-id="${result.comment_id}">Edit response</button>
                                        </li>
                                        <li style="font-size: 14px; padding: 8px 20px;">
                                            <button class="delete-comment" data-comment-id="${result.comment_id}">Delete response</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="comment-content" style="font-size: 14px; line-height: 20px;">${escapeHTML(result.comment)}</p>
                        <div class="edit-area" style="display: none; margin-top: 10px;">
                            <textarea class="edit-textarea" style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #E0E0E0; border-radius: 4px; resize: vertical; font-size: 14px;">${escapeHTML(result.comment)}</textarea>
                            <div style="display: flex; justify-content: flex-end; margin-top: 8px; gap: 10px;">
                                <button class="cancel-edit btn" style="background: transparent; border: none; color: #6B6B6B; cursor: pointer;">Cancel</button>
                                <button class="save-edit btn" data-comment-id="${result.comment_id}" style="background: #191919; color: #fff; border: none; padding: 5px 12px; border-radius: 99em; cursor: pointer;">Save</button>
                            </div>
                        </div>
                        <div class="social-interaction-bar">
                            <div class="interaction-buttons">
                                <div class="likes-container">
                                    <button class="like-button" data-comment-id="${result.comment_id}">
                                        <i class="like-svg fa-regular fa-heart"></i>
                                    </button>
                                    <span class="like-count">0</span>
                                </div>
                            </div>
                        </div>
                    `;
                    const parentComment = document.getElementById(`comment-${commentId}`);
                    let repliesContainer = parentComment.querySelector('.replies-container');
                    if (!repliesContainer) {
                        repliesContainer = document.createElement('div');
                        repliesContainer.classList.add('replies-container');
                        repliesContainer.style.marginLeft = '40px';
                        repliesContainer.style.marginTop = '20px';
                        parentComment.appendChild(repliesContainer);
                    }
                    repliesContainer.prepend(replyDiv);
                    repliesContainer.style.display = 'block';
                    const replyCountElement = parentComment.querySelector('.reply-count-toggle');
                    if (replyCountElement) {
                        const currentCount = parseInt(replyCountElement.textContent) || 0;
                        replyCountElement.textContent = `${currentCount + 1} replies`;
                    }
                    textarea.value = '';
                    replyArea.style.display = 'none';
                    this.disabled = true;
                    addCommentEventListeners(replyDiv); // Attach listeners to the new reply
                } else {
                    alert("Failed to submit reply: " + result.error);
                }
            } catch (error) {
                alert("An error occurred: " + error.message);
            }
        });
    }
}

function handleRepliesCount(commentElement) {
    const replyCountToggle = commentElement.querySelector('.reply-count-toggle');
    if (replyCountToggle && !replyCountToggle.hasAttribute('data-listener-attached')) {
        replyCountToggle.setAttribute('data-listener-attached', 'true');
        replyCountToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const commentContainer = this.closest('.comment');
            const repliesContainer = commentContainer.querySelector('.replies-container');
            const replyCount = parseInt(this.textContent) || 0;

            if (repliesContainer) {
                if (replyCount === 0) {
                    alert('No replies yet.');
                } else {
                    repliesContainer.style.display = repliesContainer.style.display === 'none' ? 'block' : 'none';
                }
            } else {
                console.error('Replies container not found for comment:', commentContainer);
            }
        });
    }
}

function handlePostLikes() {
    document.querySelectorAll('.article-card__footer .like-button').forEach(button => {
        button.addEventListener('click', async function() {
            const container = this.closest('.likes-container');
            const postId = container.dataset.postId;
            const likeCountElement = container.querySelector('.like-count');
            const isLiked = container.classList.contains('liked');

            if (!postId) {
                console.error('Post ID not found');
                alert('Error: Post ID not found');
                return;
            }

            try {
                const response = await fetch('../functions/actions/handle_like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${encodeURIComponent(postId)}&action=${isLiked ? 'unlike' : 'like'}`
                });

                const result = await response.json();

                if (result.success) {
                    container.classList.toggle('liked');
                    likeCountElement.textContent = result.new_like_count;

                    // Update the icon
                    const icon = this.querySelector('.like-svg');
                    icon.classList.toggle('fa-solid', result.is_liked);
                    icon.classList.toggle('fa-regular', !result.is_liked);
                } else {
                    console.error('Like failed:', result.error);
                    alert('Failed to update like: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the like: ' + error.message);
            }
        });
    });
}

function toggleMainCommentRespondButton() {
    if (textareaComment && respondComment) {
        respondComment.addEventListener("click", () => {
            textareaComment.classList.remove("display__control-comments");
            document.querySelector(".control-comments").style.display = "none";
        })
        textareaComment.addEventListener("input", () => {
            respondComment.style.opacity = textareaComment.value.trim() === "" ? "0.1" : "1";
            respondComment.style.cursor = textareaComment.value.trim() === "" ? "not-allowed" : "pointer";
            respondComment.disabled = textareaComment.value.trim() === "";
        });

        textareaComment.addEventListener("click", () => {
            controlComments.style.display = "block";
        });
    }
}

function toggleMainCommentCancelButton() {
    if (cancelComment) {
        cancelComment.addEventListener("click", (e) => {
            e.preventDefault();
            textareaComment.classList.remove("display__control-comments");
            controlComments.style.display = "none";
            textareaComment.value = "";
            respondComment.style.opacity = "0.5";
            respondComment.disabled = true;
        });
    }
}

function toggleSettingsMenu() {
    // Settings menu toggle
    document.querySelectorAll('.settings-comment button').forEach(button => {
        button.addEventListener('click', () => {
            const settingsMenu = button.closest('.settings-comment').nextElementSibling;
            settingsMenu.style.display = settingsMenu.style.display === 'none' ? 'block' : 'none';
        });
    });

    // Close settings menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.settings-comment') && !e.target.closest('.settings')) {
            document.querySelectorAll('.settings').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
}

function handleEditComment() {
    document.querySelectorAll('.edit-comment').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.dataset.commentId;
            const commentElement = document.getElementById(`comment-${commentId}`);
            const commentContent = commentElement.querySelector('.comment-content');
            const editArea = commentElement.querySelector('.edit-area');
            const settingsMenu = this.closest('.settings');

            commentContent.style.display = 'none';
            editArea.style.display = 'block';
            settingsMenu.style.display = 'none';
        });
    });
}

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
                const response = await fetch('../functions/actions/edit_comment.php', {
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

function handleCancelEditComment() {
    document.querySelectorAll('.cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            const commentElement = this.closest('.comment');
            const commentContent = commentElement.querySelector('.comment-content');
            const editArea = commentElement.querySelector('.edit-area');
            const editTextarea = editArea.querySelector('.edit-textarea');

            editTextarea.value = commentContent.textContent;
            commentContent.style.display = 'block';
            editArea.style.display = 'none';
        });
    });
}

function handleCancelReplyComment() {
    document.querySelectorAll('.reply-area .cancel').forEach(button => {
        button.addEventListener('click', function() {
            const replyArea = this.closest('.reply-area');
            replyArea.style.display = 'none';
            replyArea.querySelector('.reply-textarea').value = '';
            replyArea.querySelector('.submit-button').disabled = true;
        });
    });
}

function handleSubmitComment() {
    if (commentForm) {
        commentForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(commentForm);
            try {
                const response = await fetch("../functions/actions/submit_comment.php", {
                    method: "POST",
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    const commentDiv = document.createElement("div");
                    commentDiv.classList.add("comment");
                    commentDiv.id = `comment-${result.comment_id}`;
                    commentDiv.innerHTML = `
                        <div style="margin-bottom: 12px; display: flex; align-items: center; position: relative;">
                            <img alt="${escapeHTML(result.user_name)}" style="border-radius: 50%; background-color: #F2F2F2;" 
                                src="https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png" width="32" height="32" loading="lazy">
                            <div style="margin-left: 12px;">
                                <span style="font-weight: 400; font-size: 14px;">${escapeHTML(result.user_name)}</span>
                                <div style="font-size: 12px; color: #6B6B6B;">${escapeHTML(result.created_at)}</div>
                            </div>
                            <div class="settings-comment">
                                <button style="border: none; fill: #6B6B6B; padding: 8px 2px; margin: 0; cursor: pointer; background: transparent;">
                                    <svg style="color: #6B6B6B; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path fill="currentColor" fill-rule="evenodd" d="M4.385 12c0 .55.2 1.02.59 1.41.39.4.86.59 1.41.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.02.2-1.41.59-.4.39-.59.86-.59 1.41m5.62 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.42.59s1.02-.2 1.41-.59c.4-.39.59-.86.59-1.41s-.2-1.02-.59-1.41a1.93 1.93 0 0 0-1.41-.59c-.55 0-1.03.2-1.42.59s-.58.86-.58 1.41m5.6 0c0 .55.2 1.02.58 1.41.4.4.87.59 1.43.59s1.03-.2 1.42-.59.58-.86.58-1.41-.2-1.02-.58-1.41a1.93 1.93 0 0 0-1.42-.59c-.56 0-1.04.2-1.43.59s-.58.86-.58 1.41" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="settings" style="display: none; position: absolute; right: 0; top: 40px; z-index: 10;">
                                <div style="background: #fff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                    <ul style="padding: 8px 0; margin: 0; list-style: none;">
                                        <li style="font-size: 14px; padding: 8px 20px;">
                                            <button class="edit-comment" data-comment-id="${result.comment_id}">Edit response</button>
                                        </li>
                                        <li style="font-size: 14px; padding: 8px 20px;">
                                            <button class="delete-comment" data-comment-id="${result.comment_id}">Delete response</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="comment-content" style="font-size: 14px; line-height: 20px;">${escapeHTML(result.comment)}</p>
                        <div class="edit-area" style="display: none; margin-top: 10px;">
                            <textarea class="edit-textarea" style="width: 100%; min-height: 100px; padding: 12px; border: 1px solid #E0E0E0; border-radius: 4px; resize: vertical; font-size: 14px;">${escapeHTML(result.comment)}</textarea>
                            <div style="display: flex; justify-content: flex-end; margin-top: 8px; gap: 10px;">
                                <button class="cancel-edit btn" style="background: transparent; border: none; color: #6B6B6B; cursor: pointer;">Cancel</button>
                                <button class="save-edit btn" data-comment-id="${result.comment_id}" style="background: #191919; color: #fff; border: none; padding: 5px 12px; border-radius: 99em; cursor: pointer;">Save</button>
                            </div>
                        </div>
                        <div style="margin-top: 14px; display: flex; align-items: center; flex-direction: row; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 24px;">
                                <div class="likes-container">
                                    <button class="like-button" data-comment-id="${result.comment_id}">
                                        <i class="like-svg fa-regular fa-heart"></i>
                                    </button>
                                    <span class="like-count">0</span>
                                </div>
                                <button class="comments-card">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-label="responses">
                                        <path d="M18.006 16.803c1.533-1.456 2.234-3.325 2.234-5.321C20.24 7.357 16.709 4 12.191 4S4 7.357 4 11.482c0 4.126 3.674 7.482 8.191 7.482.817 0 1.622-.111 2.393-.327.231.2.48.391.744.559 1.06.693 2.203 1.044 3.399 1.044.224-.008.4-.112.486-.287a.49.49 0 0 0-.042-.518c-.495-.67-.845-1.364-1.04-2.057a4 4 0 0 1-.125-.598zm-3.122 1.055-.067-.223-.315.096a8 8 0 0 1-2.311.338c-4.023 0-7.292-2.955-7.292-6.587 0-3.633 3.269-6.588 7.292-6.588 4.014 0 7.112 2.958 7.112 6.593 0 1.794-.608 3.469-2.027 4.72l-.195.168v.255c0 .056 0 .151.016.295.025.231.081.478.154.733.154.558.398 1.117.722 1.659a5.3 5.3 0 0 1-2.165-.845c-.276-.176-.714-.383-.941-.59z"></path>
                                    </svg>
                                    <p class="reply-count-toggle">0 replies</p>
                                </button>
                                <p class="reply">
                                    <button class="reply-toggle">Reply</button>
                                </p>
                            </div>
                        </div>
                        <div class="textarea-container reply-area" style="display: none;">
                            <textarea class="reply-textarea" placeholder="Write your reply..."></textarea>
                            <div class="textarea-footer">
                                <button class="cancel btn">Cancel</button>
                                <button class="submit-button btn" data-comment-id="${result.comment_id}" disabled>Respond</button>
                            </div>
                        </div>
                    `;
                    responsesSection.prepend(commentDiv);
                    responseCount.textContent = parseInt(responseCount.textContent) + 1;
                    textareaComment.value = "";
                    controlComments.style.display = "none";
                    respondComment.style.opacity = "0.5";
                    respondComment.disabled = true;
                    addCommentEventListeners(commentDiv);
                    // attachReplyToggleListeners();
                } else {
                    alert("Failed to submit comment: " + result.error);
                }
            } catch (error) {
                alert("An error occurred: " + error.message);
            }
        });
    }
}

function handleToggleReplyComment(commentElement) {
    const replyToggle = commentElement.querySelector('.reply-toggle');
    if (replyToggle) {
        replyToggle.addEventListener('click', function() {
            const comment = this.closest('.comment');
            const replyArea = comment.querySelector('.reply-area');
            replyArea.style.display = replyArea.style.display === 'none' ? 'block' : 'none';
            if (replyArea.style.display === 'block') {
                const textarea = replyArea.querySelector('.reply-textarea');
                textarea.focus();
                const submitButton = replyArea.querySelector('.submit-button');
                submitButton.disabled = textarea.value.trim() === '';
            }
        });
    }
}