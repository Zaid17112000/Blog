<?php
    require_once '../../config/connectDB.php';
    require_once 'verify_jwt.php';

    // Verify JWT token
    $userData = verifyJWT();

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id_to_delete = filter_var($input['user_id'] ?? null, FILTER_VALIDATE_INT);

    // Validate input
    if (!$user_id_to_delete) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user ID']);
        exit;
    }

    try {
        // Check if the requesting user is an admin
        $stmt = $pdo->prepare("SELECT user_role FROM users WHERE user_id = :current_user_id");
        $stmt->bindParam(':current_user_id', $userData->user_id);
        $stmt->execute();
        $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_user || $current_user['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized - Admin access required']);
            exit;
        }

        // Prevent self-deletion
        if ($user_id_to_delete == $userData->user_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete your own account']);
            exit;
        }

        // Begin transaction
        $pdo->beginTransaction();

        // Delete user (cascading deletes will handle related records)
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id_to_delete);
        $stmt->execute();

        // Commit transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting user: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete user']);
    }