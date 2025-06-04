function updatePostCounts(decrement, isDraft = false) {
    if (isDraft) {
        // Update draft posts count
        const draftCountElement = document.querySelector('.draft-posts .draft-count');
        if (draftCountElement) {
            const currentCount = parseInt(draftCountElement.textContent);
            draftCountElement.textContent = Math.max(0, currentCount + decrement);
        }
    } else {
        // Update published posts count
        const publishedCountElement = document.querySelector('.published-posts .draft-count');
        if (publishedCountElement) {
            const currentCount = parseInt(publishedCountElement.textContent);
            publishedCountElement.textContent = Math.max(0, currentCount + decrement);
        }
    }
    
    // Update posts card count (total posts)
    const postsCardCount = document.querySelector('.card.posts .card-body h3');
    if (postsCardCount) {
        const currentCardCount = parseInt(postsCardCount.textContent);
        postsCardCount.textContent = Math.max(0, currentCardCount + decrement);
    }
}