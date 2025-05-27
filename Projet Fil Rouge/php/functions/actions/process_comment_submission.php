<?php
    /**
     * Processes the comment submission, validates inputs, and handles database operations.
     * @param PDO $pdo Database connection object
     */
    function processCommentSubmission($pdo) {
        // Get and sanitize form data
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS));
        $parent_comment_id = filter_input(INPUT_POST, 'parent_comment_id', FILTER_VALIDATE_INT) ?: null;
        $user_id = $_SESSION["user"] ?? null;
    
        // Validate input
        if (!$post_id || $post_id <= 0) {
            sendErrorResponse("Invalid post ID");
        }
        if (empty($comment)) {
            sendErrorResponse("Comment cannot be empty");
        }
        if (!$user_id) {
            sendErrorResponse("User not logged in");
        }
    
        try {
            // Validate post existence
            if (!validateEntityExists($pdo, "posts", "post_id", $post_id)) {
                sendErrorResponse("Post not found");
            }
    
            // Validate user existence
            if (!validateEntityExists($pdo, "users", "user_id", $user_id)) {
                sendErrorResponse("User not found");
            }
    
            // Validate parent comment if provided
            if ($parent_comment_id && !validateEntityExists($pdo, "comments", "comment_id", $parent_comment_id)) {
                sendErrorResponse("Parent comment not found");
            }
        
            // Insert comment into database
            $result = insertComment($pdo, $post_id, $user_id, $comment, $parent_comment_id);
            if (!$result) {
                sendErrorResponse("Failed to save comment");
            }
    
            // Get the inserted comment's creation date
            $comment_id = $pdo->lastInsertId();
            $created_at = getCommentsCreationDate($pdo, $comment_id);
            $user_name = getUsername($pdo, $user_id);
    
            // Return comment data as JSON for AJAX
            header("Content-Type: application/json");
            echo json_encode([
                "success" => true,
                "comment_id" => $comment_id,
                "comment" => $comment,
                "user_name" => $user_name, // Replace with actual user name from DB
                "created_at" => date("M d, Y", strtotime($created_at)),
                "parent_comment_id" => $parent_comment_id
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage() . " | post_id=$post_id, user_id=$user_id");
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
        }
    }

    /**
     * Validates if an entity exists in the database.
     * @param PDO $pdo Database connection
     * @param string $table Table name
     * @param string $column Column name
     * @param int $id Entity ID
     * @return bool True if exists, false otherwise
     */
    function validateEntityExists($pdo, $table, $column, $id) {
        // Whitelist allowed tables to prevent SQL injection
        $allowedTables = ["posts", "users", "comments"];
        if (!in_array($table, $allowedTables)) {
            throw new InvalidArgumentException("Invalid table name: $table");
        }

        $query = "SELECT COUNT(*) FROM $table WHERE $column = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Sends a JSON error response and exits.
     * @param string $message Error message
     * @param int $statusCode HTTP status code (default: 400)
     */
    function sendErrorResponse($message, $statusCode = 400) {
        header("Content-Type: application/json", true, $statusCode);
        echo json_encode(["success" => false, "error" => $message]);
        exit;
    }