<?php
    function queryCategory($pdo, $category_name) {
        $queryCategory = "SELECT category_id FROM categories WHERE category_name = :category_name";
        $stmt = $pdo->prepare($queryCategory);
        $stmt->bindParam(':category_name', $category_name);
        $stmt->execute();
        return $stmt->fetchColumn();
    }