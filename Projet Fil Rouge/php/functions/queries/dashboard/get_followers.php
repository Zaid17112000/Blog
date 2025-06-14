<?php
    function getFollowers($pdo, $user_id) {
        try {
            $stmt = $pdo->prepare("SELECT 
                    u.user_id,
                    u.first_name,
                    u.last_name,
                    u.user_email,
                    u.img_profile,
                    uf.follow_date
                FROM user_follows uf
                JOIN users u ON uf.follower_user_id = u.user_id
                WHERE uf.following_user_id = :user_id
                ORDER BY uf.follow_date DESC
            ");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching followers: " . $e->getMessage());
            return [];
        }
    }