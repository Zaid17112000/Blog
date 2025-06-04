<?php
    session_start();
    require_once "../../config/connectDB.php";

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $post_id = $_POST['post_id'] ?? null;

    if (!$post_id) {
        echo json_encode(['success' => false, 'message' => 'Post ID is required']);
        exit;
    }

    try {
        $query = "UPDATE posts SET post_status = 'published', post_published = NOW() WHERE post_id = :post_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'post_id' => $post_id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Post published successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Post not found or not authorized']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    exit;