<?php
    function getLikesCount($pdo, $user_id) {
        $likes_received_query = "SELECT COUNT(*) as likes_received FROM likes l 
        JOIN posts p ON l.post_id = p.post_id 
        WHERE p.user_id = :user_id";
        $stmt = $pdo->prepare($likes_received_query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['likes_received'];
    }