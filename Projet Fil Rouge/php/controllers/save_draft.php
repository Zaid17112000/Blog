<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT(); // Use JWT verification consistently
    require_once "../config/connectDB.php";
    require_once "../functions/actions/draft_submission.php";

    $user_id = $userData->user_id;

    header('Content-Type: application/json'); // Ensure JSON response

    // Configuration
    const UPLOAD_DIR = '/Projet Fil Rouge/php/uploads/';
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
    const MAX_CATEGORIES = 10;
    const MAX_TAGS = 20;


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $response = processDraftSubmission($pdo, $user_id);
            sendJsonResponse($response);
        } catch (Exception $e) {
            // $pdo->rollBack();
            error_log("Draft save error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error saving draft: ' . $e->getMessage()
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
        exit;
    }