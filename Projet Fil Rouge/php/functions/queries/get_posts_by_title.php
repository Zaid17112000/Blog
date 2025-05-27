<?php
    // require_once "../config/bootstrap.php";
    // require_once "../config/connectDB.php";

    // $title = isset($_GET['title']) ? "%" . $_GET['title'] . "%" : "%%";

    // $stmt = $pdo->prepare("SELECT p.*, CONCAT(u.last_name, ' ', u.first_name) AS username, 
    //     GROUP_CONCAT(c.category_name SEPARATOR ' | ') AS category_name 
    //     FROM posts p
    //     LEFT JOIN users u ON p.user_id = u.user_id
    //     LEFT JOIN categories_posts cp ON p.post_id = cp.post_id
    //     LEFT JOIN categories c ON cp.category_id = c.category_id
    //     WHERE p.post_title LIKE :title
    //     GROUP BY p.post_id");
    // $stmt->bindParam(':title', $title);
    // $stmt->execute();

    // echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));