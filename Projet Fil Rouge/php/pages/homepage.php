<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/queries/get_latest_posts.php";

    $posts = getLatestPosts($pdo);
?>    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/homepage.css">
</head>
<body>
    <?php
        $showEmptyState = $_SESSION["show_empty_bio_state"] ?? false;
        unset($_SESSION["show_empty_bio_state"]);
    ?>
        
    <div class="overlay <?php echo $showEmptyState ? 'show' : ''; ?>"></div>

    <!-- Header -->
    <?php include "../../views/partials/header.php" ?>
    
    <?php include "../../views/pages/add_bio_prompt.php" ?>
    
    <!-- Main Section -->
    <?php include "../../views/pages/homepage_main_section.php" ?>
    
    <!-- Newsletter -->
    <?php include "../../views/partials/newsletter.html" ?>
    
    <!-- Footer -->
    <?php include "../../views/partials/footer.html" ?>

    <script src="../../assets/js/toggle_sidebar.js"></script>
    <script src="../../assets/js/hide_bio.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
</body>
</html>