<?php
    function getRecentPosts($pdo, $user_id) {
        $recent_posts_query = "SELECT post_id, post_title, post_status, post_published 
        FROM posts WHERE user_id = :user_id 
        ORDER BY post_published DESC LIMIT 2";
        $stmt = $pdo->prepare($recent_posts_query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }