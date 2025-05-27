<?php
    function getDraftPosts($pdo, $user_id) {
        $draft_posts_query = "SELECT post_id, post_title, post_updated 
        FROM posts WHERE user_id = :user_id AND post_status = 'draft' 
        ORDER BY post_updated DESC LIMIT 3";
        $stmt = $pdo->prepare($draft_posts_query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }