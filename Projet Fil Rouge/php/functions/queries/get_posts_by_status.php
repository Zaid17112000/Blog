<?php
    // function getPostsByStatus($pdo, $post_status, $user_id) {
    //     $queryPublishedPosts = "SELECT post_id, post_title, post_excerpt, post_img_url, post_created, DATE_FORMAT(post_published, '%M %e, %Y') AS formatted_date FROM posts WHERE post_status = :post_status AND user_id = :user_id";
    //     $stmt = $pdo->prepare($queryPublishedPosts);
    //     $stmt->execute(['post_status' => $post_status, 'user_id' => $user_id]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    function getPostsByStatus($pdo, $post_status, $user_id) {
        $queryPublishedPosts = "SELECT 
            p.post_id, 
            p.post_title, 
            p.post_excerpt, 
            p.post_img_url, 
            p.post_created, 
            p.post_updated, 
            p.post_status, 
            DATE_FORMAT(p.post_created, '%M %e, %Y') AS formatted_date,
            t.tag_id,
            t.tag_name
        FROM posts p
        LEFT JOIN tags_posts tp ON p.post_id = tp.post_id
        LEFT JOIN tags t ON tp.tag_id = t.tag_id
        WHERE p.post_status = :post_status AND p.user_id = :user_id";
        
        $stmt = $pdo->prepare($queryPublishedPosts);
        $stmt->execute(['post_status' => $post_status, 'user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }