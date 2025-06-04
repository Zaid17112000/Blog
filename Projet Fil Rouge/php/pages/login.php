<?php
    session_start();
    require_once "../config/bootstrap.php";
    require_once "../config/connectDB.php";
    require_once "../functions/auth/user_login.php";
    require_once "../functions/actions/generate_jwt_token.php";
    require_once "../functions/actions/set_jwt_coockie.php";
    require_once "../functions/validation/validate_csrf_token.php";

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    // Generate CSRF token if it doesn't exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $error = "";

    if(isset($_POST["login"])) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Invalid CSRF token. Please try again.";
            // Optionally, you might want to regenerate the token here
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        else {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST["password"];

            $user = userLogin($pdo, $email);

            if($user) {
                if (password_verify($password, $user["user_password"])) {
                    // Generate JWT Token
                    $jwt = generateJWT($user["user_id"]);
                    
                    // Store JWT in HTTP-only Cookie (Secure)
                    setJWTCookie($jwt);
                    
                    $user_id = $user["user_id"];

                    session_start();

                    $_SESSION["user"] = $user_id;
                    
                    // Check if bio is empty
                    $stmt = $pdo->prepare("SELECT bio FROM users WHERE user_id = :user_id");
                    $stmt->bindParam(":user_id", $user_id);
                    $stmt->execute();
                    $userData = $stmt->fetch();

                    // Set session variable based on bio
                    $_SESSION["show_empty_bio_state"] = empty($userData['bio']); // return true or false

                    header("Location: homepage.php");
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "No user found with this email!";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header_login_signup.html"; ?>
    
    <!-- Main Content -->
    <?php include "../../views/pages/login.php"; ?>
    
    <!-- Footer -->
    <?php include "../../views/partials/footer_login_signup.php"; ?>

    <script src="../../assets/js/toggle_password.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
</body>
</html>