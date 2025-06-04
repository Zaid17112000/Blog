<?php
    function checkCommentExists($pdo, $comment_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        return $stmt->fetchColumn() > 0;
    }