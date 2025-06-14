<?php
    // function unfollowFromUser($pdo, $followerId, $userIdToUnfollow) {
    //     // First get the follower entry ID
    //     $stmt = $pdo->prepare("SELECT follower_id FROM followers WHERE user_id = :follower_id");
    //     $stmt->execute(['follower_id' => $followerId]);
    //     $followerEntry = $stmt->fetch(PDO::FETCH_ASSOC);
        
    //     if ($followerEntry) {
    //         $stmt = $pdo->prepare("DELETE FROM Follow_Users WHERE user_id = :user_id AND follower_id = :follower_id");
    //         return $stmt->execute([
    //             'user_id' => $userIdToUnfollow,
    //             'follower_id' => $followerEntry['follower_id']
    //         ]);
    //     }
    //     return false;
    // }
    function unfollowFromUser($pdo, $followerId, $userIdToUnfollow) {
        $stmt = $pdo->prepare("DELETE FROM user_follows 
            WHERE follower_user_id = :follower_id 
            AND following_user_id = :user_id");
        return $stmt->execute([
            'follower_id' => $followerId,
            'user_id' => $userIdToUnfollow
        ]);
    }