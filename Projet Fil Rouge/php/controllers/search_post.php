<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT(); // Will redirect if invalid
    require_once "../config/connectDB.php";

    if (isset($_GET['query'])) {
        $query = trim($_GET['query']);
        $user_id = $userData->user_id;

        $sql = "SELECT p.*, 
            CONCAT(u.last_name, ' ', u.first_name) AS username, 
            c.category_name,
            COUNT(l.like_id) AS like_count,
            (SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND post_id = p.post_id) > 0 AS is_liked,
            (SELECT COUNT(*) FROM saved_posts WHERE user_id = :user_id AND post_id = p.post_id) > 0 AS is_saved
        FROM posts p
        LEFT JOIN users u ON p.user_id = u.user_id
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN categories_posts cp ON p.post_id = cp.post_id
        LEFT JOIN categories c ON cp.category_id = c.category_id
        WHERE p.post_title LIKE :search OR p.post_content LIKE :search
        GROUP BY p.post_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'search' => "%$query%",
            'user_id' => $user_id
        ]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($posts);
    }