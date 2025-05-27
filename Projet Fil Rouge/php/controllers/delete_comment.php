<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT(); // Will redirect if invalid
    require_once "../config/connectDB.php";

    header('Content-Type: application/json');

    $response = ['success' => false, 'error' => ''];

    if (!isset($_POST['comment_id'])) {
        $response['error'] = 'No comment ID provided';
        echo json_encode($response);
        exit;
    }

    $comment_id = (int)$_POST['comment_id'];
    $user_id = $userData->user_id;

    /** How $_POST works here even though there’s no form? $comment_id = (int)$_POST['comment_id'];
     * $_POST is a PHP superglobal array that contains data sent to the server via the HTTP POST method, typically from HTML forms. However, it can also contain data sent programmatically, like from a fetch request.
     * A form isn’t required to send POST data. The fetch request in JavaScript mimics a form submission by:
        ** Using the POST method.
        ** Sending data in the body with Content-Type: application/x-www-form-urlencoded.
        ** When the server receives the request with comment_id=123 in the body and the correct Content-Type, PHP automatically parses it into the $_POST array. So, $_POST['comment_id'] contains 123.
        ** PHP doesn’t care whether the data came from an actual <form> or a JavaScript request—it just processes the POST body. Since the Content-Type is application/x-www-form-urlencoded, PHP populates $_POST with comment_id.
     * Example:
        ** If the JavaScript sends: comment_id=123
        ** PHP’s $_POST array becomes:
            o $_POST = [
                'comment_id' => '123'
            ];
        ** Then $comment_id = (int)$_POST['comment_id']; sets $comment_id to 123 (integer).
     */

    // Verify the user owns the comment
    $query = "SELECT user_id FROM comments WHERE comment_id = :comment_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['comment_id' => $comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        $response['error'] = 'Comment not found';
        echo json_encode($response);
        exit;
    }

    if ($comment['user_id'] != $user_id) {
        $response['error'] = 'Unauthorized';
        echo json_encode($response);
        exit;
    }

    // Delete the comment and its replies (if any)
    try {
        $pdo->beginTransaction();

        // Delete replies first
        $queryReplies = "DELETE FROM comments WHERE parent_comment_id = :comment_id";
        $stmtReplies = $pdo->prepare($queryReplies);
        $stmtReplies->execute(['comment_id' => $comment_id]);

        // Delete the comment
        $query = "DELETE FROM comments WHERE comment_id = :comment_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['comment_id' => $comment_id]);

        $pdo->commit();
        $response['success'] = true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['error'] = 'Failed to delete comment: ' . $e->getMessage();
        error_log("Delete comment error: " . $e->getMessage());
    }

    echo json_encode($response);

    /**
     * Communication Between Server and Client:
        ** The PHP script (server) needs to inform the JavaScript (client) whether the deletion was successful.
        ** Using a JSON response with a success field is a common pattern:
            o success: true means “all good, proceed with UI updates.”
            o success: false (or missing) with an error field means “something went wrong, show an error.”
     * Error Handling:
        ** If the database query fails (e.g., due to a server issue or invalid comment_id), the PHP script sets $response['error'], which JavaScript uses to alert the user (alert('Failed to delete comment: ' + result.error)).
     */

    /** Example Flow
        1. User clicks delete button:
            ** Button has data-comment-id="123".
            ** JavaScript sends comment_id=123 to delete_comment.php.
        2. PHP processes the request:
            ** Verifies user is logged in and owns comment 123.
            ** Delete comment
            ** Sends back: {"success": true} (if deleted) or {"success": false, "error": "Failed to delete comment"} (if failed).
        3. JavaScript handles the response:
            ** Parses JSON: const result = await response.json();
            ** If result.success is true:
                o Removes <div id="comment-123"> from the DOM.
                o Decrements the response count.
            ** If result.success is false:
                o Shows an alert with result.error.
     */

    /** If You’re Still Confused
     * If you’re wondering specifically about how result.success knows to check $response['success']:
        ** It’s because json_encode($response) in PHP turns the $response array into a JSON object.
        ** response.json() in JavaScript turns that JSON back into a JavaScript object (result).
        ** The keys in $response (like success, error) become properties of result, so $response['success'] maps directly to result.success.
     */