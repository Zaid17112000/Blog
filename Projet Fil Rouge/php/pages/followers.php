<?php
    session_start();
    require_once __DIR__ . '/../functions/actions/verify_jwt.php';
    $userData = verifyJWT();
    require_once "../config/connectDB.php";
    require_once "../functions/queries/dashboard/get_user_infos.php";
    require_once "../functions/queries/dashboard/get_followers.php";

    $user_id = $userData->user_id;

    // Get user information
    $user = getUserInfos($pdo, $user_id);

    // Get followers data
    $followers = getFollowers($pdo, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Followers</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/followers.css">
</head>
<body>
    <?php include "../../views/pages/followers.php" ?>

    <script>
        // Handle view profile buttons
        document.querySelectorAll('.view-profile-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                // Implement your profile viewing logic here
                // For example:
                window.location.href = `profile.php?user_id=${userId}`;
            });
        });
    </script>
</body>
</html>