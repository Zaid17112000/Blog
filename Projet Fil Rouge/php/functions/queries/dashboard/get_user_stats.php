<?php
    function getUserStats($pdo, $user_id) {
        $stats_query = " SELECT 
            (SELECT COUNT(*) FROM posts WHERE user_id = :user_id1 AND post_status = 'published') as published_posts,
            (SELECT COUNT(*) FROM posts WHERE user_id = :user_id2 AND post_status = 'draft') as draft_posts,
            (SELECT COUNT(*) FROM comments WHERE user_id = :user_id3) as total_comments,
            (SELECT COUNT(*) FROM likes WHERE user_id = :user_id4) as total_likes,
            (SELECT COUNT(*) FROM saved_posts WHERE user_id = :user_id5) as total_saved,
            (SELECT COUNT(*) FROM likes l JOIN posts p ON l.post_id = p.post_id WHERE p.user_id = :user_id6) as likes_received";
        $stmt = $pdo->prepare($stats_query);
        $stmt->bindParam(":user_id1", $user_id);
        $stmt->bindParam(":user_id2", $user_id);
        $stmt->bindParam(":user_id3", $user_id);
        $stmt->bindParam(":user_id4", $user_id);
        $stmt->bindParam(":user_id5", $user_id);
        $stmt->bindParam(":user_id6", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get total number of posts for a user
    function getTotalPosts($pdo) {
        $query = "SELECT COUNT(*) as total_posts 
            FROM Posts 
            WHERE post_status = 'published'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_posts'];
    }

    // Get total number of followers for a user
    function getTotalFollowers($pdo) {
        $query = "SELECT COUNT(*) as total_followers 
                FROM User_Follows";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_followers'];
    }
    
    // Get total number of followers for a user
    function getTotalUsers($pdo) {
        $query = "SELECT COUNT(*) as total_users 
                FROM Users";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    }

    // Get the most liked post for a user
    function getMostLikedPost($pdo) {
        $query = "SELECT p.post_id, p.post_title, COUNT(l.like_id) as like_count
                FROM Posts p
                LEFT JOIN Likes l ON p.post_id = l.post_id
                WHERE p.post_status = 'published'
                GROUP BY p.post_id, p.post_title
                ORDER BY like_count DESC, p.post_created DESC
                LIMIT 3";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get the last three created users (for admin only)
    function getLastCreatedUsers($pdo, $n) {
        $query = "SELECT user_id, first_name, last_name, user_email, user_registered_at
                FROM Users
                ORDER BY user_registered_at DESC
                LIMIT $n";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getRecentTotalPosts($pdo) {
        $recent_posts_query = "SELECT post_id, post_title, post_status, post_published 
        FROM posts 
        ORDER BY post_published DESC LIMIT 3";
        $stmt = $pdo->prepare($recent_posts_query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }