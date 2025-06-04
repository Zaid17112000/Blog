<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/queries/get_posts_by_status.php";

    $user_id = $userData->user_id;

    $draftPosts = getPostsByStatus($pdo, 'draft', $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft Posts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/drafts_posts.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="../../assets/js/toggle_sidebar.js" defer></script>
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php"; ?>
    
    <?php include "../../views/pages/draft_main_section.php"; ?>
    
    <!-- Footer -->
    <?php include "../../views/partials/footer.html"; ?>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
    <script>
        <?php require_once "../../assets/js/setup_delete_post.js"; ?>
        // For draft posts
        setupDeletePost(
            '.draft-card',
            '.drafts-list',
            `<div class="no-drafts-message">
                <p>No draft posts yet. Start writing your next masterpiece!</p>
                <a href="add_blog_ajax_draft.php">
                    <button class="action-button">Create Your First Draft</button>
                </a>
            </div>`,
            "../functions/actions/delete_post.php"
        );
    </script>
    <script src="../../assets/js/publish_post.js"></script>
</body>
</html>