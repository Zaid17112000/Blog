<?php
    function queryStatusPost($pdo, $post_data) {
        $queryStatus = "SELECT post_status FROM posts WHERE post_id = :post_id";
        $stmt = $pdo->prepare($queryStatus);
        $stmt->execute(['post_id' => $post_data['post_id']]);
        return $stmt->fetchColumn();
    }