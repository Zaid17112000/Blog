<?php
    function insertCategoryPost($pdo, $post_data, $category_id) {
        $queryInsertCategoryPost = "INSERT INTO categories_posts (post_id, category_id) VALUES (:post_id, :category_id)";
        $stmt = $pdo->prepare($queryInsertCategoryPost);
        $stmt->bindParam(':post_id', $post_data['post_id']);
        $stmt->bindParam(':category_id', $category_id);
        return $stmt->execute();
    }