<?php
    // function checkUserFollowedProfile($pdo, $user_id, $userData) {
    //     $stmt = $pdo->prepare("SELECT 1 
    //         FROM Follow_Users su 
    //         JOIN Followers s ON su.follower_id = s.follower_id
    //         WHERE su.user_id = :profile_user_id AND s.user_id = :current_user_id");
    //     $stmt->execute([
    //         'profile_user_id' => $user_id,
    //         'current_user_id' => $userData->user_id
    //     ]);
    //     return $stmt->fetchColumn();
    // }
    function checkUserFollowedProfile($pdo, $profile_user_id, $current_user_id) {
        $stmt = $pdo->prepare("SELECT 1 
            FROM user_follows 
            WHERE follower_user_id = :current_user_id 
            AND following_user_id = :profile_user_id");
        $stmt->execute([
            'profile_user_id' => $profile_user_id,
            'current_user_id' => $current_user_id
        ]);
        return $stmt->fetchColumn();
    }