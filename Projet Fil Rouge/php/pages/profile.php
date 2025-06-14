<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/queries/get_posts_by_status.php";
    require_once "../functions/queries/get_likes_count.php";
    require_once "../functions/queries/get_followers_count.php";
    require_once "../functions/queries/get_published_posts.php";
    require_once "../functions/actions/format_followers.php";
    require_once "../functions/validation/check_user_followed_profile.php";

    // Get profile user ID from URL parameter
    $profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $userData->user_id;

    // Fetch user data
    $current_user_id = $userData->user_id;

    $stmt = $pdo->prepare("SELECT 
        first_name, 
        last_name, 
        img_profile,
        bio
    FROM users 
    -- LEFT JOIN user_follows uf ON uf.user_id = users.user_id
    WHERE users.user_id = :user_id
    GROUP BY users.user_id");
    $stmt->execute(['user_id' => $profile_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch user posts
    $posts = getPublishedPosts($pdo, $profile_user_id);

    // Handle follow/unfollow actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'follow') {
            require_once "../functions/actions/follow_user.php";
            if (followToUser($pdo, $current_user_id, $profile_user_id)) {
                $_SESSION['message'] = "You've successfully followed!";
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'unfollow') {
            require_once "../functions/actions/unfollow_user.php";
            if (unfollowFromUser($pdo, $current_user_id, $profile_user_id)) {
                $_SESSION['message'] = "You've unfollowed.";
            }
        }
        header("Location: ".$_SERVER['PHP_SELF']."?user_id=".$profile_user_id);
        exit();
    }

    // Check if current user is followed to this profile
    $isFollowed = false;
    if ($current_user_id) {
        $isFollowed = checkUserFollowedProfile($pdo, $profile_user_id, $current_user_id);
    }

    // Get follower count
    $followerCount = getFollowersCount($pdo, $profile_user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> - Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/footer.css">
    <link rel="stylesheet" href="../../assets/css/profile.css">
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header.php"; ?>

    <!-- Profile -->
    <?php include "../../views/pages/profile.php"; ?>

    <!-- Footer -->
    <?php include "../../views/partials/footer.html"; ?>

    <script src="../../assets/js/toggle_sidebar.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
</body>
</html>