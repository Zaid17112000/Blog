<?php
    /**
     * Fetches the latest published posts with their categories and content preview
     * 
     * @param PDO $pdo Database connection instance
     * @param int $limit Number of posts to retrieve (default: 3)
     * @return array Array of post data with categories and preview
     */
    function getLatestPosts(PDO $pdo, int $limit = 3): array {
        $sql = "SELECT 
            p.post_id, 
            p.post_title, 
            p.post_img_url, 
            GROUP_CONCAT(DISTINCT c.category_name SEPARATOR ' | ') AS category_name, 
            SUBSTRING_INDEX(p.post_content, '. ', 1) AS content_preview,
            p.post_published
        FROM posts p 
        LEFT JOIN categories_posts cp ON p.post_id = cp.post_id 
        LEFT JOIN categories c ON cp.category_id = c.category_id
        LEFT JOIN users u ON u.user_id = p.user_id
        WHERE p.post_status = 'published'
        GROUP BY p.post_id, p.post_title, p.post_img_url, p.post_content
        ORDER BY p.post_published DESC
        LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }