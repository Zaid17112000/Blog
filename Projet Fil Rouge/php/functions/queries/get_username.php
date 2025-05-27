<?php
    function getUsername($pdo, $user_id) {
        $queryUsername = "SELECT CONCAT_WS(' ', first_name, last_name) AS user_name     FROM users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($queryUsername);
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['user_name'] ?? 'Guest';
    }