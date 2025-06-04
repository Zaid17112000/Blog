<?php
    function insertPost($pdo, $post_title, $post_content, $post_excerpt, $post_img_url, $post_status, $post_published_current_date, $user_id) {
        $queryInsertPost = "INSERT INTO posts(post_title, post_content, post_excerpt, post_img_url, post_status, post_published, user_id) VALUES(:post_title, :post_content, :post_excerpt, :post_img_url, :post_status, :post_published, :user_id)";
        $stmt = $pdo->prepare($queryInsertPost);
        return $stmt->execute([
            ':post_title' => $post_title,
            ':post_content' => $post_content,
            ':post_excerpt' => $post_excerpt,
            ':post_img_url' => $post_img_url,
            ':post_status' => $post_status,
            ':post_published' => $post_published_current_date,
            ':user_id' => $user_id
        ]);
    }