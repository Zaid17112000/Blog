<?php
    function getPublishedPosts($pdo, $user_id) {
        $queryPublishedPosts = "SELECT 
            p.post_id, 
            p.post_title, 
            p.post_excerpt, 
            p.post_img_url,
            p.post_created, 
            DATE_FORMAT(post_published, '%M %e, %Y') AS formatted_date,
            COUNT(l.like_id) as like_count, 
            COUNT(c.comment_id) as comment_count 
        FROM posts p
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN comments c ON p.post_id = c.post_id
        WHERE p.post_status = :post_status AND p.user_id = :user_id
        GROUP BY p.post_id
        ORDER BY p.post_published DESC";
        $stmt = $pdo->prepare($queryPublishedPosts);
        $stmt->execute(['post_status' => 'published', 'user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }