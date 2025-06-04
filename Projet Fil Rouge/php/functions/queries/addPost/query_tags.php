<?php
    function queryTags($pdo, $post_id) {
        $queryTagsPost = "SELECT t.tag_name FROM tags t JOIN tags_posts tp ON t.tag_id = tp.tag_id WHERE tp.post_id = :post_id";
        $stmt = $pdo->prepare($queryTagsPost);
        $stmt->execute(['post_id' => $post_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }