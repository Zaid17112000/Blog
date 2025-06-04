<?php
    require_once 'verify_jwt.php';
    $userData = verifyJWT();
    require_once "../../config/connectDB.php";
    require_once "../validation/check_post_exist.php";
    require_once "../validation/check_user_like_post.php";
    require_once "../queries/get_likes_count.php";

    if (!isset($_POST['post_id']) || !isset($_POST['action'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }

    $post_id = (int)$_POST['post_id'];
    $user_id = $userData->user_id;
    $action = $_POST['action'];

    if (!in_array($action, ['like', 'unlike'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
    }

    // Validate post exists
    $post_exist = postExists($pdo, $post_id);
    
    if (!$post_exist) {
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($action === 'like') {
            // Check if already liked
            $already_liked = checkUserLikePost($pdo, $user_id, $post_id);

            if (!$already_liked) {
                $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $post_id]);
            }
        } else {
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
        }

        // Get updated like count
        $like_count = likesCount($pdo, $post_id);

        // Check if user has liked the post
        $is_liked = checkUserLikePost($pdo, $user_id, $post_id);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'new_like_count' => $like_count,
            'is_liked' => $is_liked
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }