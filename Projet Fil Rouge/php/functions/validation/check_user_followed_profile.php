<?php
    function checkUserFollowedProfile($pdo, $user_id, $userData) {
        $stmt = $pdo->prepare("SELECT 1 
            FROM Follow_Users su 
            JOIN Followers s ON su.follower_id = s.follower_id
            WHERE su.user_id = :profile_user_id AND s.user_id = :current_user_id");
        $stmt->execute([
            'profile_user_id' => $user_id,
            'current_user_id' => $userData->user_id
        ]);
        return $stmt->fetchColumn();
    }