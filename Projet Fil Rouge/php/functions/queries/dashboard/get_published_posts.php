<?php
    function getPublishedPosts($pdo, $user_id) {
        $publish_posts_query = "SELECT post_id, post_title, post_updated 
        FROM posts WHERE user_id = :user_id AND post_status = 'published' 
        ORDER BY post_updated DESC LIMIT 3";
        $stmt = $pdo->prepare($publish_posts_query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }