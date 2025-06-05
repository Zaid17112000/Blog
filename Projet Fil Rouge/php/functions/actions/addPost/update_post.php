<?php
    function updatePost($pdo, $post_title, $post_content, $post_excerpt, $post_img_url, $post_status, $post_id, $user_id) {
        $queryUpdatePost = "UPDATE posts 
        SET post_title = :post_title, post_content = :post_content, post_excerpt = :post_excerpt, post_img_url = :post_img_url, post_status = :post_status 
        WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $pdo->prepare($queryUpdatePost);
        return $stmt->execute([
            ':post_title' => $post_title,
            ':post_content' => $post_content,
            ':post_excerpt' => $post_excerpt,
            ':post_img_url' => $post_img_url,
            ':post_status' => $post_status,
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
    }