<?php
    session_start();
    require_once "../config/connectDB.php";
    require_once "../functions/actions/insert_comment.php";
    require_once "../functions/queries/get_comments_creation_date.php";
    require_once "../functions/queries/get_username.php";
    require_once "../functions/actions/process_comment_submission.php";

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        sendErrorResponse("Invalid request method", 405);
        exit;
    }

    // Main processing function
    processCommentSubmission($pdo);