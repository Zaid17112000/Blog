<?php
    function checkUserLikePost($pdo, $user_id, $post_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND post_id = :post_id");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }