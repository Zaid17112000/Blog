<?php
    session_start();
    require_once 'verify_jwt.php';
    $userData = verifyJWT();
    require_once "../../config/connectDB.php";
    require_once "../validation/check_comment_exists.php";
    require_once "../validation/check_user_like_comment.php";
    require_once "handle_like_comment.php";
    require_once "../queries/get_like_comment_count.php";

    header('Content-Type: application/json');

    $user_id = $userData->user_id;
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // Debug logging
    error_log("handle_comment_like.php - User: $user_id, Comment ID: $comment_id, Action: $action");

    // Validate inputs
    if ($comment_id <= 0 || !in_array($action, ['like', 'unlike'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid comment ID or action']);
        exit;
    }

    // Check if comment exists
    if (!checkCommentExists($pdo, $comment_id)) {
        error_log("Comment ID $comment_id not found in database");
        echo json_encode(['success' => false, 'error' => 'Comment not found']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if user has liked the comment
        $is_liked = checkUserLikeComment($pdo, $comment_id, $user_id);

        if ($action === 'like' && !$is_liked) {
            // Add like
            $is_liked = addLike($pdo, $comment_id, $user_id);
            error_log("Like added for comment $comment_id by user $user_id");
        } elseif ($action === 'unlike' && $is_liked) {
            // Remove like
            $is_liked = removeLike($pdo, $comment_id, $user_id);
            error_log("Like removed for comment $comment_id by user $user_id");
        } else {
            // No action needed (e.g., already liked or unliked)
            error_log("No action taken: is_liked=$is_liked, action=$action");
        }

        // Get updated like count
        $new_like_count = getLikeCommentCount($pdo, $comment_id);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'is_liked' => $is_liked,
            'new_like_count' => $new_like_count
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Database error in handle_comment_like.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }