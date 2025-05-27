<?php
    function addLike($pdo, $comment_id, $user_id) {
        $stmt = $pdo->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
        $stmt->execute([$comment_id, $user_id]);
        return true;
    }

    function removeLike($pdo, $comment_id, $user_id) {
        $stmt = $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $user_id]);
        return false;
    }