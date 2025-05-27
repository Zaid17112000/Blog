<?php
    function checkUserLikeComment($pdo, $comment_id, $user_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $user_id]);
        return $stmt->fetchColumn() > 0;
    }