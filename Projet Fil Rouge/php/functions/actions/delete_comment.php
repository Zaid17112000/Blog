<?php
    session_start();
    require_once 'verify_jwt.php';
    $userData = verifyJWT();
    require_once "../../config/connectDB.php";

    header('Content-Type: application/json');

    $response = ['success' => false, 'error' => ''];

    if (!isset($_POST['comment_id'])) {
        $response['error'] = 'No comment ID provided';
        echo json_encode($response);
        exit;
    }

    $comment_id = (int)$_POST['comment_id'];
    $user_id = $userData->user_id;

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

    // Delete the comment and its replies (if any)
    try {
        $pdo->beginTransaction();

        // Delete replies first
        $queryReplies = "DELETE FROM comments WHERE parent_comment_id = :comment_id";
        $stmtReplies = $pdo->prepare($queryReplies);
        $stmtReplies->execute(['comment_id' => $comment_id]);

        // Delete the comment
        $query = "DELETE FROM comments WHERE comment_id = :comment_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['comment_id' => $comment_id]);

        $pdo->commit();
        $response['success'] = true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['error'] = 'Failed to delete comment: ' . $e->getMessage();
        error_log("Delete comment error: " . $e->getMessage());
    }

    echo json_encode($response);