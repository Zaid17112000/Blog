<section class="hero">
    <div class="container">
        <h1>Share Your Thoughts With The World</h1>
        <p>Join our community of writers and readers to explore ideas, share knowledge, and connect with like-minded individuals.</p>
        <a href="add_blog_ajax_draft.php" class="cta-button">Start Writing</a>
    </div>
</section>

<section class="recent-articles">
    <div class="container">
        <h2 class="section-title">Recent Articles</h2>
        <div class="articles-grid">
            <?php foreach ($posts as $post) : ?>
                <div class="article-card">
                    <div class="article-image">
                        <img src="<?= $post["post_img_url"] ?>" alt="Article image">
                    </div>
                    <div class="article-content">
                        <span class="category"><?= htmlspecialchars($post["category_name"]) ?></span>
                        <h3><a href="post.php?post_id=<?= $post['post_id']; ?>" style="color: #333;"><?= htmlspecialchars($post["post_title"]); ?></a></h3>
                        <p><?= $post["content_preview"] ?></p>
                        <a href="post.php?post_id=<?= $post['post_id']; ?>" class="read-more">Read More</a>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- Article 1
            <div class="article-card">
                <div class="article-image">
                    <img src="https://th.bing.com/th/id/OIP.vmuf2q-BmusGPlKlgH3O7AHaEE?pid=ImgDet&w=178&h=97&c=7&dpr=1.5" alt="Article image">
                </div>
                <div class="article-content">
                    <span class="category">Health</span>
                    <h3>The Science Behind Effective Meditation Techniques</h3>
                    <p>Recent studies have shown that regular meditation can significantly reduce stress and improve overall mental health...</p>
                    <a href="#" class="read-more">Read More</a>
                </div>
            </div> -->
            
            <!-- Article 2
            <div class="article-card">
                <div class="article-image">
                    <img src="https://th.bing.com/th/id/OIP.keC9wlFvi67tL0aNaUaenwHaEO?pid=ImgDet&w=178&h=101&c=7&dpr=1.5" alt="Article image">
                </div>
                <div class="article-content">
                    <span class="category">Business</span>
                    <h3>Remote Work Revolution: Building Effective Teams</h3>
                    <p>As companies embrace remote work permanently, new strategies are emerging for building cohesive and productive teams...</p>
                    <a href="#" class="read-more">Read More</a>
                </div>
            </div> -->
            
            <!-- Article 3
            <div class="article-card">
                <div class="article-image">
                    <img src="https://th.bing.com/th/id/OIP.wLqpqaph-eKhj0-2tpbADAHaEK?w=1280&h=720&rs=1&pid=ImgDetMain" alt="Article image">
                </div>
                <div class="article-content">
                    <span class="category">Travel</span>
                    <h3>Hidden Gems: Unexplored Destinations for 2025</h3>
                    <p>Beyond the typical tourist hotspots, these lesser-known destinations offer authentic experiences without the crowds...</p>
                    <a href="#" class="read-more">Read More</a>
                </div>
            </div> -->
        </div>
    </div>
</section>