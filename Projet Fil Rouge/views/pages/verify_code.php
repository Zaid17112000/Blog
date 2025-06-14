<div class="verification-wrapper">
    <form method="post" class="verification-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        
        <div class="verification-header">
            <div class="verification-icon"></div>
            <h1 class="verification-title">Enter Verification Code</h1>
            <p class="verification-subtitle">We've sent a 6-digit verification code to your email</p>
        </div>

        <div class="code-info">
            <p class="code-info-text">Check your email for the verification code</p>
            <?php if (isset($_SESSION["reset_email"])): ?>
                <p class="code-info-email"><?= htmlspecialchars($_SESSION["reset_email"]) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)): ?>
            <div class="verification-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="verification-group">
            <label for="code" class="verification-label">Verification Code</label>
            <input 
                type="text" 
                name="code" 
                id="code" 
                class="verification-input"
                placeholder="Enter 6-digit code" 
                maxlength="6"
                pattern="[0-9]{6}"
                required
                autocomplete="one-time-code"
            >
        </div>
        
        <button type="submit" name="verify_code" class="verification-button">
            Verify Code
        </button>

        <div class="verification-footer">
            <p class="resend-text">Didn't receive the code?</p>
            <a href="forgot_password.php" class="resend-link">Resend Code</a>
            <br>
            <a href="login.php" class="back-link">← Back to Login</a>
        </div>
    </form>
</div>