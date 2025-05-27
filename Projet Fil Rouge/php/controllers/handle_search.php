<?php
    session_start();
    require_once 'verify_jwt.php';
    $userData = verifyJWT(); // Will redirect if invalid
    require_once "../config/connectDB.php";

    header('Content-Type: application/json');

    $user = $userData->user_id;

    try {
        // Check if search term is provided
        if (!isset($_POST['search-by-title'])) {
            echo json_encode(['success' => false, 'message' => 'Search term is required']);
            exit;
        }

        $searchTerm = '%' . trim($_POST['search-by-title']) . '%';
        $category = isset($_GET['category']) ? trim($_GET['category']) : null;

        $query = "SELECT p.*, 
            CONCAT(u.last_name, ' ', u.first_name) AS username, 
            p.post_created, 
            GROUP_CONCAT(DISTINCT c.category_name SEPARATOR ' | ') AS category_name, 
            COUNT(l.like_id) as like_count, 
            u.img_profile 
            FROM posts p 
            LEFT JOIN likes l ON p.post_id = l.post_id
            LEFT JOIN categories_posts cp ON p.post_id = cp.post_id 
            LEFT JOIN categories c ON cp.category_id = c.category_id
            LEFT JOIN users u ON u.user_id = p.user_id
            WHERE p.post_title LIKE :searchTerm
            AND p.post_id != (SELECT MAX(post_id) FROM posts)";

        $params = [':searchTerm' => $searchTerm];

        if ($category) {
            $query .= " AND c.category_name = :category";
            $params[':category'] = $category;
        }

        $query .= " GROUP BY p.post_id ORDER BY p.post_created DESC";

        $stmt = $pdo->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as &$post) {
            // Check if the current user liked this post
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND post_id = :post_id");
            $stmt->bindParam(':user_id', $user);
            $stmt->bindParam(':post_id', $post["post_id"]);
            $stmt->execute();
            $is_liked = $stmt->fetchColumn() > 0;
            $post['like_class'] = $is_liked ? 'liked' : '';
            $post['like_icon_class'] = $is_liked ? 'fa-solid' : 'fa-regular';

            // Check if the current user saved this post
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_posts WHERE user_id = :user_id AND post_id = :post_id");
            $stmt->bindParam(':user_id', $user);
            $stmt->bindParam(':post_id', $post["post_id"]);
            $stmt->execute();
            $is_saved = $stmt->fetchColumn() > 0;
            $post['save_class'] = $is_saved ? 'saved' : '';
        }
        
        echo json_encode([
            'success' => true,
            'posts' => $posts,
            'searchTerm' => trim($_POST['search-by-title'])
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }