<?php
    function validateAndSanitizeUserInput(array $postData, array $filesData = []): array {
        $response = ['errors' => [], 'data' => []];
        
        // Sanitize inputs
        $sanitized = [
            'nom' => trim(htmlspecialchars($postData['nom'] ?? '')),
            'prenom' => trim(htmlspecialchars($postData['prenom'] ?? '')),
            'email' => trim(htmlspecialchars($postData['email'] ?? '')),
            'bio' => trim(htmlspecialchars($postData['bio'] ?? '')),
            'password' => $postData['password'] ?? '',
            'confirm_password' => $postData['confirm_password'] ?? ''
        ];
        
        // Validate inputs
        if (empty($sanitized['nom'])) {
            $response['errors']['nom'] = "Please enter your first name";
        }
        
        if (empty($sanitized['prenom'])) {
            $response['errors']['prenom'] = "Please enter your last name";
        }
        
        if (empty($sanitized['email'])) {
            $response['errors']['email'] = "Please enter a valid email address";
        } elseif (!filter_var($sanitized['email'], FILTER_VALIDATE_EMAIL)) {
            $response['errors']['email'] = "Your Email is in an invalid format";
        }
        
        if (empty($sanitized['password'])) {
            $response['errors']['password'] = "Please enter a password";
        } elseif (strlen($sanitized['password']) < 8) {
            $response['errors']['password'] = "Password must be at least 8 characters";
        }
        
        if ($sanitized['password'] !== $sanitized['confirm_password']) {
            $response['errors']['confirm_password'] = "Passwords do not match";
        }
        
        // Handle file upload
        $profileImagePath = null;
        if (isset($filesData['profile_image']) && $filesData['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fileInfo, $filesData['profile_image']['tmp_name']);
            
            if (in_array($mime, $allowedTypes) && $filesData['profile_image']['size'] <= $maxSize) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($filesData['profile_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('profile_') . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($filesData['profile_image']['tmp_name'], $destination)) {
                    $profileImagePath = $destination;
                }
            } else {
                $response['errors']['profile_image'] = 'Invalid image file. Please upload a JPG, PNG, or GIF under 2MB.';
            }
        }
        
        // Hash password if no errors
        if (empty($response['errors'])) {
            $sanitized['hashed_password'] = password_hash($sanitized['password'], PASSWORD_ARGON2I);
            $sanitized['profile_image_path'] = $profileImagePath;
        }
        
        $response['data'] = $sanitized;
        return $response;
    }