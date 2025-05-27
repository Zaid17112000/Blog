<?php
    function getLikeCommentCount($pdo, $comment_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        return $stmt->fetchColumn();
    }