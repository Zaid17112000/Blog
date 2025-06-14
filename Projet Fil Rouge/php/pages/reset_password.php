<?php
    session_start();
    require '../config/connectDB.php';

    // Generate CSRF token if it doesn't exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Redirect if verification step was not completed
    if (!isset($_SESSION["reset-verified"])) {
        header("Location: forgot_password.php");
        exit();
    }

    $email = $_SESSION["reset_email"];

    // Simulate updating password — in real case, use DB operations
    if (isset($_POST["set_password"])) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Invalid CSRF token. Please try again.";
            // Optionally, you might want to regenerate the token here
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        if (strlen($new_password) < 6) {
            $error = "🔒 Password must be at least 6 characters.";
        } elseif ($new_password !== $confirm_password) {
            $error = "❌ Passwords do not match.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET user_password = :new_password WHERE user_email = :email");
            $stmt->bindParam(':new_password', $hashed_password);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Simulate success and clean up session
            unset($_SESSION["reset_email"]);
            unset($_SESSION["reset-verified"]);
            $success = "✅ Your password has been reset successfully.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <!-- <link rel="stylesheet" href="../../assets/css/header.css"> -->
    <link rel="stylesheet" href="../../assets/css/reset_password.css">
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header_login_signup.html"; ?>

    <?php include "../../views/pages/reset_password.php"; ?>

    <!-- Footer -->
    <?php include "../../views/partials/footer_login_signup.php"; ?>

    <script src="../../assets/js/toggle_password.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
</body>
</html>