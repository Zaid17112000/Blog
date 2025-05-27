function updateArticlesGrid(posts, searchTerm) {
    const articlesGrid = document.getElementById('articlesGrid');
    
    if (!posts || posts.length === 0) {
        articlesGrid.innerHTML = `<div class="no-results">No posts found matching "${searchTerm}"</div>`;
        return;
    }
    
    let html = '';
    
    posts.forEach(post => {
        const isLiked = post.is_liked || false; // Make sure your API returns this
        const likeClass = isLiked ? 'liked' : '';
        const iconClass = isLiked ? 'fa-solid' : 'fa-regular';

        console.log(`${post.post_created}`);
        
        html += `
            <article class="article-card">
                <div class="article-content">
                    <div class="author">
                        <div class="author-image">
                            <img src="${post.img_profile && post.img_profile !== '' ? escapeHtml(post.img_profile) : 'https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png'}">
                        </div>
                        <div class="author-info">
                            <div class="author-name">${escapeHtml(post.username)}</div>
                            <div class="post-date">${formatDate(post.post_created)}</div>
                        </div>
                    </div>
                    
                    <h3><a href="post.php?post_id=${post.post_id}">${escapeHtml(post.post_title)}</a></h3>

                    <p>${escapeHtml(post.post_excerpt)}</p>

                    <span class="category">${escapeHtml(post.category_name || "Uncategorized")}</span>
                    
                    <div class="article-card__footer">
                        <div class="likes ${likeClass}" data-post-id="${post.post_id}">
                            <i class="like-svg ${post['like_icon_class']} fa-heart"></i>
                            <span class="like-count">${post.like_count}</span>
                        </div>
                        
                        <button class="save ${post['save_class']}" data-post-id="${post.post_id}">
                            ${post['save_class'] === 'saved' ? 
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M7.5 3.75a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-14a2 2 0 0 0-2-2z"></path></svg>' : 
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M17.5 1.25a.5.5 0 0 1 1 0v2.5H21a.5.5 0 0 1 0 1h-2.5v2.5a.5.5 0 0 1-1 0v-2.5H15a.5.5 0 0 1 0-1h2.5zm-11 4.5a1 1 0 0 1 1-1H11a.5.5 0 0 0 0-1H7.5a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-8.5a.5.5 0 0 0-1 0v7.48l-5.2-4a.5.5 0 0 0-.6 0l-5.2 4z"></path></svg>'}
                        </button>
                    </div>
                </div>
                <div class="article-image">
                    <img src="${escapeHtml(post.post_img_url)}" alt="Article image">
                </div>
            </article>
        `;
    });
    
    articlesGrid.innerHTML = html;

    attachSaveEventListeners();
    attachLikeEventListeners();
}

function attachLikeEventListeners() {
    document.querySelectorAll('.likes').forEach(likeBtn => {
        likeBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const heartIcon = this.querySelector('.like-svg');
            const likeCount = this.querySelector('.like-count');
            
            // Toggle visual state immediately for better UX
            const isLiked = this.classList.contains('liked');
            this.classList.toggle('liked');
            heartIcon.classList.toggle('fa-regular');
            heartIcon.classList.toggle('fa-solid');
            
            if (isLiked) {
                likeCount.textContent = parseInt(likeCount.textContent) - 1;
            } else {
                likeCount.textContent = parseInt(likeCount.textContent) + 1;
            }
            
            // Send the like/unlike request
            // handleLikePost(postId, !isLiked);
        });
    });
}

function attachSaveEventListeners() {
    document.querySelectorAll('.save').forEach(saveBtn => {
        saveBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            
            // Toggle visual state immediately for better UX
            const isSaved = this.classList.contains('saved');
            this.classList.toggle('saved');
            
            // Update the icon
            if (this.classList.contains('saved')) {
                this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M7.5 3.75a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-14a2 2 0 0 0-2-2z"></path></svg>`;
            } else {
                this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M17.5 1.25a.5.5 0 0 1 1 0v2.5H21a.5.5 0 0 1 0 1h-2.5v2.5a.5.5 0 0 1-1 0v-2.5H15a.5.5 0 0 1 0-1h2.5zm-11 4.5a1 1 0 0 1 1-1H11a.5.5 0 0 0 0-1H7.5a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-8.5a.5.5 0 0 0-1 0v7.48l-5.2-4a.5.5 0 0 0-.6 0l-5.2 4z"></path></svg>`;
            }
            
            // Send the save/unsave request
            handleSavePost(postId, !isSaved);
        });
    });
}