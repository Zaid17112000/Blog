<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="profile-pic">
                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
            </div>
            <h2><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h2>
            <p><?php echo $user['user_role']; ?></p>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="blogsphere.php" class="nav-link">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="../controllers/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Dashboard</h1>
            <div>
                <span>Member since: <?php echo date('M d, Y', strtotime($user['user_registered_at'])); ?></span>
            </div>
        </header>

        <section class="cards">
            <div class="card posts">
                <div class="card-header">
                    <i class="fas fa-file-alt"></i>
                    <h3>Posts</h3>
                </div>
                <div class="card-body">
                    <h3><?php echo $posts_count; ?></h3>
                    <p>Published posts</p>
                </div>
            </div>
            <div class="card comments">
                <div class="card-header">
                    <i class="fas fa-comment"></i>
                    <h3>Comments</h3>
                </div>
                <div class="card-body">
                    <h3><?php echo $comments_count; ?></h3>
                    <p>Comments made</p>
                </div>
            </div>
            <div class="card likes">
                <div class="card-header">
                    <i class="fas fa-heart"></i>
                    <h3>Likes</h3>
                </div>
                <div class="card-body">
                    <h3><?php echo $likes_received_count; ?></h3>
                    <p>Likes received</p>
                </div>
            </div>
            <div class="card saved">
                <div class="card-header">
                    <i class="fas fa-bookmark"></i>
                    <h3>Saved</h3>
                </div>
                <div class="card-body">
                    <h3><?php echo $saved_count; ?></h3>
                    <p>Posts saved</p>
                </div>
            </div>
        </section>

        <div class="content-grid">
            <div class="published-posts draft-posts">
                <div class="activity-header">
                    <h2>Publish Posts <span class="draft-count"><?php echo $posts_count; ?></span></h2>
                    <a href="published_posts.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <ul class="activity-list">
                    <?php if (empty($published_posts)): ?>
                        <li class="activity-item">
                            <div class="activity-content">
                                <p>You don't have any published posts.</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach ($published_posts as $publish): ?>
                            <li class="activity-item" data-post-id="<?= $publish['post_id'] ?>" data-post-status="published">
                                <div class="activity-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo $publish['post_title']; ?></h4>
                                    <small>Last updated: <?php echo date('M d, Y', strtotime($publish['post_updated'])); ?></small>
                                    <div>
                                        <a href="add_blog_ajax_draft.php?post_id=<?= htmlspecialchars($publish['post_id']) ?>" style="color: var(--primary-color); font-size: 12px; text-decoration: none; margin-right: 10px;">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <a href="#" class="delete-button" style="color: var(--danger-color); font-size: 12px; text-decoration: none;">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="draft-posts">
                <div class="activity-header">
                    <h2>Draft Posts <span class="draft-count"><?php echo $drafts_count; ?></span></h2>
                    <a href="draft_posts.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <ul class="activity-list">
                    <?php if (empty($draft_posts)): ?>
                        <li class="activity-item">
                            <div class="activity-content">
                                <p>You don't have any draft posts.</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach ($draft_posts as $draft): ?>
                            <li class="activity-item" data-post-id="<?= $draft['post_id'] ?>" data-post-status="draft">
                                <div class="activity-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo $draft['post_title']; ?></h4>
                                    <small>Last updated: <?php echo date('M d, Y', strtotime($draft['post_updated'])); ?></small>
                                    <div>
                                        <a href="add_blog_ajax_draft.php?post_id=<?= htmlspecialchars($draft['post_id']) ?>" style="color: var(--primary-color); font-size: 12px; text-decoration: none; margin-right: 10px;">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <a href="#" class="delete-button" style="color: var(--danger-color); font-size: 12px; text-decoration: none;">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="recent-activity">
                <div class="activity-header">
                    <h2>Recent Posts</h2>
                    <a href="blogsphere.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <ul class="activity-list">
                    <?php if (empty($recent_posts)): ?>
                        <li class="activity-item">
                            <div class="activity-content">
                                <p>You haven't published any posts yet.</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach ($recent_posts as $post): ?>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo $post['post_title']; ?></h4>
                                    <small>
                                        <?php echo date('M d, Y', strtotime($post['post_published'])); ?>
                                        <span class="activity-status <?php echo $post['post_status']; ?>">
                                            <?php echo ucfirst($post['post_status']); ?>
                                        </span>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                
                <div class="activity-header" style="margin-top: 20px;">
                    <h2>Saved Posts</h2>
                    <a href="saved_posts.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <ul class="activity-list">
                    <?php if (empty($saved_posts)): ?>
                        <li class="activity-item">
                            <div class="activity-content">
                                <p>You haven't saved any posts yet.</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach ($saved_posts as $saved): ?>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-bookmark"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo $saved['post_title']; ?></h4>
                                    <p><?php echo $saved['post_excerpt']; ?></p>
                                    <small>Saved on: <?php echo date('M d, Y', strtotime($saved['saved_at'])); ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <footer class="footer">
            <p class="footer-text">&copy; <?php echo date('Y'); ?> BlogSpace. All rights reserved.</p>
        </footer>
    </main>
</div>