<?php
    function getFollowersCount($pdo, $user_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE following_user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchColumn();
    }