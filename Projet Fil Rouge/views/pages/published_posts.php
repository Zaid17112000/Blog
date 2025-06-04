<div class="container">
    <div class="page-title">
        <h1>Published Posts</h1>
        <a href="add_blog_ajax_draft.php"><button class="action-button">Create New Post</button></a>
    </div>
    
    <div class="posts-grid" style="<?= $publishedPosts ? 'grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));' : 'grid-template-columns: 1fr;' ?>">
        <?php if ($publishedPosts && count($publishedPosts) > 0) : ?>
            <?php foreach ($publishedPosts as $pps) : ?>
                <div class="post-card" data-post-id="<?= $pps['post_id'] ?>" data-post-status="published">
                    <div class="post-image" style="background-image: url('<?= $pps["post_img_url"] ?>')"></div>
                    <div class="post-content">
                        <h2 class="post-title"><?= $pps["post_title"] ?></h2>
                        <div class="post-meta">Published on <?= $pps["formatted_date"] ?> • 5 min read</div>
                        <p class="post-excerpt"><?= $pps["post_excerpt"] ?></p>
                        <a href="post.php?post_id=<?= $pps['post_id']; ?>" class="read-more">Read More</a>
                        <div class="post-actions">
                            <a href="add_blog_ajax_draft.php?post_id=<?= htmlspecialchars($pps['post_id']) ?>" class="edit-button"><button class="edit-button">Edit</button></a>
                            <button class="delete-button" data-post-id="<?= $pps["post_id"] ?>">Delete</button>
                            <input type="hidden" data-post-status="<?= $pps["post_status"] ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="no-posts-message">
                <p>No posts have been published yet. Start sharing your ideas by creating a new post!</p>
                <a href="add_blog_ajax_draft.php"><button class="action-button">Create Your First Post</button></a>
            </div>
        <?php endif; ?>
    </div>
</div>