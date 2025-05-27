<?php
    session_start();

    // Unset all session variables
    $_SESSION = [];

    session_destroy();

    // Clear the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    setcookie('jwt_token', '', time() - 3600, '/'); // Delete JWT cookie

    header("Location: ../pages/login.php");
    exit;