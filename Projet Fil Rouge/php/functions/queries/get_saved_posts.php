<?php
    function savedPosts($pdo, $user_id) {
        $stmt = $pdo->prepare("SELECT p.post_id, p.post_title, p.post_excerpt, p.post_img_url, p.post_status, CONCAT_WS(' ', u.first_name, u.last_name) AS username, p.post_created
            FROM posts p
            JOIN saved_posts sp ON p.post_id = sp.post_id
            JOIN users u ON p.user_id = u.user_id
            WHERE sp.user_id = :user_id
            ORDER BY p.post_created DESC
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }