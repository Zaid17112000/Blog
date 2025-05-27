<main class="container" style="padding-top: 40px;">
    <!-- Categories -->
    <div class="navbar-container">
        <button id="left-arrow" class="arrow">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="categories">
            <?php foreach($categories as $category) : ?>
                <a href="?category=<?= urlencode($category['category_name']) ?>">
                    <div class="category <?= (isset($_GET['category']) && urldecode($_GET['category']) === $category['category_name']) ? 'active' : '' ?>"><?= htmlspecialchars($category["category_name"]) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        <button id="right-arrow" class="arrow">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>

    <?php
        $sql = "SELECT 
            p.post_id, 
            p.post_title, 
            p.post_img_url, 
            p.post_created, 
            p.post_published, 
            p.post_status, 
            p.post_excerpt, 
            CONCAT(u.last_name, ' ', u.first_name) AS username, 
            u.img_profile, 
            GROUP_CONCAT(DISTINCT c.category_name SEPARATOR ' | ') AS category_name 
            -- SUBSTRING_INDEX(p.post_content, '. ', 1) AS content_preview
        FROM posts p 
        LEFT JOIN categories_posts cp ON p.post_id = cp.post_id 
        LEFT JOIN categories c ON cp.category_id = c.category_id
        LEFT JOIN users u ON u.user_id = p.user_id
        WHERE p.post_status = 'published'
        GROUP BY p.post_id, p.post_title, p.post_img_url, p.post_content, p.post_published  -- Needed when using GROUP_CONCAT
        ORDER BY p.post_published DESC
        LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $featured_article = $stmt->fetch();
    ?>

    <!-- Featured Post -->
    <?php if ($featured_article) : ?>
        <article class="featured-article">
            <div class="featured-image">
                <img src="<?= htmlspecialchars($featured_article["post_img_url"]); ?>" alt="Featured post image">
            </div>
            <div class="featured-content">
                <div class="author">
                    <div class="author-image">
                        <img src="<?= !empty($featured_article["img_profile"]) ? htmlspecialchars($featured_article["img_profile"]) : 'https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png'; ?>" alt="Author profile">
                    </div>
                    <div class="author-info">
                        <div class="author-name"><?= htmlspecialchars($featured_article["username"]); ?></div>
                        <div class="post-date"><?= date("M d", strtotime($featured_article["post_created"])); ?></div>
                    </div>
                </div>
                <h3><a href="post.php?post_id=<?= $featured_article['post_id']; ?>"><?= htmlspecialchars($featured_article["post_title"]); ?></a></h3>
                <p><?= htmlspecialchars($featured_article["post_excerpt"]); ?></p>
                <span class="category"><?= htmlspecialchars($featured_article["category_name"]); ?></span>
                <?php
                    // Check if the current user liked the featured post
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE user_id = ? AND post_id = ?");
                    $stmt->execute([$user, $featured_article["post_id"]]);
                    $is_liked = $stmt->fetchColumn() > 0;
                    $like_class = $is_liked ? 'liked' : '';

                    // Check if the current user saved the featured post
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_posts WHERE user_id = ? AND post_id = ?");
                    $stmt->execute([$user, $featured_article["post_id"]]);
                    $is_saved = $stmt->fetchColumn() > 0;
                    $save_class = $is_saved ? 'saved' : '';

                    // Fetch like count for the featured post
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE post_id = ?");
                    $stmt->execute([$featured_article["post_id"]]);
                    $like_count = $stmt->fetchColumn();
                ?>
                <div class="article-card__footer">
                    <div class="likes <?= $like_class ?>" data-post-id="<?= $featured_article['post_id']; ?>">
                        <i class="like-svg <?= $is_liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart'; ?>"></i>
                        <span class="like-count"><?= htmlspecialchars($like_count); ?></span>
                    </div>
                    <button class="save <?= $save_class ?>" data-post-id="<?= $featured_article["post_id"] ?>">
                        <?php if ($is_saved) : ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M7.5 3.75a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-14a2 2 0 0 0-2-2z"></path></svg>
                        <?php else : ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M17.5 1.25a.5.5 0 0 1 1 0v2.5H21a.5.5 0 0 1 0 1h-2.5v2.5a.5.5 0 0 1-1 0v-2.5H15a.5.5 0 0 1 0-1h2.5zm-11 4.5a1 1 0 0 1 1-1H11a.5.5 0 0 0 0-1H7.5a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-8.5a.5.5 0 0 0-1 0v7.48l-5.2-4a.5.5 0 0 0-.6 0l-5.2 4z"></path></svg>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </article>
    <?php else : ?>
        <div class="no-posts-message">
            <p>No articles available at the moment.</p>
        </div>
    <?php endif; ?>

    <!-- Post List -->
    <div id="articlesGrid" class="articles-grid" style="margin-top: 40px;">
        <?php foreach($posts as $post) : ?>
            <?php
                // Check if the current user liked this post
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE user_id = ? AND post_id = ?");
                $stmt->execute([$user, $post["post_id"]]);
                $is_liked = $stmt->fetchColumn() > 0;
                $like_class = $is_liked ? 'liked' : '';

                // Check if the current user saved this post
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_posts WHERE user_id = ? AND post_id = ?");
                $stmt->execute([$user, $post["post_id"]]);
                $is_saved = $stmt->fetchColumn() > 0;
                $save_class = $is_saved ? 'saved' : '';
            ?>
            <article class="article-card">
                <div class="article-content">
                    <div class="author">
                        <div class="author-image">
                            <img src="<?= !empty($post["img_profile"]) ? htmlspecialchars($post["img_profile"]) : 'https://miro.medium.com/v2/resize:fill:48:48/1*dmbNkD5D-u45r44go_cf0g.png' ?>" alt="Author profile">
                        </div>
                        <div class="author-info">
                            <div class="author-name"><?= htmlspecialchars($post["username"]); ?></div>
                            <div class="post-date"><?= date("M d", strtotime($post["post_created"])); ?></div>
                        </div>
                    </div>
                    <h3><a href="post.php?post_id=<?= $post['post_id']; ?>"><?= htmlspecialchars($post["post_title"]); ?></a></h3>
                    <p><?= htmlspecialchars($post["post_excerpt"]); ?></p>
                    <span class="category"><?= htmlspecialchars($post["category_name"] ?? "Uncategorized"); ?></span>
                    <div class="article-card__footer">
                        <div class="likes <?= $like_class ?>" data-post-id="<?= $post['post_id']; ?>">
                            <i class="like-svg <?= $is_liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart'; ?>"></i>
                            <span class="like-count"><?= htmlspecialchars($post["like_count"]); ?></span>
                        </div>
                        <button class="save <?= $save_class ?>" data-post-id="<?= $post["post_id"] ?>">
                            <?php if ($is_saved) : ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M7.5 3.75a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-14a2 2 0 0 0-2-2z"></path></svg>
                            <?php else : ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" class="save-svg"><path fill="#000" d="M17.5 1.25a.5.5 0 0 1 1 0v2.5H21a.5.5 0 0 1 0 1h-2.5v2.5a.5.5 0 0 1-1 0v-2.5H15a.5.5 0 0 1 0-1h2.5zm-11 4.5a1 1 0 0 1 1-1H11a.5.5 0 0 0 0-1H7.5a2 2 0 0 0-2 2v14a.5.5 0 0 0 .8.4l5.7-4.4 5.7 4.4a.5.5 0 0 0 .8-.4v-8.5a.5.5 0 0 0-1 0v7.48l-5.2-4a.5.5 0 0 0-.6 0l-5.2 4z"></path></svg>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
                <div class="article-image">
                    <img src="<?= htmlspecialchars($post["post_img_url"]); ?>" alt="Article image">
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>