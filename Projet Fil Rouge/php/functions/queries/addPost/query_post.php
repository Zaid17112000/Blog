<?php
    function queryPost($pdo, $post_id, $user_id) {
        $queryPost = "SELECT post_title, post_content, post_excerpt, post_img_url, post_status FROM posts WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $pdo->prepare($queryPost);
        $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }