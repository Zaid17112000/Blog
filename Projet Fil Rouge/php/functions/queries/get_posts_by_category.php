<?php
    function getPostsByCategory($pdo, $category = null) {
        $queryGetPosts = "SELECT p.*, CONCAT(u.last_name, ' ', u.first_name) AS username, p.post_created, c.category_name, COUNT(l.like_id) as like_count FROM posts p 
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN categories_posts cp ON p.post_id = cp.post_id 
        LEFT JOIN categories c ON cp.category_id = c.category_id
        LEFT JOIN users u ON u.user_id = p.user_id";

        $conditions = [];
        $params = [];

        if (!empty($category)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_name = :category_name");
            $stmt->bindParam(":category_name", $category);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                // Category doesn't exist, redirect or show all posts
                header("Location: blog_posts.php");
                exit;
            }
            $conditions[] = "c.category_name = :category";
            $params[':category'] = urldecode($_GET['category']);
        }

        // Append conditions to the query
        if (!empty($conditions)) {
            $queryGetPosts .= " WHERE " . implode(" AND ", $conditions);
        }
        /**
         * The implode() function takes an array ($conditions) and joins its elements into a single string, with each element separated by the specified delimiter (" AND ").
         * For example, if $conditions = ["p.category_name = :category", "p.status = 'published'"], then implode(" AND ", $conditions) produces: p.category_name = :category AND p.status = 'published'.
         * The implode() function is used to handle multiple conditions flexibly. If you later add more filters (e.g., filtering by post status or date), you can append more conditions to the $conditions array, and implode() will combine them with AND. For example:
            $conditions = [
                "p.category_name = :category",
                "p.status = 'published'"
            ];
        * This becomes:
            ** WHERE p.category_name = :category AND p.status = 'published'
        */

        $queryGetPosts .= " GROUP BY p.post_id DESC";

        // Prepare and execute the query
        $stmt = $pdo->prepare($queryGetPosts);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }