<?php
    // php/controllers/verify_jwt.php
    require_once __DIR__ . '/../config/bootstrap.php';
    require_once __DIR__ . '/../config/connectDB.php';

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    /**
     * File Overview
        ** This script is a PHP controller (verify_jwt.php) designed to verify a JSON Web Token (JWT) for user authentication. It checks for a JWT in either a cookie or an HTTP Authorization header, validates it, and returns the decoded payload data if valid. If the token is missing or invalid, it responds with an error or redirects to a login page.
    * use: Imports specific classes from the Firebase JWT library, which is used for encoding and decoding JWTs.
    * Firebase\JWT\JWT: Provides methods to encode and decode JWTs.
    * Firebase\JWT\Key: Represents the key used for signing and verifying the JWT (in this case, using the HS256 algorithm).
    */

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

        /**
         * preg_match('/Bearer\s(\S+)/', ...): Uses a regular expression to match a Bearer token format (e.g., Bearer <token>).
            ** Bearer\s: Matches the word "Bearer" followed by a space.
            ** (\S+): Captures one or more non-whitespace characters (the token itself) into $matches[1].
        * $matches: Stores the regex results, with $matches[1] containing the token if matched.
        */
        
        if (!$token) {
            // header("HTTP/1.1 401 Unauthorized");
            header("Location: ../pages/login.php");
            exit(json_encode(['error' => 'Token required']));
        }
        
        /**
            if (!$token) {
                // Only redirect if we're not already on login page
                if (strpos($_SERVER['REQUEST_URI'], 'login.php') === false) {
                    header("Location: /login.php?error=token_missing");
                    exit();
                }
                return null; // Continue execution if already on login page
            }
        */

        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            /** new Key($_ENV['JWT_SECRET'], 'HS256'): Specifies the secret key (from an environment variable JWT_SECRET) and the HS256 algorithm for verification. */
            return $decoded->data; // Return only the payload data
        } catch (Exception $e) {
            // Clear invalid token
            setcookie('jwt_token', '', time() - 3600, '/', '', true, true);
            header("Location: /login.php?error=session_expired");
            exit();

            /**
             * setcookie('jwt_token', '', time() - 3600, '/', '', true, true): Clears the jwt_token cookie by setting an empty value and an expiration time in the past.
                ** time() - 3600: Sets the cookie to expire 1 hour ago, effectively deleting it.
                ** '/': Makes the cookie available across the entire domain.
                ** true, true: Enables Secure and HttpOnly flags for security (prevents JavaScript access and ensures HTTPS-only transmission).
            */
        }
    }