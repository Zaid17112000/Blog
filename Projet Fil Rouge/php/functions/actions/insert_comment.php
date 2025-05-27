<?php
    function insertComment($pdo, $post_id, $user_id, $comment, $parent_comment_id) {
        $query = "INSERT INTO comments (post_id, user_id, comment_content, parent_comment_id) VALUES (:post_id, :user_id, :comment_content, :parent_comment_id)";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([
            "post_id" => $post_id,
            "user_id" => $user_id,
            "comment_content" => $comment,
            "parent_comment_id" => $parent_comment_id
        ]);
    }