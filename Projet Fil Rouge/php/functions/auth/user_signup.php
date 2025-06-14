<?php
    function userSignup($pdo, $prenom, $nom, $email, $bio, $img_profile, $role, $hashed_password) {
        $insertUser = "INSERT INTO users(first_name, last_name, user_email, bio, img_profile, user_role, user_password) VALUES(:first_name, :last_name, :user_email, :bio, :img_profile, :user_role, :user_password)";
    
        $stmt = $pdo->prepare($insertUser);
        $stmt->bindParam(":first_name", $prenom);
        $stmt->bindParam(":last_name", $nom);
        $stmt->bindParam(":user_email", $email);
        $stmt->bindParam(":bio", $bio);
        $stmt->bindParam(":img_profile", $img_profile);
        $stmt->bindParam(":user_role", $role);
        $stmt->bindParam(":user_password", $hashed_password);
        return $stmt->execute();
    }