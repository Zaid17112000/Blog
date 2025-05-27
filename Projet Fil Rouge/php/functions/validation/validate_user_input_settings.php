<?php
    function validateUserProfile(array $postData, array $currentUser, $pdo): array {
        $errors = [];
        
        // Trim all input data
        $first_name = trim($postData['first_name'] ?? '');
        $last_name = trim($postData['last_name'] ?? '');
        $email = trim($postData['email'] ?? '');
        $bio = trim($postData['bio'] ?? '');
        $current_password = trim($postData['current_password'] ?? '');
        $new_password = trim($postData['new_password'] ?? '');
        $confirm_password = trim($postData['confirm_password'] ?? '');

        // Validate required fields
        if (empty($first_name)) {
            $errors['first_name'] = "First name is required";
        }
        
        if (empty($last_name)) {
            $errors['last_name'] = "Last name is required";
        }
        
        if (empty($email)) {
            $errors['email'] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format";
        }

        // Check if email is already taken by another user
        if ($email !== $currentUser['user_email']) {
            $email_check = $pdo->prepare("SELECT user_id FROM users WHERE user_email = :email");
            $email_check->bindParam(":email", $email);
            $email_check->execute();
            if ($email_check->fetch()) {
                $errors['email'] = "Email is already taken";
            }
        }

        // Password change validation
        $password_changed = false;
        if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
            if (!password_verify($current_password, $currentUser['user_password'])) {
                $errors['current_password'] = "Current password is incorrect";
            } elseif (empty($new_password)) {
                $errors['new_password'] = "New password is required";
            } elseif (strlen($new_password) < 8) {
                $errors['new_password'] = "Password must be at least 8 characters";
            } elseif ($new_password !== $confirm_password) {
                $errors['confirm_password'] = "Passwords don't match";
            } else {
                $password_changed = true;
            }
        }

        return [
            'errors' => $errors,
            'data' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'bio' => $bio,
                'password_changed' => $password_changed,
                'new_password' => $new_password
            ]
        ];
    }