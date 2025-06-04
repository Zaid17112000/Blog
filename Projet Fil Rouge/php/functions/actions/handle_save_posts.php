<?php
    session_start();
    require_once 'verify_jwt.php';
    $userData = verifyJWT();
    require_once "../../config/connectDB.php";

    header('Content-Type: application/json');

    $response = ['success' => false, 'message' => ''];

    try {
        // Check if post_id is provided
        if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
            http_response_code(400);
            throw new Exception('Invalid post ID');
        }

        $post_id = filter_var($_POST['post_id'], FILTER_VALIDATE_INT);
        $user_id = $userData->user_id;

        // Verify that the post exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE post_id = :post_id');
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            throw new Exception('Post not found');
        }

        // Check if the post is already saved
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM saved_posts WHERE user_id = :user_id AND post_id = :post_id');
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        $is_saved = $stmt->fetchColumn() > 0;

        if ($is_saved) {
            // Unsave the post
            $stmt = $pdo->prepare('DELETE FROM saved_posts WHERE user_id = :user_id AND post_id = :post_id');
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":post_id", $post_id);
            $stmt->execute();
            $response = [
                'success' => true,
                'message' => 'Post unsaved successfully',
                'action' => 'unsaved'
            ];
            http_response_code(200);
        } 
        else {
            // Save the post
            $stmt = $pdo->prepare('INSERT INTO saved_posts (user_id, post_id) VALUES (:user_id, :post_id)');
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":post_id", $post_id);
            $stmt->execute();
            $response = [
                'success' => true,
                'message' => 'Post saved successfully',
                'action' => 'saved'
            ];
            http_response_code(201);
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        http_response_code(500);
    }

    echo json_encode($response);
    exit;