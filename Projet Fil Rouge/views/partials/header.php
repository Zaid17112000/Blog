<?php
    require_once __DIR__ . '/../../php/controllers/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../../php/config/connectDB.php";

    // Check if user is logged in before trying to access session
    $user_id = $userData->user_id;

    if ($user_id) {
        $stmt = $pdo->prepare("SELECT user_id, img_profile FROM users WHERE user_id = :user_id");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $img_profile = $stmt->fetch(PDO::FETCH_ASSOC);
    }
?>

<header>
    <div class="container">
        <nav class="navbar">
            <a href="homepage.php" class="logo-container">
                <!-- <div class="logo5-box"></div> -->
                <div class="logo-card">
                    <div class="logo">
                        <div class="logo-icon">BS</div>
                        <div class="logo-text">BlogSpace</div>
                    </div>
                </div>
                <!-- <div class="logo">
                    Blog<span>Space</span>
                </div> -->
            </a>
            
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <ul class="nav-links">
                <li><a href="blogsphere.php">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
                <li class="abb-blog"><a href="add_blog_ajax_draft.php">Create Blog</a></li>
            </ul>

            <form class="form" method="POST" style="margin: 0; margin-right: 15px;">
                <div class="search-container">
                    <div class="search-icon" id="searchIcon">
                        <!-- &#128269; -->
                        <svg height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg"><title/><path d="M456.69,421.39,362.6,327.3a173.81,173.81,0,0,0,34.84-104.58C397.44,126.38,319.06,48,222.72,48S48,126.38,48,222.72s78.38,174.72,174.72,174.72A173.81,173.81,0,0,0,327.3,362.6l94.09,94.09a25,25,0,0,0,35.3-35.3ZM97.92,222.72a124.8,124.8,0,1,1,124.8,124.8A124.95,124.95,0,0,1,97.92,222.72Z"/></svg>
                    </div>
                    <input type="text" id="searchBar" class="search-bar" name="search-by-title" placeholder="Search..." value="<?= isset($_POST['search-by-title']) ? htmlspecialchars($_POST['search-by-title']) : '' ?>" oninput="handleSearchInput(this.value)">
                    <?php if (isset($_GET['category'])): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category']) ?>">
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="write-button-container">
                <div class="write-button-wrapper">
                    <a href="add_blog_ajax_draft.php">
                        <div class="icon-container">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" aria-label="Write">
                                <path fill="currentColor" d="M14 4a.5.5 0 0 0 0-1zm7 6a.5.5 0 0 0-1 0zm-7-7H4v1h10zM3 4v16h1V4zm1 17h16v-1H4zm17-1V10h-1v10zm-1 1a1 1 0 0 0 1-1h-1zM3 20a1 1 0 0 0 1 1v-1zM4 3a1 1 0 0 0-1 1h1z"></path>
                                <path stroke="currentColor" d="m17.5 4.5-8.458 8.458a.25.25 0 0 0-.06.098l-.824 2.47a.25.25 0 0 0 .316.316l2.47-.823a.25.25 0 0 0 .098-.06L19.5 6.5m-2-2 2.323-2.323a.25.25 0 0 1 .354 0l1.646 1.646a.25.25 0 0 1 0 .354L19.5 6.5m-2-2 2 2"></path>
                            </svg>
                            <div>Write</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="profile-container">
                <img alt="Zayd el Khobzi" class="x cs s co cp ct" src="<?= empty($img_profile['img_profile']) ? 'https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png' : $img_profile['img_profile']; ?>" width="32" height="32" loading="lazy">
            </div>
        </nav>
        <div class="sidebar">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="profile.php" class="menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="7" r="5"></circle>
                    <path d="M20 21v-2a8 8 0 0 0-16 0v2"></path>
                </svg>
                <span>Profile</span>
            </a>
            <a href="saved_posts.php" class="menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>Library</span>
            </a>
            <a href="#" class="menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 20V10M12 20V4M6 20v-6"></path>
                </svg>
                <span>Stats</span>
            </a>
            <a href="published_posts.php" class="menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <line x1="10" y1="9" x2="8" y2="9"></line>
                </svg>
                <span>Published Posts</span>
            </a>
            <a href="draft_posts.php" class="menu-item">
                <i class="fa fa-pencil-alt"></i>
                <span> Draft Posts</span>
            </a>
            <a href="settings.php" class="menu-item">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class=""><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <span>Settings</span>
            </a>
            <div class="divider"></div>
            <button class="sign-out">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Sign out</span>
            </button>
        </div>
    </div>
</header>

<script>
    const logout = document.querySelector(".sign-out");

    logout.addEventListener("click", async () => {
        try {
            const response = await fetch("../../php/functions/actions/logout.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            });

            if (response.redirected) {
                window.location.href = response.url;
            } else if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the post');
        }
    });
</script>