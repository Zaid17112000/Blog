<?php
    function setJWTCookie($jwt, $expiry = 3600) {
        setcookie('jwt_token', $jwt, [
            'expires' => time() + $expiry,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }