<?php
    /**
     * Updates user information in the database
     * 
     * @param PDO $pdo Database connection
     * @param int $userId ID of the user to update
     * @param array $validatedData Validated user data
     * @return bool True on success, false on failure
     * @throws PDOException If database operation fails
     */
    function updateUserProfile(PDO $pdo, int $userId, array $validatedData): bool {
        $updateQuery = "UPDATE users SET 
            first_name = :first_name, 
            last_name = :last_name,
            user_email = :email,
            bio = :bio";
        
        $params = [
            ':first_name' => $validatedData['first_name'],
            ':last_name' => $validatedData['last_name'],
            ':email' => $validatedData['email'],
            ':bio' => $validatedData['bio'],
            ':user_id' => $userId
        ];
        
        if ($validatedData['password_changed']) {
            $updateQuery .= ", user_password = :password";
            $params[':password'] = password_hash($validatedData['new_password'], PASSWORD_DEFAULT);
        }
        
        $updateQuery .= " WHERE user_id = :user_id";
        
        $stmt = $pdo->prepare($updateQuery);
        return $stmt->execute($params);
    }