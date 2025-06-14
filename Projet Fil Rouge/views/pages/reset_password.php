<div class="reset-password-wrapper">
    <form action="" method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <div class="reset-password">
            <h3>Reset your password</h3>
            <p>Please choose a new password and type it into the form below.</p>
            <?php if (!empty($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php elseif (!empty($success)): ?>
                <p class="success"><?= $success ?></p>
            <?php endif; ?>
            <?php if (empty($success)): ?>
                <div class="input-group">
                    <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" name="set_password">Reset Password</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Popup for success message -->
<?php if (!empty($success)): ?>
    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup" id="successPopup">
        <div class="popup-content">
            <p><?= $success ?></p>
        </div>
    </div>
    <script>
        // Show the popup
        document.getElementById('popupOverlay').style.display = 'block';
        document.getElementById('successPopup').style.display = 'block';
        
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "login.php";
        }, 2000);
    </script>
<?php endif; ?>