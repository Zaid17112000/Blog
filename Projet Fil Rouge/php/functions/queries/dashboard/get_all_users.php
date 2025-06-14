<?php
    // function getAllUsers($pdo) {
    //     try {
    //         $stmt = $pdo->prepare("SELECT 
    //             user_id, 
    //             first_name, 
    //             last_name, 
    //             user_email, 
    //             user_role, 
    //             user_registered_at 
    //             FROM users 
    //             ORDER BY user_registered_at DESC
    //         ");
    //         $stmt->execute();
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     } catch (PDOException $e) {
    //         error_log("Error fetching users: " . $e->getMessage());
    //         return [];
    //     }
    // }
    function getAllUsers($pdo) {
        $query = "SELECT 
                    u.user_id, 
                    u.first_name, 
                    u.last_name, 
                    u.user_email, 
                    u.user_role, 
                    u.user_registered_at,
                    u.img_profile,
                    COUNT(DISTINCT p.post_id) as post_count,
                    COUNT(DISTINCT l.like_id) as total_likes
                FROM Users u
                LEFT JOIN Posts p ON u.user_id = p.user_id AND p.post_status = 'published'
                LEFT JOIN Likes l ON p.post_id = l.post_id
                GROUP BY u.user_id, u.first_name, u.last_name, u.user_email, u.user_role, u.user_registered_at, u.img_profile
                ORDER BY u.user_registered_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }