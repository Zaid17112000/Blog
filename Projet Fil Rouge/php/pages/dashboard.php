<?php
    session_start();
    require_once __DIR__ . '/../controllers/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/queries/dashboard/get_user_infos.php";
    require_once "../functions/queries/dashboard/get_user_stats.php";
    require_once "../functions/queries/dashboard/get_likes_count.php";
    require_once "../functions/queries/dashboard/get_recent_posts.php";
    require_once "../functions/queries/dashboard/get_recent_comments.php";
    require_once "../functions/queries/dashboard/get_saved_posts.php";
    require_once "../functions/queries/dashboard/get_published_posts.php";
    require_once "../functions/queries/dashboard/get_draft_posts.php";
    require_once "../functions/queries/dashboard/get_user_activity.php";

    $user_id = $userData->user_id;

    // Get user information
    $user = getUserInfos($pdo, $user_id);

    // Get user stats
    $stats = getUserStats($pdo, $user_id);
    
    $posts_count = $stats['published_posts'];
    $drafts_count = $stats['draft_posts'];
    $comments_count = $stats['total_comments'];
    $likes_count = $stats['total_likes'];
    $saved_count = $stats['total_saved'];
    $likes_received_count = $stats['likes_received'];

    // Get likes received on user's posts
    $likes_received_count = getLikesCount($pdo, $user_id);

    // Get recent posts
    $recent_posts = getRecentPosts($pdo, $user_id);

    // Get recent comments
    $recent_comments = getRecentComments($pdo, $user_id);

    // Get saved posts
    $saved_posts = getSavedPosts($pdo, $user_id);

    // Get published posts
    $published_posts = getPublishedPosts($pdo, $user_id);

    // Get draft posts
    $draft_posts = getDraftPosts($pdo, $user_id);

    // Get user activity (monthly posts and comments)
    $activity_data = getUserActivity($pdo, $user_id);

    // Format activity data for chart
    $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    $formatted_activity = [];
    for ($i = 0; $i < 12; $i++) {
        $formatted_activity[$i] = [
            "month" => $months[$i],
            "posts" => 0,
            "comments" => 0
        ];
    }

    foreach ($activity_data as $data) {
        $month_index = $data['month'] - 1;
        if ($data['type'] == 'post') {
            $formatted_activity[$month_index]['posts'] = (int)$data['count'];
        } else {
            $formatted_activity[$month_index]['comments'] = (int)$data['count'];
        }
    }
    // $activity_json = json_encode($formatted_activity);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <button class="sidebar-toggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <?php include "../../views/pages/dashboard_container.php"; ?>

    <script src="../../assets/js/toggle_sidebar_on_mobile.js"></script>
</body>
</html>