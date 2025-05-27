<?php
    function getRecentComments($pdo, $user_id) {
        $recent_comments_query = "SELECT c.comment_id, c.comment_content, c.comment_created, p.post_title, p.post_id 
        FROM comments c 
        JOIN posts p ON c.post_id = p.post_id 
        WHERE c.user_id = :user_id 
        ORDER BY c.comment_created DESC LIMIT 5";
        $stmt = $pdo->prepare($recent_comments_query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }