<?php
    function postExists($pdo, $post_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE post_id = :post_id");
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }