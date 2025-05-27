<div class="container mt-5">
    <div class="page-title">
        <h1>Saved Posts</h1>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($saved_posts)): ?>
        <!-- <p>No saved posts found.</p> -->
        <div class="no-posts-container">
            <i class="fas fa-bookmark no-posts-icon"></i>
            <h3 class="no-posts-title">No Saved Posts</h3>
            <p class="no-posts-message">You haven't saved any posts yet. When you find content you like, click the Save Icon to add it to your collection.</p>
            <a href="blogsphere.php" class="no-posts-btn">Explore Posts</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($saved_posts as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card post-card">
                        <?php if ($post['post_img_url']): ?>
                            <img src="<?php echo htmlspecialchars($post['post_img_url']); ?>" class="card-img-top" alt="Post Image" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['post_title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($post['post_excerpt'], 0, 100)) . '...'; ?></p>
                            <p class="text-muted">By: <?php echo htmlspecialchars($post['username']); ?> | <?php echo date('F j, Y', strtotime($post['post_created'])); ?></p>
                            <a href="post.php?post_id=<?php echo $post['post_id']; ?>" class="btn btn-primary">View</a>
                            <button class="btn btn-danger unsave-post" data-post-id="<?php echo $post['post_id']; ?>">Unsave</button>
                            <!-- <a href="unsave_post.php?post_id=<?php echo $post['post_id']; ?>" class="btn btn-danger">Unsave</a> -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>