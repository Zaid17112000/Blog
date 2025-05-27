<?php
    function getCommentsCreationDate($pdo, $comment_id) {
        $queryCommentDate = "SELECT comment_created FROM comments WHERE comment_id = :comment_id";
        $stmt = $pdo->prepare($queryCommentDate);
        $stmt->execute(['comment_id' => $comment_id]);
        return $stmt->fetchColumn();
    }