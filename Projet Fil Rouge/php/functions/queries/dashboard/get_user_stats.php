<?php
    function getUserStats($pdo, $user_id) {
        $stats_query = " SELECT 
            (SELECT COUNT(*) FROM posts WHERE user_id = :user_id1 AND post_status = 'published') as published_posts,
            (SELECT COUNT(*) FROM posts WHERE user_id = :user_id2 AND post_status = 'draft') as draft_posts,
            (SELECT COUNT(*) FROM comments WHERE user_id = :user_id3) as total_comments,
            (SELECT COUNT(*) FROM likes WHERE user_id = :user_id4) as total_likes,
            (SELECT COUNT(*) FROM saved_posts WHERE user_id = :user_id5) as total_saved,
            (SELECT COUNT(*) FROM likes l JOIN posts p ON l.post_id = p.post_id WHERE p.user_id = :user_id6) as likes_received";
        $stmt = $pdo->prepare($stats_query);
        $stmt->bindParam(":user_id1", $user_id);
        $stmt->bindParam(":user_id2", $user_id);
        $stmt->bindParam(":user_id3", $user_id);
        $stmt->bindParam(":user_id4", $user_id);
        $stmt->bindParam(":user_id5", $user_id);
        $stmt->bindParam(":user_id6", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }