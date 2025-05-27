<?php
    function getUserInfos($pdo, $user_id) {
        $user_query = "SELECT * FROM users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($user_query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }