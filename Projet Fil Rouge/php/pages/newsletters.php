<?php
    // session_start();
    require_once "../config/connectDB.php";

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

    // Load PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $smtp_server = "smtp-relay.brevo.com";
    $smtp_port = 587;
    $smtp_login = "8f8239001@smtp-brevo.com";
    $smtp_password = $_ENV['SMTP_PASSWORD'];;

    try {
        if (!isset($_SESSION['new_post_id'])) {
            throw new Exception("No new post ID found");
        }

        $post_id = $_SESSION['new_post_id'];

        // Get the new post details with author information
        $stmt = $pdo->prepare("SELECT 
                p.*, 
                u.first_name, 
                u.last_name, 
                u.user_email as author_email
            FROM posts p
            JOIN users u ON p.user_id = u.user_id
            WHERE p.post_id = ? AND p.post_status = 'published'
        ");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            throw new Exception("Post not found or not published");
        }

        // Get all active subscribers
        $stmt = $pdo->prepare("SELECT 
                s.email, 
                COALESCE(u.first_name, 'subscriber') AS first_name
            FROM subscribers s
            LEFT JOIN users u ON s.user_id = u.user_id
            WHERE s.is_active = TRUE
        ");
        $stmt->execute();
        $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($subscribers)) {
            throw new Exception("No active subscribers found");
        }

        // Initialize PHPMailer
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE']; // 'tls' or 'ssl'
        $mail->Port       = $_ENV['SMTP_PORT'];
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        // Common email settings
        $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
        $mail->addReplyTo($post['author_email'], $post['first_name'] . ' ' . $post['last_name']);
        
        // Email content
        $subject = "New Post: " . htmlspecialchars($post['post_title']);
        
        // Create email template
        ob_start();
        include __DIR__ . '/../../views/pages/new_post_email.php';
        $email_body = ob_get_clean();
        
        // Send to each subscriber
        $success_count = 0;
        foreach ($subscribers as $subscriber) {
            try {
                $mail->clearAddresses();
                $mail->addAddress($subscriber['email'], $subscriber['first_name']);
                $mail->Subject = $subject;
                $mail->Body = $email_body;
                
                if ($mail->send()) {
                    $success_count++;
                }
                
                // Throttle sending
                if ($success_count % 10 === 0) {
                    sleep(1);
                }
            } catch (Exception $e) {
                error_log("Failed to send to {$subscriber['email']}: " . $mail->ErrorInfo);
                continue;
            }
        }
        
        // Log results
        error_log("Sent notifications for post {$post_id} to {$success_count} subscribers");
        
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
    }