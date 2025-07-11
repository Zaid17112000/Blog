<?php
    function userLogin($pdo, $email) {
        $queryLogin = "SELECT user_id, user_email, user_password FROM users WHERE user_email = :email";
        $stmt = $pdo->prepare($queryLogin);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }