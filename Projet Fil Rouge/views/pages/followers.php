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
                <a href="followers.php" class="nav-link active">
                    <i class="fa-solid fa-user-plus"></i> Followers
                </a>
            </li>
            <li class="nav-item" style="display: <?= $user["user_role"] !== 'admin' ? 'none' : 'block' ?>">
                <a href="admin_overview.php" class="nav-link">
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
            <h1>Your Followers <span class="followers-count"><?php echo count($followers); ?></span></h1>
        </header>

        <div class="followers-section">
            <?php if (empty($followers)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>You don't have any followers yet.</p>
                </div>
            <?php else: ?>
                <div class="followers-grid">
                    <?php foreach ($followers as $follower): ?>
                        <div class="follower-card" data-user-id="<?= $follower['user_id'] ?>">
                            <div class="follower-avatar">
                                <?php if ($follower['img_profile']): ?>
                                    <img src="<?= htmlspecialchars($follower['img_profile']) ?: 'https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png' ?>" alt="<?= htmlspecialchars($follower['first_name']) ?>">
                                <?php else: ?>
                                    <div class="default-avatar">
                                        <?= strtoupper(substr($follower['first_name'], 0, 1)) . substr($follower['last_name'], 0, 1) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="follower-info">
                                <h4><?= htmlspecialchars($follower['first_name'] . ' ' . $follower['last_name']) ?></h4>
                                <small><?= htmlspecialchars($follower['user_email']) ?></small>
                                <div class="follow-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('M d, Y', strtotime($follower['follow_date'])) ?>
                                </div>
                            </div>
                            <div class="follower-actions">
                                <button class="view-profile-btn" data-user-id="<?= $follower['user_id'] ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>