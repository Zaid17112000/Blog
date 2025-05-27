<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT(); // Will redirect if invalid
    require_once "../config/connectDB.php";
    require_once "../functions/queries/get_posts_by_status.php";

    // $user_id = $_SESSION["user"];
    $user_id = $userData->user_id;

    $publishedPosts = getPostsByStatus($pdo, 'published', $user_id);
?>

<!DOCTYPE html>
<htmlc lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Published Posts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/published_posts.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="../../assets/js/toggle_sidebar.js" defer></script>
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php"; ?>

    <!-- Main Content -->
    <?php include "../../views/pages/published_posts.php" ?>
    
    <!-- Footer -->
    <?php include "../../views/partials/footer.html"; ?>

    <script src="../../assets/js/main.js"></script>
    <!-- <script src="../../assets/js/handle_delete_post.js"></script> -->
    <script>
        <?php require_once "../../assets/js/setup_delete_post.js"; ?>
        setupDeletePost(
            '.post-card',
            '.posts-grid',
            `<div class="no-posts-message">
                <p>No posts have been published yet. Start sharing your ideas by creating a new post!</p>
                <a href="add_blog_ajax_draft.php"><button class="action-button">Create Your First Post</button></a>
            </div>`,
            "../../php/controllers/delete_post.php"
        );
    </script>
</body>
</htmlc