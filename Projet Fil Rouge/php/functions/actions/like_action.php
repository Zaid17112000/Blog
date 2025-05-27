<?php
    function removeLike($pdo, $user_id, $post_id) {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        return "unliked";
    }
    function addLike($pdo, $user_id, $post_id) {
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        return "liked";
    }