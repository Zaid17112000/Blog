<?php
    function followToUser($pdo, $followerId, $userIdToUnfollow) {
        // First check if subscription already exists
        $stmt = $pdo->prepare("SELECT 1 FROM Follow_Users su 
            JOIN Followers s ON su.follower_id = s.follower_id
            WHERE su.user_id = :user_id AND s.user_id = :follower_id");
        $stmt->execute([
            'user_id' => $userIdToUnfollow,
            'follower_id' => $followerId
        ]);
        
        if ($stmt->fetchColumn()) {
            return false; // Already subscribed
        }

        // Check if follower entry already exists
        $stmt = $pdo->prepare("SELECT follower_id FROM Followers WHERE user_id = :follower_id");
        $stmt->execute(['follower_id' => $followerId]);
        $followerEntry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$followerEntry) {
            // Create new follower entry if doesn't exist
            $stmt = $pdo->prepare("INSERT INTO Followers (user_id) VALUES (:follower_id)");
            $stmt->execute(['follower_id' => $followerId]);
            $followerEntryId = $pdo->lastInsertId();
        } else {
            $followerEntryId = $followerEntry['follower_id'];
        }
        
        // Create subscription
        $stmt = $pdo->prepare("INSERT INTO Follow_Users (user_id, follower_id) 
            VALUES (:user_id, :follower_id)
            ON DUPLICATE KEY UPDATE user_id = user_id"); // Handles race conditions
        
        return $stmt->execute([
            'user_id' => $userIdToUnfollow,
            'follower_id' => $followerEntryId
        ]);
    }