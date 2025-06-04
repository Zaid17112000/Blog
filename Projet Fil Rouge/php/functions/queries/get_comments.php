<?php
    function getComments($pdo, $user_id, $post_id) {
        $queryGetComments = "SELECT 
            c.comment_id, 
            c.user_id, 
            c.comment_content, 
            c.comment_created, 
            CONCAT(u.first_name, ' ', u.last_name) AS user_name, 
            c.parent_comment_id, 
            (SELECT COUNT(*) FROM comments r WHERE r.parent_comment_id = c.comment_id) AS reply_count,
            (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id) AS like_count,
            (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id AND cl.user_id = :user_id) AS user_liked
            FROM comments c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE c.post_id = :post_id 
            ORDER BY c.comment_created DESC";
        $stmt = $pdo->prepare($queryGetComments);
        $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }