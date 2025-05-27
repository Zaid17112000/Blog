<?php
    function checkEmailAlreadyExist($pdo, $email) {
        $checkEmail = $pdo->prepare("SELECT user_email FROM users WHERE user_email = :email");
        $checkEmail->bindParam(":email", $email);
        $checkEmail->execute();
        return $checkEmail->rowCount() > 0;
    }