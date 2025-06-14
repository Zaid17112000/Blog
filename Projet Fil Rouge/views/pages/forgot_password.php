<div class="forgot-password-wrapper">
    <form method="post" class="forgot-password-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        
        <div class="form-header">
            <h1 class="form-title">Find your account</h1>
            <p class="form-subtitle">Enter your email address and we'll send you a verification code to reset your password</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <input 
                name="email" 
                type="email" 
                id="email" 
                class="form-input"
                placeholder="Enter your email address"
                required
            >
        </div>
        
        <button name="reset" type="submit" class="form-button">
            Reset Password
        </button>
        
        <div class="form-footer">
            <p class="back-to-login-text">
                Remember your password? 
                <a href="login.php" class="back-to-login-link">Log in</a>
            </p>
        </div>
    </form>
</div>