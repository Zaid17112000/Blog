<?php
    session_start();
    require_once 'verify_jwt.php';
    $userData = verifyJWT();
    require_once "../../config/connectDB.php";

    header('Content-Type: application/json');

    $response = ['success' => false, 'error' => ''];

    if (!isset($_POST['user_id'])) {
        $response['error'] = 'Missing user ID to unfollow';
        echo json_encode($response);
        exit;
    }

    $user_id = (int)$_POST['user_id'];
    $current_user = $userData->user_id;

    // Check if following
    $stmt = $pdo->prepare("SELECT * FROM user_follows WHERE user_id = :user_id AND follower_id = :follower_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":follower_id", $current_user);
    $stmt->execute();
    if (!$stmt->fetch()) {
        $response['error'] = 'Not following this user';
        echo json_encode($response);
        exit;
    }

    // Remove follow relationship
    $stmt = $pdo->prepare("DELETE FROM user_follows WHERE user_id = :user_id AND follower_id = :follower_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":follower_id", $current_user);
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to unfollow';
    }

    echo json_encode($response);