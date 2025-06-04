<?php
    function validateCsrfToken() {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        // Instead of direct comparison ($_POST['csrf_token'] !== $_SESSION['csrf_token']), use hash_equals() to prevent timing attacks
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            // Token is invalid, reject the request
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token validation failed']));
        }
    }