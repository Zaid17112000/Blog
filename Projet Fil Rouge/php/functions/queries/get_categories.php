<?php
    function getCategories($pdo) {
        $queryCategories = "SELECT category_name FROM categories";
        $stmt = $pdo->prepare($queryCategories);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }