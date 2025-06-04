<?php
    // Start output buffering to catch any accidental output
    ob_start();

    try {
        session_start();
        require_once 'verify_jwt.php';
        require_once "../../config/connectDB.php";

        // Set headers first to ensure JSON response
        header('Content-Type: application/json');
        
        // Initialize response array
        $response = ['success' => false, 'error' => ''];

        // Verify request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        // Verify JWT and get user data
        $userData = verifyJWT();
        $user_id = $userData->user_id;

        // Validate input
        if (!isset($_POST['comment_id']) || !isset($_POST['comment_content'])) {
            throw new Exception('Missing comment ID or content');
        }

        $comment_id = (int)$_POST['comment_id'];
        $comment_content = trim($_POST['comment_content']);

        if (empty($comment_content)) {
            throw new Exception('Comment content cannot be empty');
        }

        // Verify comment ownership
        $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE comment_id = :comment_id");
        $stmt->execute(['comment_id' => $comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comment) {
            throw new Exception('Comment not found');
        }

        if ($comment['user_id'] != $user_id) {
            throw new Exception('Unauthorized to edit this comment');
        }

        // Update the comment
        $stmt = $pdo->prepare("UPDATE comments SET comment_content = :comment_content, comment_updated = NOW() WHERE comment_id = :comment_id");
        $success = $stmt->execute([
            'comment_content' => $comment_content,
            'comment_id' => $comment_id
        ]);

        if (!$success) {
            throw new Exception('Failed to update comment in database');
        }

        // Clear any buffered output
        ob_end_clean();

        // Send success response
        echo json_encode([
            'success' => true,
            'comment_content' => htmlspecialchars($comment_content, ENT_QUOTES, 'UTF-8')
        ]);
        exit;

    } catch (Exception $e) {
        // Clear any buffered output
        ob_end_clean();
        
        // Send error response
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }