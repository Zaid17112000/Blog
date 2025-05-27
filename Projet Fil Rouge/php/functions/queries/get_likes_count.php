<?php
    function likesCount($pdo, $post_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }