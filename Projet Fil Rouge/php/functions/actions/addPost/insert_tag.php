<?php
    function insertTag($pdo, $tag_name) {
        $queryInsertTag = "INSERT INTO tags (tag_name, tag_slug) VALUES (:tag_name, :tag_slug)";
        $stmt = $pdo->prepare($queryInsertTag);
        $slug = createSlug($tag_name);
        return $stmt->execute([
            ':tag_name' => $tag_name,
            ':tag_slug' => $slug
        ]);
    }