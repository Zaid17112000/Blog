<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT(); // Will redirect if invalid
    require_once "../config/connectDB.php";

    header('Content-Type: application/json');

    $response = ['success' => false, 'error' => ''];

    if (!isset($_POST['comment_id']) || !isset($_POST['comment_content'])) {
        $response['error'] = 'Missing comment ID or content';
        echo json_encode($response);
        exit;
    }

    $comment_id = (int)$_POST['comment_id'];
    $comment_content = trim(filter_input(INPUT_POST, 'comment_content', FILTER_SANITIZE_SPECIAL_CHARS));
    $user_id = $userData->user_id;

    if (empty($comment_content)) {
        $response['error'] = 'Comment content cannot be empty';
        echo json_encode($response);
        exit;
    }

    // Verify the user owns the comment
    $query = "SELECT user_id FROM comments WHERE comment_id = :comment_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['comment_id' => $comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        $response['error'] = 'Comment not found';
        echo json_encode($response);
        exit;
    }

    if ($comment['user_id'] != $user_id) {
        $response['error'] = 'Unauthorized';
        echo json_encode($response);
        exit;
    }

    // Update the comment
    $query = "UPDATE comments SET comment_content = :comment_content, comment_updated = NOW() WHERE comment_id = :comment_id";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute(['comment_content' => $comment_content, 'comment_id' => $comment_id])) {
        $response['success'] = true;
        $response['comment_content'] = $comment_content;
    } else {
        $response['error'] = 'Failed to update comment';
    }

    echo json_encode($response);