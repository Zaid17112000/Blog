<?php
    function queryCategoriesPost($pdo, $post_id) {
        $queryCategoriesPost = "SELECT c.category_name FROM categories c JOIN categories_posts cp ON c.category_id = cp.category_id WHERE cp.post_id = :post_id";
        $stmt = $pdo->prepare($queryCategoriesPost);
        $stmt->execute(['post_id' => $post_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }