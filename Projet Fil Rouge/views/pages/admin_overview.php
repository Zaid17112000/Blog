<button class="sidebar-toggle" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
</button>
<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="profile-pic">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
            </div>
            <h2><?= $user['first_name'] . ' ' . $user['last_name']; ?></h2>
            <p><?= $user['user_role']; ?></p>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
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
                <a href="followers.php" class="nav-link">
                    <i class="fa-solid fa-user-plus"></i> Followers
                </a>
            </li>
            <li class="nav-item">
                <a href="admin_overview.php" class="nav-link active">
                    <i class="fa-solid fa-briefcase"></i> Admin Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="../functions/actions/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Statistics</h1>
        </header>

        <!-- User Stats Section -->
        <div class="user-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Posts</h3>
                    <p><?= $total_posts; ?></p>
                </div>
                <!-- <div class="stat-card">
                    <h3>Total Followers</h3>
                    <p><?= $total_followers; ?></p>
                </div> -->
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?= $all_users ?></p>
                </div>
            </div>
        </div>

        <header class="header">
            <h1>Most Liked Posts</h1>
        </header>

        <div class="most-liked-posts">
            <?php if (!empty($most_liked_posts)): ?>
                <?php foreach ($most_liked_posts as $post): ?>
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fa-solid fa-heart"></i>
                        </div>
                        <div class="activity-content">
                            <h4><a href="post.php?post_id=<?= $post['post_id']; ?>" style="color: var(--dark-color); text-decoration: none"><?= htmlspecialchars($post["post_title"]); ?></a></h4>
                            <small>
                                <span style="font-size: 1rem;"><?= $post['like_count']; ?> likes</span>
                            </small>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="stat-card">
                    <h3>No liked posts yet</h3>
                </div>
            <?php endif; ?>
        </div>

        <header class="header">
            <h1>Recent Posts</h1>
        </header>

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
                            <h4><a href="post.php?post_id=<?= $post['post_id']; ?>" style="color: var(--dark-color); text-decoration: none"><?= htmlspecialchars($post["post_title"]); ?></a></h4>
                            <small>
                                <?= date('M d, Y', strtotime($post['post_published'])); ?>
                                <span class="activity-status <?= $post['post_status']; ?>">
                                    <?= ucfirst($post['post_status']); ?>
                                </span>
                            </small>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <header class="header">
            <h1>User Management</h1>
        </header>

        <?php if ($user['user_role'] === 'admin'): ?>
            <div class="user-management">
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Profile</th>
                                <th>Posts</th>
                                <th>Likes</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($get_users)): ?>
                                <tr>
                                    <td colspan="8">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($get_users as $u): ?>
                                    <tr data-user-id="<?= $u['user_id'] ?>">
                                        <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                        <td><?= htmlspecialchars($u['user_email']) ?></td>
                                        <td><span class="role-badge <?= strtolower($u['user_role']) ?>"><?= htmlspecialchars($u['user_role']) ?></span></td>
                                        <td>
                                            <a href="profile.php?user_id=<?= $u['user_id'] ?>" style="text-decoration: none">
                                                Show Profile
                                            </a>
                                        </td>
                                        <td><?= $u['post_count'] ?></td>
                                        <td><?= $u['total_likes'] ?></td>
                                        <td><?= date('M d, Y', strtotime($u['user_registered_at'])) ?></td>
                                        <td>
                                            <?php if ($u['user_id'] != $user_id): ?>
                                                <button class="delete-user-btn" data-user-id="<?= $u['user_id'] ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Current user</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php include "pagination.php"; ?>
            </div>
        <?php endif; ?>

        <!-- <footer class="footer">
            <p class="footer-text">&copy; <?= date('Y'); ?> BlogSpace. All rights reserved.</p>
        </footer> -->
    </main>
</div>