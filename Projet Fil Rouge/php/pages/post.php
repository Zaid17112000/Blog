<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/validation/check_user_like_post.php";
    require_once "../functions/queries/get_likes_count.php";
    require_once "../functions/queries/get_comments.php";
    require_once "../functions/validation/check_user_save_post.php";
    require_once "../functions/validation/validate_csrf_token.php";
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $current_user_id = $userData->user_id;

    // Get post_id from URL
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

    $stmt = $pdo->prepare("SELECT 
        first_name, 
        last_name, 
        img_profile
    FROM users 
    -- LEFT JOIN user_follows uf ON uf.user_id = users.user_id
    WHERE users.user_id = :user_id
    GROUP BY users.user_id");
    $stmt->execute(['user_id' => $current_user_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

    $queryPost = "SELECT * FROM posts WHERE post_id = :post_id";
    $stmt = $pdo->prepare($queryPost);
    $stmt->execute(['post_id' => $post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        // Handle case where post is not found
        header("Location: blogsphere.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute(["user_id" => $post['user_id']]);
    $post_author = $stmt->fetch(PDO::FETCH_ASSOC);

    $comments = getComments($pdo, $current_user_id, $post_id);

    // Organize comments into a nested tree structure
    $commentTree = [];
    $topLevelCount = 0;
    $pendingReplies = []; // Store replies whose parents aren't processed yet

    foreach ($comments as $comment) {
        $comment['replies'] = []; // Initialize replies array for all comments
        if ($comment['parent_comment_id'] === null) {
            $topLevelCount++;
            $commentTree[$comment['comment_id']] = $comment;
        } else {
            if (isset($commentTree[$comment['parent_comment_id']])) {
                $commentTree[$comment['parent_comment_id']]['replies'][] = $comment;
            } else {
                // Store reply for later processing
                $pendingReplies[$comment['parent_comment_id']][] = $comment;
            }
        }
    }

    // Process pending replies
    while (!empty($pendingReplies)) {
        $processed = false;
        foreach ($pendingReplies as $parentId => $replies) {
            if (isset($commentTree[$parentId])) {
                foreach ($replies as $reply) {
                    $commentTree[$parentId]['replies'][] = $reply;
                }
                unset($pendingReplies[$parentId]);
                $processed = true;
            }
        }
        // Prevent infinite loop if there are orphaned replies
        if (!$processed) {
            break;
        }
    }

    // Check if the current user liked this post
    $is_liked = checkUserLikePost($pdo, $current_user_id, $post_id);
    $like_class = $is_liked ? 'liked' : '';

    // Get like count for this post
    $like_count = likesCount($pdo, $post_id);

    // Check if the current user saved this post
    $is_saved = checkUserSavePost($pdo, $current_user_id, $post['post_id']);
    $save_class = $is_saved ? 'saved' : '';

    // Check if current user is following this post's author
    // $is_following = checkUserFollowPostAuthor($pdo, $current_user_id, $post['user_id']);

    // Handle follow/unfollow actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        validateCsrfToken();
        if (isset($_POST['action']) && $_POST['action'] === 'follow') {
            require_once "../functions/actions/follow_user.php";
            if (followToUser($pdo, $current_user_id, $post['user_id'])) {
                $_SESSION['message'] = "You've successfully followed!";
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'unfollow') {
            require_once "../functions/actions/unfollow_user.php";
            if (unfollowFromUser($pdo, $current_user_id, $post['user_id'])) {
                $_SESSION['message'] = "You've unfollowed.";
            }
        }
        // header("Location: ".$_SERVER['PHP_SELF']);
        // exit();
    }

    // Check if current user is followed to this profile
    $is_following  = false;
    if ($current_user_id && $post_author) {
        $stmt = $pdo->prepare("SELECT 1 
            FROM user_follows 
            WHERE follower_user_id = :current_user_id
            AND following_user_id = :author_id");
        $stmt->execute([
            'current_user_id' => $current_user_id,
            'author_id' => $post['user_id']
        ]);
        $is_following  = $stmt->fetchColumn();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post["post_title"]); ?> - BlogSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/post.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="../../assets/js/toggle_sidebar.js" defer></script>
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php" ?>

    <?php print_r($commentTree, true); ?>

    <!-- Blog Posts Section -->
    <?php include "../../views/pages/post_main_section.php" ?>

    <!-- Footer -->
    <?php include "../../views/partials/footer.html" ?>

    <script src="../../assets/js/post.js"></script>
    <script src="../../assets/js/handle_save_post.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
    <script>
        const escapeHTML = (str) => {
            if (typeof str !== 'string') return str;
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
        };

        // Main comment form handling
        const textareaComment = document.querySelector(".comments-form .write-comment_textarea textarea");
        const controlComments = document.querySelector(".comments-form .write-comment .control-comments");
        const respondComment = document.querySelector(".comments-form .control-comments .respond");
        const cancelComment = document.querySelector(".comments-form .control-comments .cancel");
        const commentForm = document.querySelector("#comment-form");
        const responsesSection = document.querySelector("#comments-container");
        const responseCount = document.querySelector("#response-count");

        // Initialize comment form
        toggleMainCommentRespondButton();

        toggleMainCommentCancelButton();

        toggleSettingsMenu();

        // Edit comment handling
        handleEditComment();

        // Save edited comment
        handleSaveEditComment();

        // Cancel edit
        handleCancelEditComment();

        // Cancel reply
        handleCancelReplyComment();

        handleSubmitComment();

        // Comment event listeners
        function addCommentEventListeners(commentElement) {
            // Delete comment
            handleDeleteComment(commentElement);

            handleCommentLike();

            // Reply toggle
            handleToggleReplyComment(commentElement);

            // Reply textarea input
            const replyTextarea = commentElement.querySelector('.reply-textarea');
            if (replyTextarea) {
                replyTextarea.addEventListener('input', function() {
                    const submitButton = this.closest('.reply-area').querySelector('.submit-button');
                    submitButton.disabled = this.value.trim() === '';
                });
            }
            handleSubmitReply(<?= $post_id ?>, commentElement);
            handleRepliesCount(commentElement);
        }

        if (textareaComment) {
            textareaComment.addEventListener("click", () => {
                textareaComment.classList.add("display__control-comments");
                controlComments.style.display = "block";
            });
        }

        handlePostLikes();

        // Attach event listeners to all existing comments
        document.querySelectorAll('.comment').forEach(commentElement => {
            addCommentEventListeners(commentElement);
        });
    </script>
</body>
</html>