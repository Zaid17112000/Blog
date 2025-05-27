<?php
    require_once '../config/connectDB.php';
    require_once __DIR__ . '/verify_jwt.php';

    $userData = verifyJWT();

    header('Content-Type: application/json');

    try {
        // First check the request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }
        
        $user_id = $userData->user_id;
        
        $data = json_decode(file_get_contents('php://input'), true);
        $post_id = filter_var($data['post_id'] ?? null, FILTER_VALIDATE_INT);
        
        if (!$post_id) {
            throw new Exception('Invalid post ID');
        }
        
        // Check if the post is actually saved by this user
        $stmt = $pdo->prepare("SELECT * FROM Saved_Posts WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Post not found in saved items');
        }
        
        // Delete the saved post
        $stmt = $pdo->prepare("DELETE FROM Saved_Posts WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        
        echo json_encode(['success' => true, 'message' => 'Post unsaved successfully']);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }