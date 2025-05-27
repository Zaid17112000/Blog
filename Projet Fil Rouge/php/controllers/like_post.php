<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/validation/check_user_like_post.php";
    require_once "../functions/actions/like_action.php";
    require_once "../functions/queries/get_likes_count.php";

    header('Content-Type: application/json');

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["post_id"])) {
        $post_id = filter_var($_POST["post_id"], FILTER_VALIDATE_INT);
        $user_id = $userData->user_id;

        if ($post_id === false) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid post ID"]);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $userLikedPost = checkUserLikePost($pdo, $user_id, $post_id);

            $action = $userLikedPost 
            ? removeLike($pdo, $user_id, $post_id) 
            : addLike($pdo, $user_id, $post_id);

            // Get updated like count
            $likes = likesCount($pdo, $post_id);

            $pdo->commit();

            echo json_encode([
                "success" => true,
                "likes" => $likes,
                "action" => $action
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid request"]);
    }