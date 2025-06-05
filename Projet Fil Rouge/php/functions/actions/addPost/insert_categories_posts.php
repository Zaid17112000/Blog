<?php
    function insertCategoryPost($pdo, $post_id, $category_id) {
        $queryInsertCategoryPost = "INSERT INTO categories_posts (post_id, category_id) VALUES (:post_id, :category_id)";
        $stmt = $pdo->prepare($queryInsertCategoryPost);
        return $stmt->execute([
            ':post_id' => $post_id,
            ':category_id' => $category_id
        ]);
    }