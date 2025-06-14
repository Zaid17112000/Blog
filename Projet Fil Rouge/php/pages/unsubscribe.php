<?php
    require_once '../config/connectDB.php';

    if (isset($_GET['token'])) {
        $token = $_GET['token'];
        
        $stmt = $pdo->prepare("UPDATE subscribers 
            SET is_active = FALSE 
            WHERE unsubscribe_token = ?
        ");
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            $message = "You've been unsubscribed successfully.";
        } else {
            $message = "Invalid unsubscribe link.";
        }
    } else {
        $message = "No unsubscribe token provided.";
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
</head>
<body>
    <h1><?= $message ?></h1>
    <p><a href="<?= $_ENV['SITE_URL'] ?>">Return to homepage</a></p>
</body>
</html>