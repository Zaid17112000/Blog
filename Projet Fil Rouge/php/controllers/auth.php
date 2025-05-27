<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    function validateJWT() {
        $jwtSecret = $_ENV['JWT_SECRET'];
        
        if (!isset($_COOKIE['jwt_token'])) {
            header("Location: /login.php");
            exit();
        }

        try {
            $token = $_COOKIE['jwt_token'];
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
            return $decoded->data; // Return user data
        } catch (Exception $e) {
            // Token invalid/expired
            setcookie('jwt_token', '', time() - 3600, '/'); // Clear cookie
            header("Location: /login.php");
            exit();
        }
    }