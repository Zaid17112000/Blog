<?php
    ini_set('display_errors', 0);
    error_reporting(0); // Or use E_ALL in development

    session_start();
    header('Content-Type: application/json');
    require '../config/connectDB.php';

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

    // Generate CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $response = ['success' => false];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $response['error'] = "Invalid CSRF token";
            echo json_encode($response);
            exit;
        }

        // Validate email
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $response['error'] = "Invalid email address";
            echo json_encode($response);
            exit;
        }

        // Generate unsubscribe token
        $unsubscribe_token = bin2hex(random_bytes(16));

        try {
            // Check if email exists in Users table
            $user_stmt = $pdo->prepare("SELECT user_id, first_name, last_name 
                FROM users 
                WHERE user_email = ?
            ");
            $user_stmt->execute([$email]);
            $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

            // Check if already subscribed
            $subscriber_stmt  = $pdo->prepare("SELECT * FROM subscribers WHERE email = ?");
            $subscriber_stmt ->execute([$email]);
            
            if ($subscriber_stmt ->rowCount() > 0) {
                // Update existing subscription
                $pdo->prepare("UPDATE subscribers SET 
                    is_active = TRUE, 
                    user_id = ?, 
                    unsubscribe_token = ? 
                WHERE email = ?")
                    ->execute([
                        $user_data ? $user_data['user_id'] : null,
                        $unsubscribe_token,
                        $email
                    ]);
                $response['message'] = "Subscription updated successfully!";
            } else {
                // New subscription
                $stmt = $pdo->prepare("
                    INSERT INTO Subscribers 
                    (email, user_id, unsubscribe_token)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $email,
                    $user_data ? $user_data['user_id'] : null,
                    $unsubscribe_token
                ]);
                $response['message'] = "Thanks for subscribing!";
            }

            // Send confirmation email
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
            $mail->Port       = $_ENV['SMTP_PORT'];
            $mail->isHTML(true);
            
            $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($email);

            // Personalize email if user exists
            $greeting = $user_data ? 
                "Dear " . htmlspecialchars($user_data['first_name']) : 
                "Hello";

            $mail->Subject = 'Thanks for subscribing!';
            $mail->Body = "
                <h1>{$greeting},</h1>
                <p>Thank you for subscribing to our newsletter!</p>
                <p>You'll receive updates when new posts are published.</p>
                <p><a href='".$_ENV['SITE_URL']."/unsubscribe?token=$unsubscribe_token'>Unsubscribe</a></p>
            ";
            $mail->CharSet = 'UTF-8';
            
            $mail->send();

            $response['success'] = true;
            
            // header("Location: " . $_SERVER['PHP_SELF']);
            // exit();
            
        } catch (Exception $e) {
            error_log("Subscription error: " . $e->getMessage());
            $response['error'] = "Subscription failed. Please try again later.";
        }
    }

    echo json_encode($response);
    exit;