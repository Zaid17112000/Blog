<?php
    require_once __DIR__ . '/../../config/bootstrap.php';
    require_once __DIR__ . '/../../config/connectDB.php';

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    function verifyJWT() {
        $token = null; // Initializes a variable $token to null, which will store the JWT if found.
        
        // 1. Check for cookie (matches login.php)
        if (isset($_COOKIE['jwt_token'])) {
            $token = $_COOKIE['jwt_token'];
        }
        // 2. Check Authorization header (for APIs)
        elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            // header("HTTP/1.1 401 Unauthorized");
            header("Location: ../pages/login.php");
            exit(json_encode(['error' => 'Token required']));
        }
        
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            return $decoded->data; // Return only the payload data
        } catch (Exception $e) {
            // Clear invalid token
            setcookie('jwt_token', '', time() - 3600, '/', '', true, true);
            header("Location: /login.php?error=session_expired");
            exit();
        }
    }