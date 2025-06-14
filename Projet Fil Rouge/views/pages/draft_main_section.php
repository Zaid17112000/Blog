<div class="container">
    <div class="page-title">
        <h1>Draft Posts</h1>
        <a href="add_blog_ajax_draft.php"><button class="action-button">Create New Daft</button></a>
    </div>

    <div class="drafts-list">
        <?php if ($draftPosts && count($draftPosts) > 0) : ?>
            <?php 
            // Group posts by ID first
            $groupedPosts = [];
            foreach ($draftPosts as $dps) {
                $postId = $dps['post_id'];
                if (!isset($groupedPosts[$postId])) {
                    $groupedPosts[$postId] = $dps;
                    $groupedPosts[$postId]['categories'] = [];
                }
                if ($dps['category_id']) {
                    $groupedPosts[$postId]['categories'][] = [
                        'category_id' => $dps['category_id'],
                        'category_name' => $dps['category_name']
                    ];
                }
            }
            ?>
            
            <?php foreach ($groupedPosts as $dps) : ?>
                <div class="draft-card">
                    <div class="draft-info">
                        <h2 class="draft-title"><a href="post.php?post_id=<?= $dps['post_id']; ?>" style="color: #333;"><?= htmlspecialchars($dps["post_title"]); ?></a></h2>
                        <div class="draft-meta">
                            <span>Last edited: <?= htmlspecialchars($dps["post_updated"]) ?></span>
                            <span>Created: <?= htmlspecialchars($dps["formatted_date"]) ?></span>
                        </div>
                        <div>
                            <?php foreach ($dps['categories'] as $category): ?>
                                <span class="category-label"><?= htmlspecialchars($category['category_name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <!-- rest of your card -->
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