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
                <a href="settings.php" class="nav-link active">
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
            <h1>Profile Settings</h1>
            <div>
                <span>Member since: <?= date('M d, Y', strtotime($user['user_registered_at'])); ?></span>
            </div>
        </header>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Your profile has been updated successfully!
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $errors['general']; ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h2><?= $user['first_name'] . ' ' . $user['last_name']; ?></h2>
                    <p><?= $user['user_email']; ?></p>
                    <span class="role-badge <?= $user['user_role']; ?>">
                        <?= ucfirst($user['user_role']); ?>
                    </span>
                </div>
            </div>

            <form method="POST" action="../../php/pages/settings.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="profile-form">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                value="<?= isset($errors['first_name']) ? '' : htmlspecialchars($user['first_name']); ?>">
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['first_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                value="<?= htmlspecialchars($user['last_name']); ?>">
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['last_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                value="<?= htmlspecialchars($user['user_email']); ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" class="form-control <?= isset($errors['bio']) ? 'is-invalid' : ''; ?>" rows="4"><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        <span class="bio-counter">0/500</span>
                        <?php if (isset($errors['bio'])): ?>
                            <div class="invalid-feedback"><?= $errors['bio']; ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Tell us a little about yourself (max 500 characters)</small>
                    </div>

                    <div class="password-section">
                        <h3>Change Password</h3>
                        <p class="text-muted">Leave blank if you don't want to change your password</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : ''; ?>">
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['current_password']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : ''; ?>">
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['new_password']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_password']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group full-width" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>