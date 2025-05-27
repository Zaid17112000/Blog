<?php
    function getUserActivity($pdo, $user_id) {
        $activity_query = "SELECT 
        MONTH(post_published) as month, 
        COUNT(*) as count, 
        'post' as type 
            FROM posts 
            WHERE user_id = :user_id1 AND post_status = 'published' AND post_published >= DATE_SUB(NOW(), INTERVAL 1 YEAR) 
            GROUP BY MONTH(post_published)
            UNION ALL
            SELECT MONTH(comment_created) as month, COUNT(*) as count, 'comment' as type 
            FROM Comments 
            WHERE user_id = :user_id2 AND comment_created >= DATE_SUB(NOW(), INTERVAL 1 YEAR) 
            GROUP BY MONTH(comment_created)
            ORDER BY month";
        $stmt = $pdo->prepare($activity_query);
        $stmt->bindParam(":user_id1", $user_id);
        $stmt->bindParam(":user_id2", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }