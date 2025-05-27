<?php
    function getSavedPosts($pdo, $user_id) {
        $saved_posts_query = "SELECT p.post_id, p.post_title, p.post_excerpt, sp.saved_at 
        FROM saved_posts sp 
        JOIN posts p ON sp.post_id = p.post_id 
        WHERE sp.user_id = :user_id 
        ORDER BY sp.saved_at DESC LIMIT 2";
        $stmt = $pdo->prepare($saved_posts_query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }