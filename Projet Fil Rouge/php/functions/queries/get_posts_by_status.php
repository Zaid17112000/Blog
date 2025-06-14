<?php
    function getPostsByStatus($pdo, $post_status, $user_id) {
        $queryPublishedPosts = "SELECT 
            p.post_id, 
            p.post_title, 
            p.post_excerpt, 
            p.post_img_url, 
            p.post_created, 
            p.post_updated, 
            p.post_status, 
            cp.category_id, 
            DATE_FORMAT(p.post_created, '%M %e, %Y') AS formatted_date,
            GROUP_CONCAT(c.category_name SEPARATOR ' | ') AS category_name
        FROM posts p
        LEFT JOIN categories_posts cp ON p.post_id = cp.post_id
        LEFT JOIN categories c ON cp.category_id = c.category_id
        WHERE p.post_status = :post_status AND p.user_id = :user_id
        GROUP BY p.post_id, p.post_title, p.post_excerpt, p.post_img_url, p.post_created, p.post_updated, p.post_status
        ORDER BY p.post_created DESC";
        
        $stmt = $pdo->prepare($queryPublishedPosts);
        $stmt->execute(['post_status' => $post_status, 'user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }