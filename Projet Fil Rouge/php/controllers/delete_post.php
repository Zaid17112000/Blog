<?php
session_start();
require_once __DIR__ . '/../controllers/verify_jwt.php';
$userData = verifyJWT(); // Will redirect if invalid
require_once "../config/connectDB.php";

// Ensure no output before this point
header('Content-Type: application/json');
    
$response = ['success' => false, 'message' => ''];

try {
    // Check if post_id is provided
    if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
        throw new Exception('Invalid post ID');
    }

    $post_id = filter_var($_POST['post_id'], FILTER_VALIDATE_INT);
    $user_id = $userData->user_id;
    $post_status = isset($_POST["post_status"]) ? $_POST["post_status"] : '';

    // Verify the post exists and belongs to the user
    $query = "SELECT post_id FROM posts WHERE post_id = :post_id AND user_id = :user_id AND post_status = :post_status";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->bindParam(":post_status", $post_status);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception('Post not found or you do not have permission to delete it');
    }

    // First delete from tags_posts (to avoid foreign key constraint)
    $deleteTagsQuery = "DELETE FROM tags_posts WHERE post_id = :post_id";
    $stmt = $pdo->prepare($deleteTagsQuery);
    $stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);
    $stmt->execute();

    // Then delete the post
    $deleteQuery = "DELETE FROM posts WHERE post_id = :post_id AND user_id = :user_id";
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Post deleted successfully';
    } else {
        throw new Exception('Failed to delete post');
    }
} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch(Exception $e) {
    $response['message'] = $e->getMessage();
}

// Ensure only JSON is output
echo json_encode($response);
exit;