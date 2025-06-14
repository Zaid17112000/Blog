<?php
    session_start();

    // Generate CSRF token if it doesn't exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if (isset($_POST["verify_code"])) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Invalid CSRF token. Please try again.";
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            return;
        }

        $entered_code = $_POST["code"];
        $correct_code = $_SESSION["code-reset-password"];

        if ($entered_code == $correct_code) {
            // Code matches, proceed to reset password
            unset($_SESSION["code-reset-password"]);
            $_SESSION["reset-verified"] = true;
            header("Location: reset_password.php");
            exit();
        } else {
            $error = "The verification code is incorrect. Please try again.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Code - BlogSpace</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/check_code.css">
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header_login_signup.html"; ?>

    <?php include "../../views/pages/verify_code.php"; ?>

    <!-- Footer -->
    <?php include "../../views/partials/footer_login_signup.php"; ?>

    <script src="../../assets/js/toggle_password.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
    <script src="../../assets/js/verify_code.js"></script>
</body>
</html>