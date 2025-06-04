<?php
    session_start();
    require_once 'verify_jwt.php';
    $userData = verifyJWT();
    require_once "../../config/connectDB.php";

    header('Content-Type: application/json');
        
    $response = ['success' => false, 'message' => ''];

    try {
        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        // Check if post_id is provided
        if (!isset($_POST['post_id'])) {
            throw new Exception('Post ID is required');
        }

        $post_id = filter_var($_POST['post_id'], FILTER_VALIDATE_INT);
        if ($post_id === false) {
            throw new Exception('Invalid post ID');
        }

        $user_id = $userData->user_id;
        $post_status = isset($_POST["post_status"]) && $_POST["post_status"] == 'published' ? 'published' : 'draft';

        // Verify the post exists and belongs to the user
        $query = "SELECT post_id FROM posts WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception('Post not found or you do not have permission to delete it');
        }

        // Begin transaction
        $pdo->beginTransaction();

        try {
            // Delete related records in proper order to maintain referential integrity
            $tablesToClean = [
                'tags_posts',
                'likes',
                'comments',
                'saved_posts',
            ];

            foreach ($tablesToClean as $table) {
                $deleteQuery = "DELETE FROM $table WHERE post_id = :post_id";
                $stmt = $pdo->prepare($deleteQuery);
                $stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);
                $stmt->execute();
            }

            // Finally delete the post
            $deleteQuery = "DELETE FROM posts WHERE post_id = :post_id";
            $stmt = $pdo->prepare($deleteQuery);
            $stmt->bindParam(":post_id", $post_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Post deleted successfully';
            } else {
                $pdo->rollBack();
                throw new Exception('Failed to delete post');
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } catch(PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log('Database error in delete_post_dashboard.php: ' . $e->getMessage());
    } catch(Exception $e) {
        $response['message'] = $e->getMessage();
        error_log('Error in delete_post_dashboard.php: ' . $e->getMessage());
    }

    echo json_encode($response);
    exit;