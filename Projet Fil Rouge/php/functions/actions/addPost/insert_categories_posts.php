<?php
    function insertCategoryPost($pdo, $post_id, $category_id) {
        $queryInsertCategoryPost = "INSERT INTO categories_posts (post_id, category_id) VALUES (:post_id, :category_id) ON DUPLICATE KEY UPDATE post_id = VALUES(post_id)";
        $stmt = $pdo->prepare($queryInsertCategoryPost);
        return $stmt->execute([
            ':post_id' => $post_id,
            ':category_id' => $category_id
        ]);
    }