<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/queries/get_saved_posts.php";

    $user_id = $userData->user_id;

    // Fetch saved posts
    try {
        $saved_posts = savedPosts($pdo, $user_id);
    } catch (PDOException $e) {
        $saved_posts = [];
        $error = "Error fetching saved posts: " . $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Saved Posts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/saved_posts.css">
    <script src="../../assets/js/toggle_sidebar.js" defer></script>
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php" ?>

    <?php include "../../views/pages/saved_posts.php" ?>

    <?php include "../../views/partials/footer.html" ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="../../assets/js/unsave_post.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
</body>
</html>