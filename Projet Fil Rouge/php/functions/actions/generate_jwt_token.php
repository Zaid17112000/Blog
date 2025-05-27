<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    function generateJWT($user_id, $expiry = 3600) {
        $secretKey = $_ENV['JWT_SECRET'];
        $issuedAt = time();
        $expirationTime = $issuedAt + $expiry;

        $payload = [
            "iat" => $issuedAt,
            "exp" => $expirationTime,
            "data" => [
                "user_id" => $user_id
            ]
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }