<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/queries/dashboard/get_user_infos.php";
    require_once "../functions/queries/dashboard/get_all_users.php";
    require_once "../functions/queries/dashboard/get_user_stats.php";

    $user_id = $userData->user_id;

    // Get user information
    $user = getUserInfos($pdo, $user_id);

    // Pagination parameters
    $users_per_page = 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Gets the current page number from URL (?page=X), defaults to 1
    if ($current_page < 1) {
        $current_page = 1;
    } // Validation: Ensures page number is never less than 1
    $offset = ($current_page - 1) * $users_per_page;

    // Get total count of users for pagination
    $total_users_query = "SELECT COUNT(*) FROM users";
    $total_users = $pdo->query($total_users_query)->fetchColumn();
    $total_pages = ceil($total_users / $users_per_page);

    // Get paginated users
    $users_query = "SELECT 
        u.user_id, 
        u.first_name, 
        u.last_name, 
        u.user_email, 
        u.user_role, 
        u.user_registered_at,
        COUNT(DISTINCT p.post_id) as post_count,
        COUNT(DISTINCT l.like_id) as total_likes
    FROM users u
    LEFT JOIN posts p ON u.user_id = p.user_id
    LEFT JOIN likes l ON p.post_id = l.post_id
    GROUP BY u.user_id
    ORDER BY u.user_registered_at DESC
    LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($users_query);
    $stmt->bindParam(':limit', $users_per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $users = [];

    // Get user stats
    $total_posts = getTotalPosts($pdo);
    $total_followers = getTotalFollowers($pdo);
    $all_users = getTotalUsers($pdo);
    $most_liked_posts = getMostLikedPost($pdo);
    // $most_commented_post = getMostCommentedPost($pdo);
    $recent_posts = getRecentTotalPosts($pdo);
    $get_users = getAllUsers($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_overview.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <?php include "../../views/pages/admin_overview.php" ?>

    <script src="../../assets/js/toggle_sidebar_on_mobile.js"></script>
    <script src="../../assets/js/dashboard/delete_user.js"></script>
</body>
</html>