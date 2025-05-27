<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT(); // Will redirect if invalid
    require_once "../config/connectDB.php";

    header('Content-Type: application/json');

    // Validate POST data
    if (!isset($_POST['post_id']) || !isset($_POST['action'])) {
        echo json_encode(['success' => false, 'error' => 'Missing post_id or action']);
        exit;
    }

    $post_id = (int)$_POST['post_id'];
    $user_id = $userData->user_id;
    $action = $_POST['action'];

    // Validate action
    if (!in_array($action, ['save', 'unsave'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
    }

    // Validate post exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE post_id = ?");
    $stmt->execute([$post_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($action === 'save') {
            // Check if already saved
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_posts WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
            if ($stmt->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO saved_posts (user_id, post_id, saved_at) VALUES (?, ?, NOW())");
                $stmt->execute([$user_id, $post_id]);
            }
        } else {
            // Delete saved post
            $stmt = $pdo->prepare("DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
        }

        // Check current save status
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_posts WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        $is_saved = $stmt->fetchColumn() > 0;

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'is_saved' => $is_saved,
            'message' => $is_saved ? 'Post saved' : 'Post unsaved'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Save error: ' . $e->getMessage()); // Log error
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }