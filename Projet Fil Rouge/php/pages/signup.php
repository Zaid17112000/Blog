<?php
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
    require_once "../config/connectDB.php";
    require_once "../functions/auth/user_signup.php";
    require_once "../functions/validation/check_email.php";
    require_once "../functions/validation/validate_user_input_signup.php";

    // Initialize response array
    $validateInputEntry = ['errors' => [], 'data' => []];

    // Generate CSRF token if it doesn't exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submit"])) {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $validateInputEntry["errors"]["general"] = "Invalid CSRF token. Please refresh the page and try again.";
            // Optionally, you might want to regenerate the token here
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            $validateInputEntry = validateAndSanitizeUserInput($_POST, $_FILES);

            if (empty($validateInputEntry["errors"])) {
                $email = $validateInputEntry['data']['email'];
                $email_already_exist = checkEmailAlreadyExist($pdo, $email);
        
                if ($email_already_exist) {
                    $validateInputEntry["errors"]["email"] = "Email already exists!";
                } else {
                    $prenom = $validateInputEntry['data']['prenom'];
                    $nom = $validateInputEntry['data']['nom'];
                    $bio = $validateInputEntry['data']['bio'];
                    $profileImagePath = $validateInputEntry['data']['profile_image_path'] ?? null;
                    $hashed_password = $validateInputEntry['data']['hashed_password'];
                    
                    $signup = userSignup($pdo, $prenom, $nom, $email, $bio, $profileImagePath, $hashed_password);
                    if($signup) {
                        header("Location: login.php");
                        exit();
                    } else {
                        error_log("Database Error: " . implode(", ", $stmt->errorInfo()));
                        $validateInputEntry["errors"]["general"] = "Something went wrong. Please try again.";
                    }
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/signup.css">
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header_login_signup.html" ?>

    <!-- Main Content -->
    <?php include "../../views/pages/signup.php" ?>

    <!-- Footer -->
    <?php include "../../views/partials/footer_login_signup.php" ?>

    <script src="../../assets/js/toggle_password.js"></script>
    <script src="../../assets/js/handle_bio.js"></script>
    <script src="../../assets/js/handle_img_profile.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
</body>
</html>