<div class="container">
    <div class="page-title">
        <h1>Draft Posts</h1>
        <a href="add_blog_ajax_draft.php"><button class="action-button">Create New Daft</button></a>
    </div>
    
    <div class="drafts-list">
        <?php if ($draftPosts && count($draftPosts) > 0) : ?>
            <?php foreach ($draftPosts as $dps) : ?>
                <!-- Draft 1 -->
                <div class="draft-card">
                    <div class="draft-info">
                        <h2 class="draft-title"><?= htmlspecialchars($dps["post_title"]) ?></h2>
                        <div class="draft-meta">
                            <span>Last edited: <?= htmlspecialchars($dps["post_updated"]) ?></span>
                            <span>Created: <?= htmlspecialchars($dps["formatted_date"]) ?></span>
                        </div>
                        <div>
                            <span class="category-label"><?= htmlspecialchars($dps["tag_name"]) ?></span>
                        </div>
                        <div class="completion-status">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                            <span>75% complete</span>
                        </div>
                    </div>
                    <div class="draft-actions">
                        <a href="add_blog_ajax_draft.php?post_id=<?= $dps['post_id'] ?>" class="edit-button"><button class="edit-button">Edit</button></a>
                        <button class="publish-button" data-post-id="<?= $dps["post_id"] ?>">Publish</button>
                        <button class="delete-button" data-post-id="<?= $dps["post_id"] ?>">Delete</button>
                        <input type="hidden" data-post-status="<?= $dps["post_status"] ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="no-drafts-message">
                <p>No draft posts yet. Start writing your next masterpiece!</p>
                <a href="add_blog_ajax_draft.php">
                    <button class="action-button">Create Your First Draft</button>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>