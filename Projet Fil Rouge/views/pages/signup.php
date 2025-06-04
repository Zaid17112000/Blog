<main class="main-content">
    <div class="login-container">
        <div class="login-header">
            <h1>Create Your Account</h1>
            <p>Fill in your details to get started</p>
        </div>

        <form id="signup-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="nom">First Name</label>
                <input type="text" id="nom" name="nom" value="<?php echo isset($validateInputEntry['data']['nom']) ? htmlspecialchars($validateInputEntry['data']['nom']) : ''; ?>">
                <p class="error-message" id="nom-error">
                    <?php echo isset($validateInputEntry['errors']['nom']) ? htmlspecialchars($validateInputEntry['errors']['nom']) : ''; ?>
                </p>
            </div>

            <div class="form-group">
                <label for="prenom">Last Name</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo isset($validateInputEntry['data']['prenom']) ? htmlspecialchars($validateInputEntry['data']['prenom']) : ''; ?>">
                <p class="error-message" id="prenom-error">
                    <?php echo isset($validateInputEntry['errors']['prenom']) ? htmlspecialchars($validateInputEntry['errors']['prenom']) : ''; ?>
                </p>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($validateInputEntry['data']['email']) ? htmlspecialchars($validateInputEntry['data']['email']) : ''; ?>">
                <p class="error-message" id="email-error">
                    <?php echo isset($validateInputEntry['errors']['email']) ? htmlspecialchars($validateInputEntry['errors']['email']) : ''; ?>
                </p>
            </div>

            <div class="form-group bio-field">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Tell us a little about yourself" maxlength="250" style="font-family: 'Merriweather Sans', sans-serif;"><?php echo isset($validateInputEntry['data']['bio']) ? htmlspecialchars($validateInputEntry['data']['bio']) : ''; ?></textarea>
                <span class="bio-counter">0/250</span>
                <p class="error-message" id="bio-error">
                    <?php echo isset($validateInputEntry['errors']['bio']) ? htmlspecialchars($validateInputEntry['errors']['bio']) : ''; ?>
                </p>
                <small class="form-text text-muted">Optional. Maximum 250 characters.</small>
            </div>

            <div class="form-group">
                <label for="profile_image">Profile Picture</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*">
                <p class="error-message" id="profile_image-error">
                    <?php echo isset($validateInputEntry['errors']['profile_image']) ? htmlspecialchars($validateInputEntry['errors']['profile_image']) : ''; ?>
                </p>
                <small class="form-text text-muted">Optional. Max size 2MB. JPG, PNG, or GIF.</small>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-container">
                    <input type="password" id="password" name="password">
                    <button type="button" class="toggle-password" onclick="togglePassword()" style="cursor: pointer;">
                        <svg id="eye-icon" fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.21967 2.21967C1.9534 2.48594 1.9292 2.9026 2.14705 3.19621L2.21967 3.28033L6.25424 7.3149C4.33225 8.66437 2.89577 10.6799 2.29888 13.0644C2.1983 13.4662 2.4425 13.8735 2.84431 13.9741C3.24613 14.0746 3.6534 13.8305 3.75399 13.4286C4.28346 11.3135 5.59112 9.53947 7.33416 8.39452L9.14379 10.2043C8.43628 10.9258 8 11.9143 8 13.0046C8 15.2138 9.79086 17.0046 12 17.0046C13.0904 17.0046 14.0788 16.5683 14.8004 15.8608L20.7197 21.7803C21.0126 22.0732 21.4874 22.0732 21.7803 21.7803C22.0466 21.5141 22.0708 21.0974 21.8529 20.8038L21.7803 20.7197L15.6668 14.6055L15.668 14.604L14.4679 13.4061L11.598 10.5368L11.6 10.536L8.71877 7.65782L8.72 7.656L7.58672 6.52549L3.28033 2.21967C2.98744 1.92678 2.51256 1.92678 2.21967 2.21967ZM10.2041 11.2655L13.7392 14.8006C13.2892 15.2364 12.6759 15.5046 12 15.5046C10.6193 15.5046 9.5 14.3853 9.5 13.0046C9.5 12.3287 9.76824 11.7154 10.2041 11.2655ZM12 5.5C10.9997 5.5 10.0291 5.64807 9.11109 5.925L10.3481 7.16119C10.8839 7.05532 11.4364 7 12 7C15.9231 7 19.3099 9.68026 20.2471 13.4332C20.3475 13.835 20.7546 14.0794 21.1565 13.9791C21.5584 13.8787 21.8028 13.4716 21.7024 13.0697C20.5994 8.65272 16.6155 5.5 12 5.5ZM12.1947 9.00928L15.996 12.81C15.8942 10.7531 14.2472 9.10764 12.1947 9.00928Z" fill="#666"/>
                        </svg>
                    </button>
                </div>
                <p class="error-message" id="password-error">
                    <?php echo isset($validateInputEntry['errors']['password']) ? htmlspecialchars($validateInputEntry['errors']['password']) : ''; ?>
                </p>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password">
                <p class="error-message" id="confirm-password-error">
                    <?php echo isset($validateInputEntry['errors']['confirm_password']) ? htmlspecialchars($validateInputEntry['errors']['confirm_password']) : ''; ?>
                </p>
            </div>

            <button type="submit" class="login-button" name="submit">Sign Up</button>

            <p class="error-message" id="general-error">
                <?php echo isset($validateInputEntry['errors']['general']) ? htmlspecialchars($validateInputEntry['errors']['general']) : ''; ?>
            </p>
        </form>
    </div>
</main>