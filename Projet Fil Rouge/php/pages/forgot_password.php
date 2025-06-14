<?php
    session_start();

    // Generate CSRF token if it doesn't exist
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Load environment variables
    require_once '../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
    $dotenv->load();

    require '../../vendor/phpmailer/phpmailer/src/Exception.php';
    require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $smtp_server = "smtp-relay.brevo.com";
    $smtp_port = 587;
    $smtp_login = "8f8239001@smtp-brevo.com";
    $smtp_password = $_ENV['SMTP_PASSWORD'];

    if (isset($_POST["reset"])) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Invalid CSRF token. Please try again.";

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $sender_email = "zaydds2020@gmail.com";
        $receiver_email = $_POST["email"];
        $subject = "🔒 Reset Your Password"; 
        $code_verification = rand(100000,999999);
        $body = "
            <p>Hello,</p>
            <p>We received a request to reset your password for your <strong>BlogSpace</strong> account.</p>
            <p><strong>Your verification code is:</strong> <span style='font-size: 20px;'>$code_verification</span></p>
            <p>If you didn’t request this, please ignore this email. This code will expire in 10 minutes.</p>
            <p>Thanks,<br>The BlogSpace Team</p>
        ";

        $_SESSION["code-reset-password"] = $code_verification;
        $_SESSION["reset_email"] = $_POST["email"];

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
            $mail->Port       = $_ENV['SMTP_PORT'];
            $mail->isHTML(true);

            $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($receiver_email);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->send();
            
            header("Location: check_code.php");
            exit;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
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
    <link rel="stylesheet" href="../../assets/css/forgot_password.css">
</head>
<body>
    <!-- Header -->
    <?php include "../../views/partials/header_login_signup.html"; ?>

    <?php include "../../views/pages/forgot_password.php"; ?>

    <!-- Footer -->
    <?php include "../../views/partials/footer_login_signup.php"; ?>

    <script src="../../assets/js/toggle_password.js"></script>
    <script src="../../assets/js/handle_hamburger.js"></script>
</body>
</html>